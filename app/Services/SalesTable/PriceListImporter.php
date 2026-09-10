<?php

namespace App\Services\SalesTable;

use App\Models\SalesCatalogItem;
use App\Models\SalesPriceList;
use App\Models\SalesState;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Entity\Cell\FormulaCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;
use RuntimeException;

/**
 * Publica uma nova versão da Tabela de Vendas a partir do .xlsx que o admin envia
 * pelo painel (Filament > Tabela de Vendas > Publicar tabela).
 *
 * As regras de negócio abaixo são o mesmo cálculo que o app antigo (Google Apps
 * Script) fazia "ao vivo" toda vez que um vendedor abria a tela — aqui calculamos
 * UMA VEZ, no momento da publicação, e gravamos o resultado já pronto em
 * sales_catalog_items (uma linha por produto x tabela/estado). É por isso que a
 * tela do cliente e os downloads em .xlsx ficam simples e rápidos.
 *
 * IMPORTANTE — não renomeie as abas da planilha nem mova as colunas usadas abaixo
 * sem também atualizar as constantes desta classe. Segue exatamente a mesma
 * estrutura documentada no app antigo.
 *
 * Sobre performance: uma planilha de catálogo real (~30 abas de estado x
 * milhares de produtos) facilmente passa de 1 milhão de células. A primeira
 * versão desta classe usava o PhpSpreadsheet (mesmo com "setLoadSheetsOnly" por
 * aba) e isso ainda estourava a memória em arquivos grandes de verdade: cada
 * aba lida reabria o workbook inteiro (reprocessando shared-strings/estilos do
 * arquivo todo de novo a cada chamada) e construía um objeto pesado por célula.
 *
 * Por isso a leitura aqui usa o OpenSpout, que é um leitor de XLSX 100%
 * sequencial (baseado em XMLReader, não carrega o DOM do arquivo em memória) —
 * o próprio pacote documenta uso de memória abaixo de poucos MB independente do
 * tamanho do arquivo. Fazemos UMA única passada pelo arquivo inteiro (um único
 * "open"), guardando cada aba já como array simples de valores (não como
 * objetos de célula) — isso é uma fração da memória que o PhpSpreadsheet usava
 * para a mesma informação. O PhpSpreadsheet continua sendo usado só para GERAR
 * os .xlsx de download (SalesTableExporter), que são arquivos pequenos.
 */
class PriceListImporter
{
    private const ABA_CADASTRO = 'Tabela - Modelo';
    private const ABA_ESTOQUE = 'Tabela - Estoque';
    private const ABA_CONFIG = 'Config';
    private const ABA_BA = 'BA';

    /** Tabelas que usam a variante de promoção "NET". SP tem variante própria; as demais usam "EXT". */
    private const TABELAS_NET = ['NET', 'ES', 'GO', 'RN', 'RO', 'SE', 'TO', 'SC', 'MS', 'RS'];

    /** Nome das abas de promoção por variante. Ajuste aqui se o período/nome mudar. */
    private const PROMO = [
        'NET' => ['agosto' => 'PROMOÇÃO - AGOSTO - NET', 'extra' => 'PROMOÇÃO EXTRA - NET'],
        'SP' => ['agosto' => 'PROMOÇÃO - AGOSTO - SP', 'extra' => 'PROMOÇÃO EXTRA - IN'],
        'EXT' => ['agosto' => 'PROMOÇÃO - AGOSTO - EXT', 'extra' => 'PROMOÇÃO EXTRA - EXT'],
    ];

    private const CHUNK = 500;

