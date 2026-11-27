<?php

namespace App\Console\Commands;

use App\Models\Reseller;
use App\Models\State;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Importa a lista de lojistas enviada pelo cliente ("lojistas.xlsx", 27/nov/2026)
 * para a página Onde comprar.
 *
 *  - Lojas físicas: grava o endereço e busca as coordenadas (OpenStreetMap) para o mapa.
 *  - Lojas online (Mercado Livre): marcadas como "loja online", aparecem na lista
 *    "Compre online" com o link da loja.
 *
 * Pode rodar mais de uma vez: atualiza os registros existentes (mesmo nome + endereço/link)
 * e não sobrescreve coordenadas que já tenham sido ajustadas no painel.
 *
 *   php artisan resellers:import-lojistas
 *   php artisan resellers:import-lojistas --limpar-demo   (remove antes os revendedores fictícios)
 */
class ImportLojistas extends Command
{
    protected $signature = 'resellers:import-lojistas
                            {--limpar-demo : Remove os revendedores de teste [DEMO] antes de importar}
                            {--sem-geo : Não busca coordenadas (dá para buscar depois no painel)}';

    protected $description = 'Importa os lojistas da planilha do cliente para a página Onde comprar';

    /** Lojas físicas — endereço já separado em campos. */
    private const FISICAS = [
        ['name' => 'MHV Moto Parts',           'address' => 'Avenida Nordestina',          'number' => '677',  'complement' => null,     'neighborhood' => 'Vila Americana',          'city' => 'São Paulo',       'uf' => 'SP', 'zip' => '08032-000'],
        ['name' => 'Brothers Moto Peças',      'address' => 'Rua Luciano Silva',           'number' => '269',  'complement' => null,     'neighborhood' => 'Vila das Belezas',        'city' => 'São Paulo',       'uf' => 'SP', 'zip' => '05841-000'],
        ['name' => 'União Motos Poá',          'address' => 'Avenida Nove de Julho',       'number' => '1383', 'complement' => '01',     'neighborhood' => 'Centro',                  'city' => 'Poá',             'uf' => 'SP', 'zip' => '08557-100'],
        ['name' => 'FTP Moto',                 'address' => 'Rua Lorena',                  'number' => '390',  'complement' => null,     'neighborhood' => 'Vila Monte Belo',         'city' => 'Itaquaquecetuba', 'uf' => 'SP', 'zip' => '08577-790'],
        ['name' => 'Brothers Moto Peças',      'address' => "Estrada do M'Boi Mirim",      'number' => '3454', 'complement' => null,     'neighborhood' => 'Jardim Coimbra',          'city' => 'São Paulo',       'uf' => 'SP', 'zip' => '04832-306'],
        ['name' => 'União Motos Guarulhos',    'address' => 'Estrada do Capão Bonito',     'number' => '1741', 'complement' => null,     'neighborhood' => 'Jardim Maria de Lourdes', 'city' => 'Guarulhos',       'uf' => 'SP', 'zip' => '07263-010'],
        ['name' => 'Brasa Moto Peças',         'address' => 'Rua Adolfo Lemos',            'number' => '79',   'complement' => null,     'neighborhood' => 'Inhoaíba',                'city' => 'Rio de Janeiro',  'uf' => 'RJ', 'zip' => null],
        ['name' => 'Nave Moto Peças',          'address' => 'Avenida Jacu-Pêssego',        'number' => '5802', 'complement' => null,     'neighborhood' => 'Itaquera',                'city' => 'São Paulo',       'uf' => 'SP', 'zip' => '08290-000'],
        ['name' => 'S Coelho Pneumáticos',     'address' => 'Rua Capela de Santana',       'number' => '14',   'complement' => null,     'neighborhood' => 'Jardim Marisa',           'city' => 'São Paulo',       'uf' => 'SP', 'zip' => '05108-000'],
        ['name' => 'Re Peças',                 'address' => 'Rua Pará',                    'number' => '502',  'complement' => null,     'neighborhood' => 'Ipiranga',                'city' => 'Ribeirão Preto',  'uf' => 'SP', 'zip' => '14055-010'],
        ['name' => 'Moto Peças Duas Rodas',    'address' => 'Rua Doutor Augusto Figueiredo', 'number' => '31', 'complement' => 'Loja A', 'neighborhood' => 'Bangu',                   'city' => 'Rio de Janeiro',  'uf' => 'RJ', 'zip' => null],
        ['name' => 'Motosio Peças',            'address' => 'Estrada do Tingui',           'number' => '30A',  'complement' => null,     'neighborhood' => 'Campo Grande',            'city' => 'Rio de Janeiro',  'uf' => 'RJ', 'zip' => null],
    ];

