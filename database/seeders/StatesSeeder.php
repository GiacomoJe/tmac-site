<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Seeder;

class StatesSeeder extends Seeder
{
    public function run(): void
    {
        // [uf, name, region, min_quote_value]
        // Mínimos variam por região conforme custo logístico:
        // Sudeste e Sul mais baixos (logística mais barata e demanda maior),
        // Norte mais alto (custo de envio elevado), Nordeste/Centro-Oeste intermediários.
        $states = [
            ['AC', 'Acre',                'Norte',         3500],
            ['AL', 'Alagoas',             'Nordeste',      2500],
            ['AP', 'Amapá',               'Norte',         3500],
            ['AM', 'Amazonas',            'Norte',         3500],
            ['BA', 'Bahia',               'Nordeste',      2500],
            ['CE', 'Ceará',               'Nordeste',      2500],
            ['DF', 'Distrito Federal',    'Centro-Oeste',  2500],
            ['ES', 'Espírito Santo',      'Sudeste',       2000],
            ['GO', 'Goiás',               'Centro-Oeste',  2500],
            ['MA', 'Maranhão',            'Nordeste',      3000],
            ['MT', 'Mato Grosso',         'Centro-Oeste',  3000],
            ['MS', 'Mato Grosso do Sul',  'Centro-Oeste',  3000],
            ['MG', 'Minas Gerais',        'Sudeste',       2000],
            ['PA', 'Pará',                'Norte',         3500],
            ['PB', 'Paraíba',             'Nordeste',      2500],
            ['PR', 'Paraná',              'Sul',           2000],
            ['PE', 'Pernambuco',          'Nordeste',      2500],
            ['PI', 'Piauí',               'Nordeste',      3000],
            ['RJ', 'Rio de Janeiro',      'Sudeste',       2000],
            ['RN', 'Rio Grande do Norte', 'Nordeste',      2500],
            ['RS', 'Rio Grande do Sul',   'Sul',           2000],
            ['RO', 'Rondônia',            'Norte',         3500],
            ['RR', 'Roraima',             'Norte',         3500],
            ['SC', 'Santa Catarina',      'Sul',           2000],
            ['SP', 'São Paulo',           'Sudeste',       2000],
            ['SE', 'Sergipe',             'Nordeste',      2500],
            ['TO', 'Tocantins',           'Norte',         3000],
        ];

        foreach ($states as [$uf, $name, $region, $min]) {
            State::updateOrCreate(
                ['uf' => $uf],
                ['name' => $name, 'region' => $region, 'min_quote_value' => $min]
            );
        }
    }
}
