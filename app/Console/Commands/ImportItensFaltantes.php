<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\TmacBrand;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Importa a planilha "Itens fora do site TMAC" (cliente, 27/nov/2026).
 * Colunas: COD | DESCRIÇÃO | MARCA | GRUPO | TAG | STATUS | OBS
 *
 * Regras:
 *  - STATUS "COD XXX": o item já está no site com o código antigo XXX → só troca o código
 *    (mantém nome, fotos, URL e compatibilidades). Não duplica.
 *  - OBS com código duplicado (ex.: "TM8021TM8021") → corrige o código do produto existente.
 *  - Demais linhas: cadastra o produto novo, com marca (Universo TMAC) e categoria.
 *  - "FORA DE LINHA" (no STATUS ou no fim da descrição): o produto fica INATIVO (fora do site).
 *  - TAG "LANÇAMENTO": selo "Lançamento" no card.
 *
 * Pode rodar mais de uma vez: na segunda vez só atualiza.
 *
 *   php artisan products:import-faltantes --dry-run    (simula e mostra o resumo)
 *   php artisan products:import-faltantes
 */
class ImportItensFaltantes extends Command
{
    protected $signature = 'products:import-faltantes
                            {file=database/data/itens-fora-do-site.xlsx : XLSX relativo à raiz do projeto}
                            {--dry-run : Simula sem gravar}';

    protected $description = 'Cadastra os itens que faltavam no site (planilha do cliente) e corrige códigos antigos';

    /** MARCA da planilha → slug da marca no Universo TMAC */
    private const MARCAS = [
        'TMAC' => 'tmac', 'ATROX' => 'atrox', 'CORAMI' => 'corami',
        'LBJ' => 'lbj', 'MOTOLED' => 'motoled', 'LED' => 'motoled',
    ];