    /** Lojas online — links limpos (sem os parâmetros de rastreio do Mercado Livre). */
    private const ONLINE = [
        ['name' => 'Delfos Store',  'website' => 'https://www.mercadolivre.com.br/pagina/delfosstore'],
        ['name' => 'Loja da Nay',   'website' => 'https://www.mercadolivre.com.br/pagina/naypecasstore'],
        ['name' => 'Lica Comércio', 'website' => 'https://www.mercadolivre.com.br/pagina/licacomercio'],
        ['name' => 'LSC Connect',   'website' => 'https://www.mercadolivre.com.br/pagina/lscconnect'],
        ['name' => 'Grupo Bell',    'website' => 'https://www.mercadolivre.com.br/pagina/grupobell'],
        ['name' => 'FP Motoparts',  'website' => 'https://lista.mercadolivre.com.br/_CustId_3487499593'],
        ['name' => 'EV Motoparts',  'website' => 'https://lista.mercadolivre.com.br/_CustId_3497022809'],
    ];

    public function handle(): int
    {
        if ($this->option('limpar-demo')) {
            $this->call('resellers:clear-demo', ['--force' => true]);
        }

        $states = State::pluck('id', 'uf');
        $semGeo = [];

        $this->info('Lojas físicas');
        foreach (self::FISICAS as $row) {
            $reseller = Reseller::firstOrNew(['name' => $row['name'], 'address' => $row['address']]);
            $reseller->fill([
                'number'       => $row['number'],
                'complement'   => $row['complement'],
                'neighborhood' => $row['neighborhood'],
                'city'         => $row['city'],
                'state_id'     => $states[$row['uf']] ?? null,
                'zip'          => $row['zip'],
                'is_online'    => false,
                'is_active'    => true,
            ]);

            $origem = 'já tinha coordenadas';
            if (! $this->option('sem-geo') && ($reseller->latitude === null || $reseller->longitude === null)) {
                [$lat, $lng, $origem] = $this->geocode($row);
                if ($lat !== null) {
                    $reseller->latitude  = $lat;
                    $reseller->longitude = $lng;
                } else {
                    $semGeo[] = $row['name'].' — '.$row['city'];
                }
            }

            $reseller->save();
            $this->line(sprintf('  %s %s — %s/%s (%s)',
                $reseller->wasRecentlyCreated ? '+' : '↻', $row['name'], $row['city'], $row['uf'], $origem));
        }

        $this->newLine();
        $this->info('Lojas online');
        foreach (self::ONLINE as $row) {
            $reseller = Reseller::firstOrNew(['name' => $row['name'], 'website' => $row['website']]);
            $reseller->fill([
                'city'      => $reseller->city ?? '',
                'is_online' => true,
                'is_active' => true,
            ]);
            $reseller->save();
            $this->line(sprintf('  %s %s', $reseller->wasRecentlyCreated ? '+' : '↻', $row['name']));
        }

        $this->newLine();
        if ($semGeo) {
            $this->warn('Sem coordenadas (não aparecem no mapa até ajustar no painel → Revendedores → "Buscar coordenadas"):');
            foreach ($semGeo as $s) {
                $this->line("  • {$s}");
            }
        }
        $this->info('✔ Importação concluída.');

        return self::SUCCESS;
    }

    /**
     * Busca as coordenadas no OpenStreetMap (Nominatim), do mais preciso para o mais genérico:
     * endereço com número → rua sem número → CEP → bairro. Respeita o limite de 1 consulta/segundo.
     *
     * @return array{0: ?float, 1: ?float, 2: string}
     */
    private function geocode(array $row): array
    {
        $tentativas = [
            'endereço' => ['street' => "{$row['number']} {$row['address']}", 'city' => $row['city'], 'state' => $row['uf']],
            'rua'      => ['street' => $row['address'], 'city' => $row['city'], 'state' => $row['uf']],
            'CEP'      => $row['zip'] ? ['postalcode' => $row['zip']] : null,
            'bairro'   => ['q' => "{$row['neighborhood']}, {$row['city']}, {$row['uf']}"],
        ];

        foreach (array_filter($tentativas) as $nivel => $params) {
            usleep(1_100_000);
            try {
                $res = Http::timeout(12)
                    ->withHeaders(['User-Agent' => 'TMAC-Import/1.0 (contato@tmacimport.com.br)'])
                    ->get('https://nominatim.openstreetmap.org/search', $params + [
                        'country' => isset($params['q']) ? null : 'Brasil',
                        'countrycodes' => 'br',
                        'format' => 'json',
                        'limit' => 1,
                    ])
                    ->json();

                if (! empty($res[0]['lat'])) {
                    return [round((float) $res[0]['lat'], 7), round((float) $res[0]['lon'], 7), "coordenadas pelo {$nivel}"];
                }
            } catch (\Throwable $e) {
                $this->warn("    falha ao consultar o mapa: {$e->getMessage()}");
                break;
            }
        }

        return [null, null, 'SEM coordenadas'];
    }
}