    public function import(string $absolutePath, string $originalFilename, ?User $uploadedBy = null): SalesPriceList
    {
        // Planilhas de catálogo real chegam a dezenas/centenas de milhares de linhas
        // (produtos x tabelas) — sem isso, o PHP costuma cortar a publicação no meio
        // pelo max_execution_time padrão (30s) antes de terminar de gravar tudo.
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        $current = ini_get('memory_limit');
        if ($current !== false && $this->toBytes($current) > 0 && $this->toBytes($current) < $this->toBytes('256M')) {
            @ini_set('memory_limit', '256M');
        }

        // Única leitura sequencial do arquivo inteiro — cada aba já sai como array
        // de valores simples (não objetos), pronto para os métodos abaixo.
        $sheets = $this->lerPlanilha($absolutePath);

        $tabelas = $this->listarTabelas($sheets);
        if (empty($tabelas)) {
            throw new RuntimeException('A aba "'.self::ABA_CONFIG.'" não tem nenhuma tabela cadastrada na coluna A.');
        }

        $cadastro = $this->lerCadastro($sheets);
        if (empty($cadastro)) {
            throw new RuntimeException('A aba "'.self::ABA_CADASTRO.'" está vazia ou não foi encontrada.');
        }

        $estoque = $this->mapaEstoque($sheets);
        $precosBA = $this->mapaTabela($sheets, self::ABA_BA, obrigatoria: false);

        // Os mapas de promoção só dependem da variante (NET/SP/EXT) — só existem 3
        // combinações possíveis, então lemos cada aba de promoção UMA vez só, mesmo
        // que várias tabelas (ex.: 10 estados) compartilhem a mesma variante NET.
        $promoPorVariante = [];
        foreach (array_keys(self::PROMO) as $variante) {
            $promoPorVariante[$variante] = [
                'agosto' => $this->mapaPromo($sheets, self::PROMO[$variante]['agosto']),
                'extra' => $this->mapaPromo($sheets, self::PROMO[$variante]['extra']),
            ];
        }

        return DB::transaction(function () use ($sheets, $tabelas, $cadastro, $estoque, $precosBA, $promoPorVariante, $absolutePath, $originalFilename, $uploadedBy) {
            $storedPath = 'tabela-vendas/'.now()->format('Y-m-d_His').'-'.preg_replace('/[^A-Za-z0-9._-]/', '_', $originalFilename);
            Storage::disk('local')->put($storedPath, file_get_contents($absolutePath));

            $priceList = SalesPriceList::create([
                'original_filename' => $originalFilename,
                'file_path' => $storedPath,
                'uploaded_by' => $uploadedBy?->id,
                'is_active' => false,
                'products_count' => count($cadastro),
                'states_count' => count($tabelas),
                'published_at' => now(),
            ]);

            foreach ($tabelas as $i => $codigo) {
                SalesState::create([
                    'sales_price_list_id' => $priceList->id,
                    'code' => $codigo,
                    'sort_order' => $i,
                ]);
            }

            foreach ($tabelas as $tabela) {
                $variante = $this->variantePromo($tabela);
                $precos = ($tabela === self::ABA_BA) ? $precosBA : $this->mapaTabela($sheets, $tabela, obrigatoria: true);

                $this->importarTabela(
                    $priceList->id,
                    $tabela,
                    $cadastro,
                    $estoque,
                    $precosBA,
                    $precos,
                    $promoPorVariante[$variante]['agosto'],
                    $promoPorVariante[$variante]['extra'],
                );
            }

            // Só troca a versão ativa depois que tudo acima terminou sem erro.
            SalesPriceList::where('is_active', true)->update(['is_active' => false]);
            $priceList->update(['is_active' => true]);

            return $priceList->fresh(['states']);
        });
    }