    /**
     * Início do nome do produto → categorias. Vem antes do GRUPO porque o grupo da planilha
     * é inconsistente (ex.: manete de freio no grupo "DIRECAO", bico injetor em "MECANICA").
     * Ordem importa: o primeiro que casar vence.
     */
    private const POR_NOME = [
        // Iluminação: entram em Elétrica e também em Iluminação
        'BLOCO OPTICO' => ['ELÉTRICA', 'ILUMINAÇÃO'], 'FAROL' => ['ELÉTRICA', 'ILUMINAÇÃO'],
        'LANTERNA' => ['ELÉTRICA', 'ILUMINAÇÃO'], 'LAMPADA' => ['ELÉTRICA', 'ILUMINAÇÃO'],
        'PISCA' => ['ELÉTRICA', 'ILUMINAÇÃO'], 'ARO DO FAROL' => ['ELÉTRICA', 'ILUMINAÇÃO'],
        'MOLDURA DE FAROL' => ['ELÉTRICA', 'ILUMINAÇÃO'], 'MOLDURA DO FAROL' => ['ELÉTRICA', 'ILUMINAÇÃO'],
        'SOQUETE' => ['ELÉTRICA', 'ILUMINAÇÃO'], 'MOLDURA SUPERIOR DO FAROL' => ['ELÉTRICA', 'ILUMINAÇÃO'],

        // Freio
        'MANETE DE FREIO' => ['FREIO'], 'MANICOTO DE FREIO' => ['FREIO'], 'PEDAL DE FREIO' => ['FREIO'],
        'PASTILHA' => ['FREIO'], 'DISCO DE FREIO' => ['FREIO'], 'CILINDRO DE FREIO' => ['FREIO'],
        'CONJUNTO DE FREIO' => ['FREIO'], 'SAPATA' => ['FREIO'], 'LONA DE FREIO' => ['FREIO'],

        // Transmissão
        'CAPA CORRENTE' => ['TRANSMISSÃO'], 'CAPA DE CORRENTE' => ['TRANSMISSÃO'],
        'MANETE DE EMBREAGEM' => ['TRANSMISSÃO'], 'MANICOTO DE EMBREAGEM' => ['TRANSMISSÃO'],
        'PLACA ACIONADORA' => ['TRANSMISSÃO'], 'DISCO DE EMBREAGEM' => ['TRANSMISSÃO'],
        'CAMPANA' => ['TRANSMISSÃO'], 'CUBO DE EMBREAGEM' => ['TRANSMISSÃO'], 'EMBREAGEM' => ['TRANSMISSÃO'],
        'PEDAL DE CAMBIO' => ['TRANSMISSÃO'], 'KIT TRANSMISSAO' => ['TRANSMISSÃO'], 'KIT RELACAO' => ['TRANSMISSÃO'],
        'CORRENTE DE TRANSMISSAO' => ['TRANSMISSÃO'], 'COROA' => ['TRANSMISSÃO'], 'PINHAO' => ['TRANSMISSÃO'],
        'CORREIA' => ['TRANSMISSÃO'], 'ROLDANA' => ['TRANSMISSÃO'], 'ESPACADOR DO ROLAMENTO DA COROA' => ['TRANSMISSÃO'],
        'TAMPA DO PINHAO' => ['TRANSMISSÃO'],

        // Suspensão
        'BENGALA' => ['SUSPENSÃO'], 'AMORTECEDOR' => ['SUSPENSÃO'], 'TUBO INTERNO' => ['SUSPENSÃO'],
        'TUBO EXTERNO' => ['SUSPENSÃO'], 'MOLA' => ['SUSPENSÃO'], 'BUCHA DA BALANCA' => ['SUSPENSÃO'],
        'BRACO OSCILANTE' => ['SUSPENSÃO'], 'BALANCA' => ['SUSPENSÃO'], 'SANFONA' => ['SUSPENSÃO'],

        'CABO' => ['CABOS DE COMANDO'],

        // Injeção
        'BICO INJETOR' => ['INJEÇÃO'], 'JUNCAO DO BICO' => ['INJEÇÃO'], 'CORPO DE INJECAO' => ['INJEÇÃO'], 'BOMBA DE COMBUSTIVEL' => ['INJEÇÃO'],
        'SENSOR' => ['INJEÇÃO'], 'FILTRO DE COMBUSTIVEL' => ['INJEÇÃO'],

        // Elétrica
        'MOTOR DE PARTIDA' => ['ELÉTRICA'], 'INTERRUPTOR' => ['ELÉTRICA'], 'CHAVE' => ['ELÉTRICA'],
        'CDI' => ['ELÉTRICA'], 'CHICOTE' => ['ELÉTRICA'], 'FIACAO' => ['ELÉTRICA'], 'BUZINA' => ['ELÉTRICA'], 'BOIA DE TANQUE' => ['ELÉTRICA'],
        'ALARME' => ['ELÉTRICA'], 'CAXIMBO' => ['ELÉTRICA'], 'CACHIMBO' => ['ELÉTRICA'], 'REGULADOR' => ['ELÉTRICA'],
        'RELE' => ['ELÉTRICA'], 'ESTATOR' => ['ELÉTRICA'], 'BOBINA' => ['ELÉTRICA'], 'BATERIA' => ['ELÉTRICA'],

        // Roda
        'RODA' => ['RODA'], 'ARO DE RODA' => ['RODA'], 'CAMARA DE AR' => ['RODA'], 'CUBO DE RODA' => ['RODA'],
        'RAIO' => ['RODA'], 'PNEU' => ['RODA'],

        // Fixação
        'PARAFUSO' => ['FIXAÇÃO'], 'PORCA' => ['FIXAÇÃO'], 'ARRUELA' => ['FIXAÇÃO'], 'ABRACADEIRA' => ['FIXAÇÃO'],
        'CUPILHA' => ['FIXAÇÃO'], 'PRESILHA' => ['FIXAÇÃO'], 'PINO' => ['FIXAÇÃO'],

        // Acessórios
        'BAULETO' => ['ACESSÓRIOS'], 'RETROVISOR' => ['ACESSÓRIOS'], 'ADAPTADOR (ALONGADOR) DE RETROVISOR' => ['ACESSÓRIOS'],
        'ANTENA' => ['ACESSÓRIOS'], 'LUVA' => ['ACESSÓRIOS'], 'MANOPLA' => ['ACESSÓRIOS'], 'TRAVA' => ['ACESSÓRIOS'],
        'CADEADO' => ['ACESSÓRIOS'], 'CONTRAPESO' => ['ACESSÓRIOS'], 'SUPORTE DE CELULAR' => ['ACESSÓRIOS'],
        'CARREGADOR' => ['ACESSÓRIOS'], 'INTERCOMUNICADOR' => ['ACESSÓRIOS'], 'FONE' => ['ACESSÓRIOS'], 'MOCHILA' => ['ACESSÓRIOS'],
        'BOLSA' => ['ACESSÓRIOS'], 'ENCOSTO' => ['ACESSÓRIOS'], 'MANETE' => ['ACESSÓRIOS'],

        // Carenagem
        'PARABRISA' => ['CARENAGEM'], 'SUPORTE AJUSTAVEL DE PARABRISA' => ['CARENAGEM'], 'REFLETOR' => ['CARENAGEM'],
        'TAMPA DE ACABAMENTO' => ['CARENAGEM'], 'PARALAMA' => ['CARENAGEM'], 'CARENAGEM' => ['CARENAGEM'],

        // Chassi
        'GUIDAO' => ['CHASSI'], 'COLUNA DE DIRECAO' => ['CHASSI'], 'CAIXA DE DIRECAO' => ['CHASSI'],
        'GUARDA PO DA COLUNA' => ['CHASSI'], 'PEDALEIRA' => ['CHASSI'], 'PEDAL DE APOIO' => ['CHASSI'],
        'PAINEL' => ['CHASSI'], 'VELOCIMETRO' => ['CHASSI'], 'KIT ENGRENAGEM DE VELOCIMETRO' => ['CHASSI'],
        'ENGRENAGEM DO VELOCIMETRO' => ['CHASSI'], 'SUPORTE' => ['CHASSI'], 'TAMPA DO TANQUE' => ['CHASSI'],
        'COXIM' => ['CHASSI'], 'CARCACA DO ACELERADOR' => ['CHASSI'], 'PROTETOR DE ESCAPAMENTO' => ['CHASSI'],
        'CAVALETE' => ['CHASSI'], 'DESCANSO' => ['CHASSI'], 'PEDAL' => ['CHASSI'],

        'COMPRESSOR' => ['FERRAMENTAS E EQUIPAMENTOS'],

        // Rolamento: roda ou motor (resolvido em categorias())
        'ROLAMENTO' => [],

        // Motor (o resto do "coração" da moto)
        'BALANCIM' => ['MOTOR'], 'ARVORE' => ['MOTOR'], 'CARBURADOR' => ['MOTOR'], 'BIELA' => ['MOTOR'],
        'FILTRO' => ['MOTOR'], 'CONDUTOR DE AR' => ['MOTOR'], 'TAMPA DO FILTRO' => ['MOTOR'], 'VELA' => ['MOTOR'],
        'BOMBA DE OLEO' => ['MOTOR'], 'BOMBA DE AGUA' => ['MOTOR'], 'ENGRENAGEM' => ['MOTOR'], 'ACIONADOR' => ['MOTOR'],
        'TENSOR' => ['MOTOR'], 'GUIA' => ['MOTOR'], 'ANEL DE VEDACAO' => ['MOTOR'], 'RETENTOR' => ['MOTOR'],
        'MEDIDOR DE OLEO' => ['MOTOR'], 'VALVULA' => ['MOTOR'], 'PISTAO' => ['MOTOR'], 'KIT PISTAO' => ['MOTOR'],
        'JUNTA' => ['MOTOR'], 'CAMBIO' => ['MOTOR'], 'EIXO' => ['MOTOR'], 'AGULHA' => ['MOTOR'], 'BOIA' => ['MOTOR'],
        'VIRABREQUIM' => ['MOTOR'], 'CILINDRO' => ['MOTOR'], 'CABECOTE' => ['MOTOR'], 'MANICOTO' => ['MOTOR'],
    ];

