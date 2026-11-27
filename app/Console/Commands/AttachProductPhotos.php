<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Vincula fotos aos produtos pelo nome do arquivo (= código do produto).
 *
 *   TM7250.webp      → foto principal do TM7250
 *   TM7250_1.webp    → foto extra (galeria) do TM7250
 *   C6002_3.webp     → sem a "principal": a de menor número vira a principal
 *
 * Se o arquivo usa o código ANTIGO de um item que teve o código trocado
 * (STATUS "COD XXX" na planilha de itens), liga ao código novo.
 *
 * Nunca apaga foto existente: se o produto já tem foto principal, as novas entram na galeria.
 * Pode rodar de novo sem duplicar.
 *
 *   php artisan products:attach-photos --dry-run
 *   php artisan products:attach-photos
 */
class AttachProductPhotos extends Command
{
    protected $signature = 'products:attach-photos
                            {dir=products/fotos-2026-11 : Pasta dentro de storage/app/public}
                            {--planilha=database/data/itens-fora-do-site.xlsx : Planilha com os códigos antigos (STATUS "COD XXX")}
                            {--dry-run : Simula sem gravar}';

    protected $description = 'Liga as fotos (nome do arquivo = código) aos produtos';

    public function handle(): int
    {
        $dir  = trim($this->argument('dir'), '/');
        $disk = Storage::disk('public');

        if (! $disk->exists($dir)) {
            $this->error("Pasta não encontrada: storage/app/public/{$dir}");
            return self::FAILURE;
        }

        $files = collect($disk->files($dir))
            ->filter(fn ($f) => preg_match('/\.(webp|jpe?g|png)$/i', $f))
            ->values();

        $antigoParaNovo = $this->codigosAntigos(base_path($this->option('planilha')));
        $dry = (bool) $this->option('dry-run');

        // Agrupa os arquivos por produto
        $grupos = [];   // product_id => [ [ordem, path], ... ]
        $semProduto = [];
        foreach ($files as $path) {
            $nome = strtoupper(pathinfo($path, PATHINFO_FILENAME));
            $base = preg_replace('/_\d+$/', '', $nome);
            $ordem = preg_match('/_(\d+)$/', $nome, $m) ? (int) $m[1] : 0;

            $produto = null;
            foreach (array_unique([$nome, $base]) as $codigo) {
                $produto = Product::withTrashed()->where('sku', $codigo)->first()
                    ?? (isset($antigoParaNovo[$codigo]) ? Product::withTrashed()->where('sku', $antigoParaNovo[$codigo])->first() : null);
                if ($produto) {
                    if ($codigo === $nome) $ordem = 0;   // o nome inteiro é o código: é a principal
                    break;
                }
            }

            if (! $produto) {
                $semProduto[] = basename($path);
                continue;
            }
            $grupos[$produto->id][] = [$ordem, $path];
        }

        $st = ['principal' => 0, 'galeria' => 0, 'ja_tinha' => 0];

        foreach ($grupos as $productId => $lista) {
            usort($lista, fn ($a, $b) => $a[0] <=> $b[0] ?: strcmp($a[1], $b[1]));
            $produto = Product::withTrashed()->find($productId);

            $temPrincipal = $produto->main_image && $disk->exists($produto->main_image);
            $ordemGaleria = (int) $produto->images()->max('sort_order');

            foreach ($lista as [, $path]) {
                if ($produto->main_image === $path || $produto->images()->where('path', $path)->exists()) {
                    $st['ja_tinha']++;
                    continue;
                }

                if (! $temPrincipal) {
                    $st['principal']++;
                    $temPrincipal = true;
                    if (! $dry) $produto->updateQuietly(['main_image' => $path]);
                    continue;
                }

                $st['galeria']++;
                if (! $dry) {
                    ProductImage::create([
                        'product_id' => $produto->id,
                        'path'       => $path,
                        'alt'        => $produto->name,
                        'sort_order' => ++$ordemGaleria,
                    ]);
                }
            }
        }

        $this->newLine();
        $this->info('═══ Fotos'.($dry ? ' (simulação — nada gravado)' : '').' ═══');
        $this->line('  Arquivos na pasta:          '.$files->count());
        $this->line('  Produtos com foto nova:     <fg=green>'.count($grupos).'</>');
        $this->line("  Viraram foto principal:     {$st['principal']}");
        $this->line("  Foram para a galeria:       {$st['galeria']}");
        $this->line("  Já estavam vinculadas:      {$st['ja_tinha']}");

        if ($semProduto) {
            $this->newLine();
            $this->warn(count($semProduto).' arquivo(s) sem produto com esse código (rode antes o products:import-faltantes):');
            foreach ($semProduto as $f) $this->line("  • {$f}");
        }
        $this->newLine();

        return self::SUCCESS;
    }

    /** Lê "COD XXX" da coluna STATUS: [código antigo => código novo] */
    private function codigosAntigos(string $path): array
    {
        if (! is_file($path)) return [];

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) return [];

        $shared = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            foreach (simplexml_load_string($xml)->si as $si) {
                if (isset($si->t)) {
                    $shared[] = (string) $si->t;
                } else {
                    $txt = '';
                    foreach ($si->r as $r) $txt .= (string) $r->t;
                    $shared[] = $txt;
                }
            }
        }
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($sheet === false) return [];

        $map = [];
        $first = true;
        foreach (simplexml_load_string($sheet)->sheetData->row as $row) {
            if ($first) { $first = false; continue; }
            $cols = [];
            foreach ($row->c as $c) {
                preg_match('/^([A-Z]+)/', (string) $c['r'], $m);
                $v = (string) $c->v;
                if ((string) $c['t'] === 's') $v = $shared[(int) $v] ?? '';
                elseif (isset($c->is->t)) $v = (string) $c->is->t;
                $cols[$m[1] ?? ''] = $v;
            }
            // A = COD, F = STATUS
            if (preg_match('/^COD\s+(\S+)/i', trim($cols['F'] ?? ''), $m)) {
                $map[strtoupper($m[1])] = strtoupper(trim($cols['A'] ?? ''));
            }
        }

        return $map;
    }
}