    private function importarTabela(
        int $priceListId,
        string $tabela,
        array $cadastro,
        array $estoque,
        array $precosBA,
        array $precos,
        array $promoAgosto,
        array $promoExtra,
    ): void {
        $now = now();
        $linhas = [];

        foreach ($cadastro as $item) {
            $cod = $item['cod'];
            $usaBA = str_contains(mb_strtoupper($item['desc']), 'CAMARA DE AR ARO');
            $base = $usaBA ? ($precosBA[$cod] ?? null) : ($precos[$cod] ?? null);

            $promo = null;
            $preco = $base['preco'] ?? 0.0;
            if (array_key_exists($cod, $promoAgosto)) {
                $promo = 'PROMOÇÃO';
                $preco = $promoAgosto[$cod];
            } elseif (array_key_exists($cod, $promoExtra)) {
                $promo = 'PROMOÇÃO EXTRA';
                $preco = $promoExtra[$cod];
            }

            $status = $estoque[$cod] ?? $estoque[$this->codigoLimpo($cod)] ?? null;
            if ($status === null) {
                $status = 'SEM CADASTRO';
            } elseif ($status === '' || $status === '0') {
                $status = 'EM ESTOQUE';
            }
            $statusUpper = mb_strtoupper($status);
            $bloqueado = str_contains($statusUpper, 'SEM ESTOQUE') || str_contains($statusUpper, 'FORA DE LINHA');

            $linhas[] = [
                'sales_price_list_id' => $priceListId,
                'tabela' => $tabela,
                'cod' => $cod,
                'descricao' => $item['desc'],
                'marca' => $item['marca'],
                'grupo' => $base['grupo'] ?? null,
                'caixa_master' => $item['cx'],
                'sub_embalagem' => $item['sub'],
                'tag' => $item['tag'],
                'promo' => $promo,
                'status' => $status,
                'bloqueado' => $bloqueado,
                'preco' => round((float) $preco, 2),
                'achou_preco' => $base !== null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($linhas) >= self::CHUNK) {
                SalesCatalogItem::insert($linhas);
                $linhas = [];
            }
        }

        if (! empty($linhas)) {
            SalesCatalogItem::insert($linhas);
        }
    }

    /* ---------------------------- leitura da planilha ---------------------------- */