    /** GRUPO da planilha → categoria (quando o nome não resolveu) */
    private const POR_GRUPO = [
        'ACESSORIO' => ['ACESSÓRIOS'], 'MANOPLA' => ['ACESSÓRIOS'], 'TRAVAS' => ['ACESSÓRIOS'], 'LUVA' => ['ACESSÓRIOS'],
        'MOCHILA' => ['ACESSÓRIOS'], 'CONTRAPESO' => ['ACESSÓRIOS'], 'ACABAMENTO' => ['CHASSI'],
        'RODA' => ['RODA'], 'ARO DE RODA' => ['RODA'], 'CAMARA DE AR' => ['RODA'], 'CUBO DE RODA' => ['RODA'], 'ROLAMENTO' => ['RODA'],
        'PARAFUSO' => ['FIXAÇÃO'], 'PORCA' => ['FIXAÇÃO'], 'ARRUELA' => ['FIXAÇÃO'], 'ABRACADEIRA' => ['FIXAÇÃO'],
        'CUPILHA' => ['FIXAÇÃO'], 'PRESILHA' => ['FIXAÇÃO'],
        'DIRECAO' => ['CHASSI'], 'COLUNA DE DIRECAO' => ['CHASSI'], 'CHASSI' => ['CHASSI'], 'CHASSIS' => ['CHASSI'],
        'SUPORTE' => ['CHASSI'], 'SUPORTES' => ['CHASSI'], 'PEDAL' => ['CHASSI'], 'GUARDA PO' => ['CHASSI'],
        'FREIO' => ['FREIO'], 'SUSPENSAO' => ['SUSPENSÃO'], 'SUSPENCAO' => ['SUSPENSÃO'], 'BUCHA' => ['SUSPENSÃO'],
        'ELETRICA' => ['ELÉTRICA'], 'ELETRICO' => ['ELÉTRICA'], 'SENSORES' => ['INJEÇÃO'], 'PLACA DE PARTIDA' => ['TRANSMISSÃO'],
        'ILUMINACAO' => ['ELÉTRICA', 'ILUMINAÇÃO'], 'PISCA LED' => ['ELÉTRICA', 'ILUMINAÇÃO'],
        'EMBREAGEM' => ['TRANSMISSÃO'], 'KIT TRANSMISSAO' => ['TRANSMISSÃO'],
        'MECANICA' => ['MOTOR'], 'MOTOR' => ['MOTOR'], 'TAMPAS' => ['MOTOR'], 'TAMPA' => ['CHASSI'],
        'CABOS' => ['CABOS DE COMANDO'], 'CABO' => ['CABOS DE COMANDO'], 'PARABRISA' => ['CARENAGEM'],
        'CHICOTE' => ['ELÉTRICA'], 'INTERCOMUNICADOR EXTERNO' => ['ACESSÓRIOS'], 'BICO INJETOR' => ['INJEÇÃO'],
        'MOLDURA DE FAROL' => ['ELÉTRICA', 'ILUMINAÇÃO'], 'CARREGADOR DE CELULARES' => ['ACESSÓRIOS'],
    ];

