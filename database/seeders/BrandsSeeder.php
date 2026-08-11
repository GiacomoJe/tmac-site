<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandsSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['Cobreq',       'Pastilhas e lonas de freio de alta performance.'],
            ['Vedamotors',   'Juntas, retentores e selos para todos os motores.'],
            ['NGK',          'Velas de ignição, cabos e bobinas.'],
            ['Bosch',        'Componentes elétricos, baterias e sistemas de ignição.'],
            ['Fram',         'Linha completa de filtros para motocicletas.'],
            ['Tecfil',       'Filtros de ar, óleo e combustível.'],
            ['Cofap',        'Amortecedores, kits de suspensão e molas.'],
            ['Pirelli',      'Pneus de moto para todas as cilindradas.'],
            ['Michelin',     'Pneus premium para uso urbano, esportivo e off-road.'],
            ['Magnetron',    'Sistemas elétricos: CDI, regulador, bobinas e alternadores.'],
            ['Riffel',       'Coroas, pinhões e correntes — kits de tração.'],
            ['DID',          'Correntes de transmissão de alta performance.'],
            ['Heliar',       'Baterias para motocicletas e scooters.'],
            ['Moura',        'Baterias seladas e convencionais para moto.'],
            ['Procotton',    'Filtros de ar de alta vazão (high flow).'],
            ['Pro Tork',     'Acessórios, escapamentos e componentes esportivos.'],
        ];

        foreach ($brands as $i => [$name, $desc]) {
            Brand::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $desc,
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );
        }
    }
}