    /**
     * Lê o arquivo inteiro em UMA única passada sequencial (OpenSpout/XMLReader),
     * sem nunca montar o DOM do workbook em memória. Cada aba vira um array
     * simples de linhas (cada linha já um array de valores, na ordem das
     * colunas) — é isso que os métodos abaixo (listarTabelas, lerCadastro etc.)
     * recebem no lugar do antigo "abre o arquivo de novo por aba".
     *
     * @return array<string, list<list<mixed>>> aba => linhas
     */
    private function lerPlanilha(string $absolutePath): array
    {
        $options = new Options();
        $options->SHOULD_FORMAT_DATES = false;
        $options->SHOULD_PRESERVE_EMPTY_ROWS = false;

        $reader = new Reader($options);
        $reader->open($absolutePath);

        $sheets = [];

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                $linhas = [];
                foreach ($sheet->getRowIterator() as $row) {
                    $linhas[] = $this->linhaParaArray($row);
                }
                $sheets[$sheet->getName()] = $linhas;
            }
        } finally {
            $reader->close();
        }

        return $sheets;
    }

    /**
     * Converte uma Row do OpenSpout num array de valores brutos, na mesma
     * lógica que o antigo toArray(null, true, false, false) do PhpSpreadsheet:
     * células de fórmula usam o VALOR JÁ CALCULADO gravado no arquivo (não o
     * texto da fórmula) — é o que getComputedValue() devolve.
     */
    private function linhaParaArray(Row $row): array
    {
        return array_map(
            static function ($cell) {
                if ($cell instanceof FormulaCell) {
                    return $cell->getComputedValue();
                }

                return $cell->getValue();
            },
            $row->getCells()
        );
    }

    /**
     * @param array<string, list<list<mixed>>> $sheets
     */
    private function sheetRows(array $sheets, string $nome, bool $obrigatoria = true): ?array
    {
        if (! array_key_exists($nome, $sheets)) {
            if ($obrigatoria) {
                throw new RuntimeException('Aba não encontrada na planilha: "'.$nome.'". Confira o nome exato da aba.');
            }

            return null;
        }

        return $sheets[$nome];
    }

    private function listarTabelas(array $sheets): array
    {
        $rows = $this->sheetRows($sheets, self::ABA_CONFIG);
        $tabelas = [];
        foreach (array_slice($rows, 1) as $row) {
            $v = trim((string) ($row[0] ?? ''));
            if ($v !== '') {
                $tabelas[] = $v;
            }
        }

        return $tabelas;
    }

    /** cadastro geral de produtos — aba "Tabela - Modelo". */
    private function lerCadastro(array $sheets): array
    {
        $rows = $this->sheetRows($sheets, self::ABA_CADASTRO);
        $itens = [];
        foreach (array_slice($rows, 1) as $row) {
            $cod = trim((string) ($row[0] ?? ''));
            if ($cod === '') {
                continue;
            }
            $itens[] = [
                'cod' => $cod,
                'desc' => trim((string) ($row[1] ?? '')),
                'marca' => trim((string) ($row[2] ?? '')) ?: null,
                'cx' => $this->nullableCell($row[5] ?? null),
                'sub' => $this->nullableCell($row[6] ?? null),
                'tag' => trim((string) ($row[7] ?? '')) ?: null,
            ];
        }

        return $itens;
    }

    /** aba de estado/tabela de preço: COD col A, PREÇO col G, GRUPO col K, dados a partir da linha 4. */
    private function mapaTabela(array $sheets, string $nomeAba, bool $obrigatoria): array
    {
        $rows = $this->sheetRows($sheets, $nomeAba, $obrigatoria);
        if ($rows === null) {
            return [];
        }

        $mapa = [];
        foreach (array_slice($rows, 3) as $row) {
            $cod = trim((string) ($row[0] ?? ''));
            if ($cod === '') {
                continue;
            }
            $mapa[$cod] = [
                'preco' => is_numeric($row[6] ?? null) ? (float) $row[6] : 0.0,
                'grupo' => trim((string) ($row[10] ?? '')) ?: null,
            ];
        }

        return $mapa;
    }

    /** aba de promoção: COD col A, VALOR col C, dados a partir da linha 2. */
    private function mapaPromo(array $sheets, string $nomeAba): array
    {
        $rows = $this->sheetRows($sheets, $nomeAba, obrigatoria: false);
        if ($rows === null) {
            return [];
        }

        $mapa = [];
        foreach (array_slice($rows, 1) as $row) {
            $cod = trim((string) ($row[0] ?? ''));
            if ($cod === '') {
                continue;
            }
            $mapa[$cod] = is_numeric($row[2] ?? null) ? (float) $row[2] : 0.0;
        }

        return $mapa;
    }

    /** aba de estoque: COD col B, STATUS col C. */
    private function mapaEstoque(array $sheets): array
    {
        $rows = $this->sheetRows($sheets, self::ABA_ESTOQUE);
        $mapa = [];
        foreach (array_slice($rows, 1) as $row) {
            $cod = trim((string) ($row[1] ?? ''));
            if ($cod === '') {
                continue;
            }
            $status = $row[2] ?? '';
            $mapa[$cod] = ((string) $status === '0') ? '' : trim((string) $status);
        }

        return $mapa;
    }

    private function variantePromo(string $tabela): string
    {
        if ($tabela === 'SP') {
            return 'SP';
        }

        return in_array($tabela, self::TABELAS_NET, true) ? 'NET' : 'EXT';
    }

    private function codigoLimpo(string $cod): string
    {
        $p = strpos($cod, ' (');

        return $p !== false && $p > 0 ? trim(substr($cod, 0, $p)) : $cod;
    }

    private function nullableCell(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function toBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '-1') {
            return -1;
        }

        $unit = strtolower(substr($value, -1));
        $num = (int) $value;

        return match ($unit) {
            'g' => $num * 1024 * 1024 * 1024,
            'm' => $num * 1024 * 1024,
            'k' => $num * 1024,
            default => (int) $value,
        };
    }
}
