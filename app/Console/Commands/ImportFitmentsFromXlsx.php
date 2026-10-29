<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\MotorcycleFitment;
use App\Models\MotorcycleMake;
use App\Models\MotorcycleModel;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportFitmentsFromXlsx extends Command
{
    protected $signature = 'fitments:import
                            {file=TESTE DE DESCRIÇÃO DE PRODUTOS TMAC.xlsx : XLSX na raiz do projeto}
                            {--create-missing : Cadastra modelos de moto que não existirem}
                            {--create-products : Cadastra produtos cujo SKU não existir no catálogo}
                            {--dry-run : Simula sem gravar}
                            {--limit= : Processa apenas N produtos}';

    protected $description = 'Importa compatibilidade produto↔moto das colunas APLICAÇÃO da planilha';

    /** Prefixo do modelo → marca da moto */
    private const MARCA_POR_PREFIXO = [
        'CG' => 'Honda', 'CB' => 'Honda', 'CBX' => 'Honda', 'CBR' => 'Honda',
        'CRF' => 'Honda', 'NXR' => 'Honda', 'NX' => 'Honda', 'XR' => 'Honda',
        'XLR' => 'Honda', 'XRE' => 'Honda', 'BIZ' => 'Honda', 'POP' => 'Honda',
        'PCX' => 'Honda', 'ADV' => 'Honda', 'ELITE' => 'Honda', 'LEAD' => 'Honda',
        'SH' => 'Honda', 'HORNET' => 'Honda', 'FALCON' => 'Honda', 'C' => 'Honda',
        'TWISTER' => 'Honda', 'SAHARA' => 'Honda', 'BROS' => 'Honda',

        'FZ' => 'Yamaha', 'FZ15' => 'Yamaha', 'FZ25' => 'Yamaha',
        'FACTOR' => 'Yamaha', 'FAZER' => 'Yamaha', 'NEO' => 'Yamaha',
        'RD' => 'Yamaha', 'RDZ' => 'Yamaha', 'XTZ' => 'Yamaha', 'YBR' => 'Yamaha',
        'CRYPTON' => 'Yamaha', 'LANDER' => 'Yamaha', 'TENERE' => 'Yamaha',
        'MT' => 'Yamaha', 'XJ' => 'Yamaha', 'YS' => 'Yamaha', 'TDM' => 'Yamaha',

        'GSR' => 'Suzuki', 'EN' => 'Suzuki', 'YES' => 'Suzuki',
        'INTRUDER' => 'Suzuki', 'BURGMAN' => 'Suzuki', 'GSX' => 'Suzuki',
        'VSTROM' => 'Suzuki', 'DL' => 'Suzuki',

        'NINJA' => 'Kawasaki', 'Z' => 'Kawasaki', 'VERSYS' => 'Kawasaki',
        'GF' => 'Kasinski', 'COMET' => 'Kasinski', 'MIRAGE' => 'Kasinski',
        'DAFRA' => 'Dafra', 'APACHE' => 'Dafra', 'CITYCOM' => 'Dafra',
        'SHINERAY' => 'Shineray', 'HAOJUE' => 'Haojue',
    ];

    /** Sufixos de versão que não diferenciam o modelo na nossa base */
    private const SUFIXOS_VERSAO = '/\b(KS|ES|ESD|ESI|ESDI|ESDD|EX|KSE|ED|SED|SE|CARGO|FLEX|DX|START|PLUS)\b/';

    public function handle(): int
    {
        @ini_set('memory_limit', '512M');

        $path = base_path($this->argument('file'));
        if (! file_exists($path)) {
            $this->error("Arquivo não encontrado: {$path}");
            return self::FAILURE;
        }

        $this->info('Lendo planilha…');
        $rows = $this->readXlsx($path);
        if (empty($rows)) {
            $this->error('Planilha vazia ou ilegível.');
            return self::FAILURE;
        }

        if ($limit = $this->option('limit')) {
            $rows = array_slice($rows, 0, (int) $limit);
        }

        $dry     = $this->option('dry-run');
        $criar   = $this->option('create-missing');

        // Índices de busca de modelos
        $modelos = MotorcycleModel::with('make')->get();
        $porNome  = [];
        $porChave = [];
        foreach ($modelos as $m) {
            $porNome[$this->norm($m->name)] = $m;
            $porChave[$this->chave($m->name)] ??= $m;
        }

        $this->info(count($rows).' produtos na planilha · '.$modelos->count().' modelos cadastrados');
        $this->newLine();

        $st = [
            'produtos_ok' => 0, 'produtos_sem_sku' => 0, 'sku_fuzzy' => 0,
            'produtos_criados' => 0,
            'fitments' => 0, 'fitments_dup' => 0,
            'modelos_criados' => 0, 'apps_sem_match' => 0,
        ];
        $semMatch     = [];
        $criados      = [];
        $skusFaltando = [];
        $prodCriados  = [];

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $row) {
            $sku = trim((string) ($row['codigo_base'] ?? ''));
            if ($sku === '') { $bar->advance(); continue; }

            $produto = Product::where('sku', $sku)->first();

            // Tentativa tolerante: ignora caixa e espaços/traços
            if (! $produto) {
                $alvo = strtoupper(preg_replace('/[\s\-\.]/', '', $sku));
                $produto = Product::whereRaw(
                    "UPPER(REPLACE(REPLACE(REPLACE(sku,' ',''),'-',''),'.','')) = ?", [$alvo]
                )->first();
                if ($produto) $st['sku_fuzzy']++;
            }

            // Cria o produto se não existir e a flag estiver ativa
            if (! $produto && $this->option('create-products')) {
                if ($dry) {
                    $st['produtos_criados']++;
                    $prodCriados[$sku] = trim((string) ($row['descricao'] ?? $sku));
                    $bar->advance();
                    continue;   // no dry-run não há produto real para vincular
                }
                $produto = $this->criarProduto($row, $sku);
                if ($produto) {
                    $st['produtos_criados']++;
                    $prodCriados[$sku] = $produto->name;
                }
            }

            if (! $produto) {
                $st['produtos_sem_sku']++;
                $skusFaltando[] = $sku;
                $bar->advance();
                continue;
            }
            $st['produtos_ok']++;

            // APLICAÇÃO 01 em diante (a 00 é o texto completo concatenado)
            for ($i = 1; $i <= 33; $i++) {
                $raw = trim((string) ($row["APLICAÇÃO ".str_pad($i, 2, '0', STR_PAD_LEFT)] ?? ''));
                if ($raw === '') continue;

                $parsed = $this->parseAplicacao($raw);
                if (! $parsed) { $st['apps_sem_match']++; $semMatch[$raw] = true; continue; }

                [$nomeModelo, $de, $ate] = $parsed;

                // Busca o modelo
                $modelo = $porNome[$this->norm($nomeModelo)]
                       ?? $porChave[$this->chave($nomeModelo)]
                       ?? null;

                if (! $modelo) {
                    if (! $criar) {
                        $st['apps_sem_match']++;
                        $semMatch[$nomeModelo] = true;
                        continue;
                    }
                    if ($dry) {
                        // Conta o modelo só na primeira vez, mas sempre conta o fitment
                        if (! isset($criados[$nomeModelo])) {
                            $criados[$nomeModelo] = $this->inferirMarca($nomeModelo);
                            $st['modelos_criados']++;
                        }
                        $st['fitments']++;
                        continue;
                    }
                    $modelo = $this->criarModelo($nomeModelo, $de, $ate);
                    if (! $modelo) { $st['apps_sem_match']++; $semMatch[$nomeModelo] = true; continue; }

                    $porNome[$this->norm($modelo->name)]   = $modelo;
                    $porChave[$this->chave($modelo->name)] ??= $modelo;
                    $criados[$modelo->name] = $modelo->make?->name;
                    $st['modelos_criados']++;
                }

                if ($dry) { $st['fitments']++; continue; }

                $f = MotorcycleFitment::firstOrNew([
                    'product_id'          => $produto->id,
                    'motorcycle_model_id' => $modelo->id,
                    'year_from'           => $de,
                ]);

                if ($f->exists) { $st['fitments_dup']++; continue; }

                $f->year_to = $ate;
                $f->notes   = Str::limit($raw, 250);
                $f->save();
                $st['fitments']++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // ── Relatório ──
        $this->info('═══ Resultado ═══');
        $this->line("  Produtos encontrados pelo SKU : <fg=green>{$st['produtos_ok']}</>");
        if ($st['sku_fuzzy']) {
            $this->line("    └ casados ignorando formato : {$st['sku_fuzzy']}");
        }
        if ($st['produtos_criados']) {
            $this->line("  Produtos cadastrados          : <fg=cyan>{$st['produtos_criados']}</>");
        }
        $this->line("  SKUs não encontrados no banco : <fg=yellow>{$st['produtos_sem_sku']}</>");
        $this->line("  Compatibilidades criadas      : <fg=green>{$st['fitments']}</>");
        if ($st['fitments_dup'])    $this->line("  Já existiam                   : {$st['fitments_dup']}");
        if ($st['modelos_criados']) $this->line("  Modelos de moto cadastrados   : <fg=cyan>{$st['modelos_criados']}</>");
        if ($st['apps_sem_match'])  $this->line("  Aplicações sem modelo         : <fg=red>{$st['apps_sem_match']}</>");

        if (! empty($prodCriados)) {
            $this->newLine();
            $this->comment('Produtos '.($dry ? 'que seriam cadastrados' : 'cadastrados').':');
            foreach (array_slice($prodCriados, 0, 20, true) as $s => $n) {
                $this->line("  • <fg=cyan>{$s}</>  ".Str::limit($n, 70));
            }
            if (count($prodCriados) > 20) $this->line('  … e mais '.(count($prodCriados) - 20));
        }

        if (! empty($criados)) {
            $this->newLine();
            $this->comment('Modelos '.($dry ? 'que seriam criados' : 'criados').':');
            foreach (array_slice($criados, 0, 30, true) as $nome => $marca) {
                $this->line("  • {$nome}  <fg=gray>({$marca})</>");
            }
            if (count($criados) > 30) $this->line('  … e mais '.(count($criados) - 30));
        }

        if (! empty($semMatch)) {
            $this->newLine();
            $this->warn('Sem correspondência'.($criar ? '' : ' — rode com --create-missing para cadastrá-los').':');
            foreach (array_slice(array_keys($semMatch), 0, 25) as $s) $this->line("  • {$s}");
            if (count($semMatch) > 25) $this->line('  … e mais '.(count($semMatch) - 25));
        }

        if (! empty($skusFaltando)) {
            $this->newLine();
            $this->warn('SKUs da planilha que NÃO existem no catálogo ('.count($skusFaltando).'):');
            $this->line('  '.implode('  ', array_slice($skusFaltando, 0, 60)));
            if (count($skusFaltando) > 60) $this->line('  … e mais '.(count($skusFaltando) - 60));
            $this->newLine();
            $this->line('  <fg=gray>Esses produtos precisam ser cadastrados antes de receberem compatibilidade.</>');
        }

        if ($dry) {
            $this->newLine();
            $this->comment('DRY RUN — nada foi gravado.');
        }

        $this->newLine();
        return self::SUCCESS;
    }

    /** Mapeia o campo "grupo" da planilha para as categorias oficiais */
    private const GRUPO_PARA_CATEGORIA = [
        'SUSPENSAO'             => 'SUSPENSÃO',
        'SUSPENSÃO'             => 'SUSPENSÃO',
        'ELETRICO/ELETRONICO'   => 'ELÉTRICA',
        'ELETRICO'              => 'ELÉTRICA',
        'ELETRONICO'            => 'ELÉTRICA',
        'MOTOR'                 => 'MOTOR',
        'FREIO'                 => 'FREIO',
        'TRANSMISSAO'           => 'TRANSMISSÃO',
        'CHASSI'                => 'CHASSI',
        'CARENAGEM'             => 'CARENAGEM',
        'ACESSORIOS'            => 'ACESSÓRIOS',
        'RODA'                  => 'RODA',
        'FIXACAO'               => 'FIXAÇÃO',
        'INJECAO'               => 'INJEÇÃO',
        'CABOS DE COMANDO'      => 'CABOS DE COMANDO',
    ];

    /** Palavras no nome do produto → categoria (fallback quando não há grupo) */
    private const PALAVRA_PARA_CATEGORIA = [
        'AMORTECEDOR'   => 'SUSPENSÃO',
        'BENGALA'       => 'SUSPENSÃO',
        'MOLA'          => 'SUSPENSÃO',
        'RETENTOR'      => 'SUSPENSÃO',
        'CABO'          => 'CABOS DE COMANDO',
        'PASTILHA'      => 'FREIO',
        'DISCO DE FREIO'=> 'FREIO',
        'LONA'          => 'FREIO',
        'PISTAO'        => 'MOTOR',
        'PISTÃO'        => 'MOTOR',
        'VELA'          => 'MOTOR',
        'JUNTA'         => 'MOTOR',
        'VIRABREQUIM'   => 'MOTOR',
        'CORRENTE'      => 'TRANSMISSÃO',
        'COROA'         => 'TRANSMISSÃO',
        'PINHAO'        => 'TRANSMISSÃO',
        'EMBREAGEM'     => 'TRANSMISSÃO',
        'FAROL'         => 'ELÉTRICA',
        'LAMPADA'       => 'ELÉTRICA',
        'LÂMPADA'       => 'ELÉTRICA',
        'PISCA'         => 'ELÉTRICA',
        'BATERIA'       => 'ELÉTRICA',
        'CDI'           => 'ELÉTRICA',
        'VELOCIMETRO'   => 'CHASSI',
        'VELOCÍMETRO'   => 'CHASSI',
        'PAINEL'        => 'CHASSI',
        'GUIDAO'        => 'CHASSI',
        'RETROVISOR'    => 'ACESSÓRIOS',
        'MANOPLA'       => 'ACESSÓRIOS',
        'PNEU'          => 'RODA',
        'ARO'           => 'RODA',
        'RAIO'          => 'RODA',
    ];

    /** Cria o produto a partir da linha da planilha */
    private function criarProduto(array $row, string $sku): ?Product
    {
        $nome = trim((string) ($row['descricao'] ?? ''));
        if ($nome === '') return null;

        $reduzida = trim((string) ($row['descricao_reduzida'] ?? ''));
        $partNum  = trim((string) ($row['part_number'] ?? ''));
        $grupo    = $this->norm((string) ($row['grupo'] ?? ''));

        // Descrição: códigos originais ajudam o lojista a localizar a peça
        $descricao = null;
        if ($partNum !== '') {
            $codigos = preg_split('/\s*\*\s*/', $partNum, -1, PREG_SPLIT_NO_EMPTY);
            $descricao = 'Códigos originais: '.implode(' · ', array_map('trim', $codigos));
        }

        $produto = Product::create([
            'sku'               => $sku,
            'name'              => Str::limit($nome, 250, ''),
            'slug'              => Str::slug(Str::limit($nome, 180, '').'-'.$sku),
            'short_description' => $reduzida ?: null,
            'description'       => $descricao,
            'is_active'         => true,
        ]);

        // Vincula categoria pelo grupo, ou infere pelo nome
        $catNome = self::GRUPO_PARA_CATEGORIA[$grupo] ?? null;

        if (! $catNome) {
            $nomeUp = $this->norm($nome);
            foreach (self::PALAVRA_PARA_CATEGORIA as $palavra => $cat) {
                if (str_contains($nomeUp, $this->norm($palavra))) { $catNome = $cat; break; }
            }
        }

        if ($catNome) {
            $cat = Category::where('name', $catNome)->first();
            if ($cat) $produto->categories()->sync([$cat->id]);
        }

        return $produto;
    }

    /** "BIZ 125 KS 06/12" → ['BIZ 125 KS', 2006, 2012] */
    private function parseAplicacao(string $raw): ?array
    {
        // Remove sufixos técnicos que não fazem parte do modelo
        $t = preg_replace('/\((PAR|PRO-LINK[^)]*|A GAS|[^)]*GAS[^)]*)\)/i', '', $raw);
        $t = preg_replace('/\s*-\s*(MOLAS?\s+[\p{L}\s\/]+|PREMIUM|COM REGULAGEM|SEM REGULAGEM)\s*$/iu', '', $t);
        $t = trim(preg_replace('/\s{2,}/', ' ', $t), " -\u{B7}");

        // "MODELO AA/AA" ou "MODELO AA/..."
        if (preg_match('#^(.*?)\s+(\d{2})\s*/\s*(\d{2}|\.{2,3})\s*$#', $t, $m)) {
            return [trim($m[1]), $this->ano($m[2]), str_contains($m[3], '.') ? null : $this->ano($m[3])];
        }
        // "MODELO 2012"
        if (preg_match('/^(.*?)\s+(\d{4})\s*$/', $t, $m)) {
            return [trim($m[1]), (int) $m[2], (int) $m[2]];
        }
        return null;
    }

    private function ano(string $aa): int
    {
        $n = (int) $aa;
        return $n >= 50 ? 1900 + $n : 2000 + $n;
    }

    private function norm(string $t): string
    {
        $t = Str::ascii($t);
        $t = strtoupper(str_replace('-', ' ', $t));
        $t = preg_replace('/[^A-Z0-9 ]/', ' ', $t);
        return trim(preg_replace('/\s+/', ' ', $t));
    }

    /** Normalização agressiva: remove sufixos de versão para casar variantes */
    private function chave(string $t): string
    {
        $k = $this->norm($t);
        $k = preg_replace(self::SUFIXOS_VERSAO, '', $k);
        $k = preg_replace('/\bC\s*100\b/', 'BIZ 100', $k);  // C-100 BIZ = Biz 100
        $k = preg_replace('/\b(\d+)I\b/', '$1', $k);        // 110I → 110
        return trim(preg_replace('/\s+/', ' ', $k));
    }

    private function inferirMarca(string $nomeModelo): string
    {
        $up = $this->norm($nomeModelo);

        foreach (self::MARCA_POR_PREFIXO as $pref => $marca) {
            if (preg_match('/\b'.preg_quote($pref, '/').'\b/', $up)) {
                return $marca;
            }
        }
        return 'Outros';
    }

    private function criarModelo(string $nome, ?int $de, ?int $ate): ?MotorcycleModel
    {
        $marcaNome = $this->inferirMarca($nome);

        $make = MotorcycleMake::firstOrCreate(
            ['slug' => Str::slug($marcaNome)],
            ['name' => $marcaNome, 'is_active' => true, 'sort_order' => 99]
        );

        $nomeFmt = Str::title(strtolower($nome));

        return MotorcycleModel::firstOrCreate(
            ['slug' => Str::slug($nome)],
            [
                'motorcycle_make_id' => $make->id,
                'name'       => $nomeFmt,
                'year_start' => $de,
                'year_end'   => $ate,
                'is_active'  => true,
            ]
        );
    }

    /** Leitor XLSX nativo (ZipArchive + SimpleXML) */
    private function readXlsx(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) return [];

        $shared = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $sx = simplexml_load_string($xml);
            foreach ($sx->si as $si) {
                $shared[] = (string) ($si->t ?? implode('', array_map(
                    fn ($r) => (string) $r->t,
                    iterator_to_array($si->r ?? [])
                )));
            }
        }

        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($sheet === false) return [];

        $sx = simplexml_load_string($sheet);
        $matrix = [];
        foreach ($sx->sheetData->row as $row) {
            $line = [];
            foreach ($row->c as $c) {
                preg_match('/([A-Z]+)/', (string) $c['r'], $m);
                $col = $this->colToIndex($m[1] ?? 'A');
                $v = (string) $c->v;
                if ((string) $c['t'] === 's')      $v = $shared[(int) $v] ?? '';
                elseif (isset($c->is->t))          $v = (string) $c->is->t;
                $line[$col] = $v;
            }
            $matrix[] = $line;
        }
        if (empty($matrix)) return [];

        $headers = array_map(fn ($h) => trim((string) $h), $matrix[0]);
        $out = [];
        foreach (array_slice($matrix, 1) as $line) {
            $a = [];
            foreach ($headers as $i => $h) if ($h !== '') $a[$h] = $line[$i] ?? '';
            if (array_filter($a)) $out[] = $a;
        }
        return $out;
    }

    private function colToIndex(string $col): int
    {
        $n = 0;
        foreach (str_split($col) as $ch) $n = $n * 26 + (ord($ch) - 64);
        return $n - 1;
    }
}
