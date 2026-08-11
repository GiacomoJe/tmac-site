<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportProductsFromXlsx extends Command
{
    protected $signature = 'products:import
                            {file=produtos-tmac.xlsx : Caminho do arquivo XLSX na raiz do projeto}
                            {--images : Baixa as imagens dos produtos}
                            {--images-only : Só baixa imagens, não mexe nos dados dos produtos}
                            {--limit= : Importa apenas N produtos (para teste)}
                            {--offset=0 : Pula as N primeiras linhas (retomar de onde parou)}
                            {--dry-run : Simula sem gravar no banco}';

    protected $description = 'Importa produtos do export WooCommerce (XLSX)';

    /** Mapeia categorias do export para as 13 categorias oficiais TMAC */
    private const CATEGORY_MAP = [
        // Já são oficiais
        'ACESSÓRIOS'                  => 'ACESSÓRIOS',
        'CABOS DE COMANDO'            => 'CABOS DE COMANDO',
        'CARENAGEM'                   => 'CARENAGEM',
        'CHASSI'                      => 'CHASSI',
        'ELÉTRICA'                    => 'ELÉTRICA',
        'FERRAMENTAS E EQUIPAMENTOS'  => 'FERRAMENTAS E EQUIPAMENTOS',
        'FIXAÇÃO'                     => 'FIXAÇÃO',
        'FREIO'                       => 'FREIO',
        'INJEÇÃO'                     => 'INJEÇÃO',
        'MOTOR'                       => 'MOTOR',
        'RODA'                        => 'RODA',
        'SUSPENSÃO'                   => 'SUSPENSÃO',
        'TRANSMISSÃO'                 => 'TRANSMISSÃO',

        // Adaptadas
        'LUVA'                                => 'ACESSÓRIOS',
        'MANOPLA ESPORTIVA'                   => 'ACESSÓRIOS',
        'MANETE ESPORTIVO'                    => 'ACESSÓRIOS',
        'TRAVA PARA MOTO'                     => 'ACESSÓRIOS',
        'CONTRAPESO DE GUIDÃO'                => 'ACESSÓRIOS',
        'BAULETO'                             => 'ACESSÓRIOS',
        'RETROVISOR (ESPORTIVO/UNIVERSAL)'    => 'ACESSÓRIOS',
        'ANTENA METÁLICA CORTA PIPA RETRÁTIL' => 'ACESSÓRIOS',
        'ENCOSTO DE BAULETO COM CABECEIRA'    => 'ACESSÓRIOS',
        'RODA DE LIGA LEVE'                   => 'RODA',
        'PISCA'                               => 'ELÉTRICA',
        'LÂMPADA EM LED (FAROL)'              => 'ELÉTRICA',
        'FAROL AUXILIAR'                      => 'ELÉTRICA',
        'FILTRO DE AR ESPORTIVO'              => 'MOTOR',
        'GUIDAO EM ALUMINIO'                  => 'CHASSI',
        'SUPORTE PARA GUIDÃO DE ALUMÍNIO'     => 'CHASSI',
        'PEDAL DE APOIO TRASEIRO'             => 'CHASSI',
        'SANFONA DE BENGALA'                  => 'SUSPENSÃO',
        'PEDAL DE CAMBIO'                     => 'TRANSMISSÃO',
        'MANETE DE EMBREAGEM'                 => 'TRANSMISSÃO',
        'MANETE DE FREIO'                     => 'FREIO',
    ];

    public function handle(): int
    {
        $path = base_path($this->argument('file'));

        if (! file_exists($path)) {
            $this->error("Arquivo não encontrado: {$path}");
            return self::FAILURE;
        }

        // ── Lê o XLSX ───────────────────────────────────────────
        $this->info('Lendo planilha…');
        $rows = $this->readXlsx($path);

        if (empty($rows)) {
            $this->error('Não foi possível ler a planilha ou ela está vazia.');
            return self::FAILURE;
        }

        $total = count($rows);

        $offset = (int) $this->option('offset');
        if ($offset > 0) {
            $rows = array_slice($rows, $offset);
        }
        if ($limit = $this->option('limit')) {
            $rows = array_slice($rows, 0, (int) $limit);
        }

        $this->info("{$total} linhas na planilha · processando ".count($rows));

        // Aumenta o limite de memória para o lote grande
        @ini_set('memory_limit', '512M');

        // ── Pré-carrega categorias ──────────────────────────────
        $categories = Category::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [mb_strtoupper(trim($name)) => $id])
            ->all();

        if (empty($categories)) {
            $this->error('Nenhuma categoria cadastrada. Rode: php artisan db:seed --class=CategoriesSeeder');
            return self::FAILURE;
        }

        $dryRun     = $this->option('dry-run');
        $imagesOnly = $this->option('images-only');
        $withImages = $this->option('images') || $imagesOnly;

        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'images' => 0, 'imgFail' => 0];
        $unmapped = [];

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        $i = 0;
        foreach ($rows as $row) {
            // Libera memória periodicamente
            if (++$i % 100 === 0) {
                gc_collect_cycles();
            }
            $sku  = trim((string) ($row['SKU'] ?? ''));
            $name = trim((string) ($row['Nome'] ?? ''));

            if ($sku === '' || $name === '') {
                $stats['skipped']++;
                $bar->advance();
                continue;
            }

            // Preço
            $rawPrice = trim((string) ($row['Preço'] ?? ''));
            $price    = $rawPrice !== '' ? (float) str_replace(',', '.', $rawPrice) : null;

            // Descrições (limpa HTML e tabs do export)
            $aplicacao      = $this->cleanText($row['Metadado: aplicacao'] ?? '');
            $caracteristicas = $this->cleanText($row['Metadado: caracteristicas'] ?? '');

            // Categorias
            $catIds = [];
            foreach (explode(',', (string) ($row['Categorias'] ?? '')) as $raw) {
                $raw = mb_strtoupper(trim($raw));
                if ($raw === '') continue;

                $target = self::CATEGORY_MAP[$raw] ?? null;
                if (! $target) {
                    $unmapped[$raw] = ($unmapped[$raw] ?? 0) + 1;
                    continue;
                }
                if (isset($categories[$target])) {
                    $catIds[] = $categories[$target];
                }
            }
            $catIds = array_unique($catIds);

            if ($dryRun) {
                $stats['created']++;
                $bar->advance();
                continue;
            }

            // ── Persiste ────────────────────────────────────────
            $existing = Product::withTrashed()->where('sku', $sku)->first();

            if ($imagesOnly) {
                // Modo somente-imagens: não toca nos dados, só precisa do registro
                if (! $existing) {
                    $stats['skipped']++;
                    $bar->advance();
                    continue;
                }
                $product = $existing;
            } else {
                $data = [
                    'sku'               => $sku,
                    'name'              => $name,
                    'slug'              => $this->uniqueSlug($name, $sku),
                    'short_description' => $caracteristicas ?: null,
                    'description'       => $aplicacao ?: null,
                    'quote_price'       => $price,
                    'is_active'         => true,
                ];

                if ($existing) {
                    $existing->restore();
                    $existing->update($data);
                    $product = $existing;
                    $stats['updated']++;
                } else {
                    $product = Product::create($data);
                    $stats['created']++;
                }

                if (! empty($catIds)) {
                    $product->categories()->sync($catIds);
                }
            }

            // ── Imagem ──────────────────────────────────────────
            if ($withImages) {
                $url = trim((string) ($row['Imagens'] ?? ''));
                if ($url !== '' && ! Str::contains($url, 'Simagem')) {
                    if ($this->downloadImage($url, $sku, $product)) {
                        $stats['images']++;
                    } else {
                        $stats['imgFail']++;
                    }
                }
            }

            unset($product, $existing, $data, $catIds);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // ── Relatório ───────────────────────────────────────────
        $this->info('═══ Resultado ═══');
        $this->line("  Criados:    <fg=green>{$stats['created']}</>");
        $this->line("  Atualizados:<fg=yellow>{$stats['updated']}</>");
        $this->line("  Ignorados:  {$stats['skipped']}");

        if ($withImages) {
            $this->line("  Imagens OK: <fg=green>{$stats['images']}</>");
            $this->line("  Imagens falha: <fg=red>{$stats['imgFail']}</>");
        }

        if (! empty($unmapped)) {
            $this->newLine();
            $this->warn('Categorias sem mapeamento (produtos ficaram sem elas):');
            foreach ($unmapped as $cat => $n) {
                $this->line("  {$n}x  {$cat}");
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->comment('DRY RUN — nada foi gravado no banco.');
        }

        $this->newLine();
        return self::SUCCESS;
    }

    /** Lê XLSX sem dependência externa (descompacta e parseia o XML) */
    private function readXlsx(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        // Shared strings
        $shared = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $sx = simplexml_load_string($xml);
            foreach ($sx->si as $si) {
                $shared[] = (string) ($si->t ?? implode('', array_map(fn ($r) => (string) $r->t, iterator_to_array($si->r ?? []))));
            }
        }

        // Sheet
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) return [];

        $sx     = simplexml_load_string($sheetXml);
        $matrix = [];

        foreach ($sx->sheetData->row as $row) {
            $line = [];
            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                preg_match('/([A-Z]+)/', $ref, $m);
                $col = $this->colToIndex($m[1] ?? 'A');

                $v = (string) $c->v;
                if ((string) $c['t'] === 's') {
                    $v = $shared[(int) $v] ?? '';
                } elseif (isset($c->is->t)) {
                    $v = (string) $c->is->t;
                }
                $line[$col] = $v;
            }
            $matrix[] = $line;
        }

        if (empty($matrix)) return [];

        // Cabeçalho → chaves
        $headers = array_map(fn ($h) => trim((string) $h), $matrix[0]);
        $out     = [];

        foreach (array_slice($matrix, 1) as $line) {
            $assoc = [];
            foreach ($headers as $i => $h) {
                if ($h !== '') $assoc[$h] = $line[$i] ?? '';
            }
            if (array_filter($assoc)) $out[] = $assoc;
        }

        return $out;
    }

    private function colToIndex(string $col): int
    {
        $n = 0;
        foreach (str_split($col) as $ch) {
            $n = $n * 26 + (ord($ch) - 64);
        }
        return $n - 1;
    }

    /** Remove tags HTML, entidades e espaços/tabs extras do export */
    private function cleanText(?string $raw): string
    {
        if (! $raw) return '';
        $t = html_entity_decode(strip_tags((string) $raw), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $t = preg_replace('/[\t\r\n]+/', ' ', $t);
        return trim(preg_replace('/\s{2,}/', ' ', $t));
    }

    private function uniqueSlug(string $name, string $sku): string
    {
        return Str::slug($name.'-'.$sku);
    }

    /**
     * Baixa a imagem em STREAM direto para o disco.
     * Não carrega o arquivo inteiro em memória (evita exhaustion no lote grande).
     */
    private function downloadImage(string $url, string $sku, Product $product): bool
    {
        $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $ext = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? strtolower($ext) : 'jpg';

        $relative = 'products/'.Str::slug($sku).'.'.$ext;
        $absolute = Storage::disk('public')->path($relative);

        // Garante o diretório
        $dir = dirname($absolute);
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        // Já existe e tem conteúdo? Só vincula.
        if (is_file($absolute) && filesize($absolute) > 0) {
            if ($product->main_image !== $relative) {
                $product->updateQuietly(['main_image' => $relative]);
            }
            return true;
        }

        $tmp = $absolute.'.part';

        try {
            $in = @fopen($url, 'rb', false, stream_context_create([
                'http' => [
                    'timeout'          => 20,
                    'follow_location'  => 1,
                    'max_redirects'    => 3,
                    'user_agent'       => 'Mozilla/5.0 (compatible; TMAC-Importer/1.0)',
                    'ignore_errors'    => false,
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]));

            if (! $in) return false;

            $out = @fopen($tmp, 'wb');
            if (! $out) { fclose($in); return false; }

            // Copia em blocos de 256 KB — memória constante
            $bytes = 0;
            while (! feof($in)) {
                $chunk = fread($in, 262144);
                if ($chunk === false) break;
                fwrite($out, $chunk);
                $bytes += strlen($chunk);
                unset($chunk);
            }

            fclose($in);
            fclose($out);

            if ($bytes < 512) {           // arquivo vazio/inválido
                @unlink($tmp);
                return false;
            }

            @rename($tmp, $absolute);
            $product->updateQuietly(['main_image' => $relative]);

            return true;
        } catch (\Throwable $e) {
            @unlink($tmp);
            return false;
        }
    }
}