    public function handle(): int
    {
        $path = base_path($this->argument('file'));
        if (! is_file($path)) {
            $this->error("Arquivo não encontrado: {$path}");
            return self::FAILURE;
        }

        $rows = $this->readXlsx($path);
        if (! $rows) {
            $this->error('Planilha vazia ou ilegível.');
            return self::FAILURE;
        }

        $dry    = (bool) $this->option('dry-run');
        $cats   = Category::pluck('id', 'name')->mapWithKeys(fn ($id, $n) => [mb_strtoupper(trim($n)) => $id])->all();
        $brands = TmacBrand::pluck('id', 'slug')->all();

        $st = ['novos' => 0, 'atualizados' => 0, 'codigo_trocado' => 0, 'inativos' => 0, 'lancamentos' => 0];
        $semCategoria = [];
        $codigoAntigoNaoAchado = [];

        foreach ($rows as $row) {
            $sku  = strtoupper(trim((string) ($row['COD'] ?? '')));
            $desc = trim((string) ($row['DESCRIÇÃO'] ?? $row['DESCRICAO'] ?? ''));
            if ($sku === '' || $desc === '') continue;

            $status = mb_strtoupper(trim((string) ($row['STATUS'] ?? '')));
            $obs    = trim((string) ($row['OBS'] ?? ''));
            $marca  = mb_strtoupper(trim((string) ($row['MARCA'] ?? '')));
            $grupo  = $this->norm((string) ($row['GRUPO'] ?? ''));
            $launch = str_contains($this->norm((string) ($row['TAG'] ?? '')), 'LANCAMENTO');

            $foraDeLinha = str_contains($status, 'FORA DE LINHA') || (bool) preg_match('/FORA DE LINHA\s*$/iu', $desc);
            $nome    = trim(preg_replace('/\s*-?\s*FORA DE LINHA\s*$/iu', '', $desc));
            $brandId = $brands[self::MARCAS[$marca] ?? ''] ?? null;

            // Código antigo no site: "COD XXX" no STATUS, ou duplicado citado na OBS
            $antigo = null;
            if (preg_match('/^COD\s+(\S+)/u', $status, $m)) {
                $antigo = strtoupper($m[1]);
            } elseif (preg_match('/c[óo]digo duplicado\s*"([^"]+)"/iu', $obs, $m)) {
                $antigo = strtoupper(trim($m[1]));
            }

            $produto = Product::withTrashed()->where('sku', $sku)->first();

            // 1) Troca de código de um produto que já está no site
            if (! $produto && $antigo) {
                $existente = Product::withTrashed()->where('sku', $antigo)->first();
                if ($existente) {
                    $st['codigo_trocado']++;
                    if ($foraDeLinha) $st['inativos']++;
                    if ($launch) $st['lancamentos']++;
                    if (! $dry) {
                        $existente->sku = $sku; // nome, URL, fotos e compatibilidades continuam
                        if ($foraDeLinha) $existente->is_active = false;
                        if ($launch) $existente->is_launch = true;
                        if (! $existente->tmac_brand_id && $brandId) $existente->tmac_brand_id = $brandId;
                        $existente->save();
                    }
                    continue;
                }
                $codigoAntigoNaoAchado[] = "{$sku} (antigo {$antigo})";
            }

            $catIds = array_values(array_filter(array_map(fn ($c) => $cats[$c] ?? null, $this->categorias($nome, $grupo))));
            if (! $catIds) $semCategoria[] = "{$sku} — grupo ".($row['GRUPO'] ?? '');
            if ($foraDeLinha) $st['inativos']++;
            if ($launch) $st['lancamentos']++;

            // 2) Já existe com o código novo (rodando de novo): atualiza
            if ($produto) {
                $st['atualizados']++;
                if (! $dry) {
                    if ($produto->trashed()) $produto->restore();
                    $produto->fill([
                        'tmac_brand_id' => $produto->tmac_brand_id ?: $brandId,
                        'is_active'     => ! $foraDeLinha,
                        'is_launch'     => $launch,
                    ])->save();
                    if ($catIds && $produto->categories()->count() === 0) {
                        $produto->categories()->sync($catIds);
                    }
                }
                continue;
            }

            // 3) Produto novo
            $st['novos']++;
            if ($dry) continue;

            $slugBase = Str::slug(Str::limit($nome, 180, '').'-'.$sku);
            $slug = $slugBase;
            for ($i = 2; Product::withTrashed()->where('slug', $slug)->exists(); $i++) {
                $slug = "{$slugBase}-{$i}";
            }

            $novo = Product::create([
                'sku'           => $sku,
                'name'          => Str::limit($nome, 200, ''),
                'slug'          => $slug,
                'tmac_brand_id' => $brandId,
                'is_active'     => ! $foraDeLinha,
                'is_launch'     => $launch,
            ]);
            if ($catIds) $novo->categories()->sync($catIds);
        }

        $this->newLine();
        $this->info('═══ Resultado'.($dry ? ' (simulação — nada gravado)' : '').' ═══');
        $this->line("  Produtos novos:                 <fg=green>{$st['novos']}</>");
        $this->line("  Código trocado (já no site):    <fg=yellow>{$st['codigo_trocado']}</>");
        $this->line("  Atualizados (já importados):    {$st['atualizados']}");
        $this->line("  Fora de linha → inativos:       {$st['inativos']}");
        $this->line("  Com selo Lançamento:            {$st['lancamentos']}");

        if ($codigoAntigoNaoAchado) {
            $this->newLine();
            $this->warn('Código antigo não encontrado no site — cadastrados como novos:');
            foreach ($codigoAntigoNaoAchado as $s) $this->line("  • {$s}");
        }
        if ($semCategoria) {
            $this->newLine();
            $this->warn(count($semCategoria).' sem categoria (ajustar no painel → Produtos):');
            foreach (array_slice($semCategoria, 0, 40) as $s) $this->line("  • {$s}");
        }
        $this->newLine();

        return self::SUCCESS;
    }

    /** @return string[] nomes de categorias */
    private function categorias(string $nome, string $grupo): array
    {
        $n = $this->norm($nome);
        foreach (self::POR_NOME as $prefixo => $cats) {
            if (str_starts_with($n, $prefixo)) {
                if ($prefixo === 'ROLAMENTO') {
                    return str_contains($n, 'RODA') ? ['RODA'] : ['MOTOR'];
                }
                return $cats;
            }
        }

        return self::POR_GRUPO[$grupo] ?? [];
    }

    /** Maiúsculas, sem acento, espaços simples */
    private function norm(string $t): string
    {
        $t = mb_strtoupper(trim($t));
        $t = strtr($t, ['Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'É' => 'E', 'Ê' => 'E', 'Í' => 'I',
                        'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ç' => 'C']);

        return preg_replace('/\s+/', ' ', $t);
    }

    /** Lê a 1ª aba do XLSX (sem dependência externa) → linhas associativas pelo cabeçalho */
    private function readXlsx(string $path): array
    {
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

        $matrix = [];
        foreach (simplexml_load_string($sheet)->sheetData->row as $row) {
            $line = [];
            foreach ($row->c as $c) {
                preg_match('/([A-Z]+)/', (string) $c['r'], $m);
                $col = 0;
                foreach (str_split($m[1] ?? 'A') as $ch) $col = $col * 26 + (ord($ch) - 64);
                $v = (string) $c->v;
                if ((string) $c['t'] === 's') $v = $shared[(int) $v] ?? '';
                elseif (isset($c->is->t)) $v = (string) $c->is->t;
                $line[$col - 1] = $v;
            }
            $matrix[] = $line;
        }
        if (! $matrix) return [];

        $headers = array_map(fn ($h) => mb_strtoupper(trim((string) $h)), $matrix[0]);
        $out = [];
        foreach (array_slice($matrix, 1) as $line) {
            $assoc = [];
            foreach ($headers as $i => $h) {
                if ($h !== '') $assoc[$h] = $line[$i] ?? '';
            }
            if (array_filter($assoc)) $out[] = $assoc;
        }

        return $out;
    }
}
