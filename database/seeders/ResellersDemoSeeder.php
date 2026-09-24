<?php

namespace Database\Seeders;

use App\Models\Reseller;
use App\Models\State;
use Illuminate\Database\Seeder;

/**
 * Revendedores FICTÍCIOS para teste do mapa (São Paulo/SP).
 *
 * Para remover depois:  php artisan db:seed --class=ResellersDemoSeeder --  (ver método clear)
 * Ou direto no SQL:     DELETE FROM resellers WHERE notes LIKE '%[DEMO]%';
 */
class ResellersDemoSeeder extends Seeder
{
    public function run(): void
    {
        $spId = State::where('uf', 'SP')->value('id');

        $items = [
            [
                'name' => 'Moto Peças Brás',
                'company_name' => 'Brás Comércio de Peças Ltda',
                'address' => 'Rua Maria Marcolina', 'number' => '412',
                'neighborhood' => 'Brás', 'city' => 'São Paulo', 'zip' => '03007-020',
                'latitude' => -23.5381, 'longitude' => -46.6170,
                'phone' => '(11) 3315-4420', 'whatsapp' => '11993154420',
                'instagram' => 'motopecasbras',
                'opening_hours' => 'Seg-Sex 8h-18h · Sáb 8h-13h',
                'is_featured' => true, 'sort_order' => 1,
            ],
            [
                'name' => 'Centro Moto Santana',
                'address' => 'Av. Braz Leme', 'number' => '1888',
                'neighborhood' => 'Santana', 'city' => 'São Paulo', 'zip' => '02511-000',
                'latitude' => -23.5000, 'longitude' => -46.6300,
                'phone' => '(11) 2281-7744', 'whatsapp' => '11982817744',
                'opening_hours' => 'Seg-Sex 8h-18h30 · Sáb 8h-12h',
                'sort_order' => 2,
            ],
            [
                'name' => 'Tatuapé Motoparts',
                'company_name' => 'TMP Auto Peças ME',
                'address' => 'Rua Serra de Bragança', 'number' => '755',
                'neighborhood' => 'Tatuapé', 'city' => 'São Paulo', 'zip' => '03318-000',
                'latitude' => -23.5405, 'longitude' => -46.5760,
                'phone' => '(11) 2092-3310', 'whatsapp' => '11970923310',
                'instagram' => 'tatuapemotoparts',
                'opening_hours' => 'Seg-Sex 8h-18h · Sáb 9h-13h',
                'is_featured' => true, 'sort_order' => 3,
            ],
            [
                'name' => 'Ipiranga Racing',
                'address' => 'Av. Dom Pedro I', 'number' => '2340',
                'neighborhood' => 'Ipiranga', 'city' => 'São Paulo', 'zip' => '04264-000',
                'latitude' => -23.5940, 'longitude' => -46.6050,
                'phone' => '(11) 2063-8890', 'whatsapp' => '11960638890',
                'opening_hours' => 'Seg-Sáb 8h-19h',
                'sort_order' => 4,
            ],
            [
                'name' => 'Pinheiros Moto Center',
                'address' => 'Rua Teodoro Sampaio', 'number' => '1930',
                'neighborhood' => 'Pinheiros', 'city' => 'São Paulo', 'zip' => '05406-050',
                'latitude' => -23.5608, 'longitude' => -46.6890,
                'phone' => '(11) 3085-2210', 'whatsapp' => '11950852210',
                'instagram' => 'pinheirosmotocenter',
                'opening_hours' => 'Seg-Sex 9h-19h · Sáb 9h-14h',
                'sort_order' => 5,
            ],
            [
                'name' => 'Lapa Auto Moto',
                'address' => 'Rua Clélia', 'number' => '1120',
                'neighborhood' => 'Lapa', 'city' => 'São Paulo', 'zip' => '05042-000',
                'latitude' => -23.5240, 'longitude' => -46.7020,
                'phone' => '(11) 3672-4455', 'whatsapp' => '11986724455',
                'opening_hours' => 'Seg-Sex 8h-18h · Sáb 8h-12h',
                'sort_order' => 6,
            ],
            [
                'name' => 'Santo Amaro Motos',
                'address' => 'Av. Santo Amaro', 'number' => '4655',
                'neighborhood' => 'Brooklin', 'city' => 'São Paulo', 'zip' => '04702-001',
                'latitude' => -23.6210, 'longitude' => -46.6820,
                'phone' => '(11) 5044-9920', 'whatsapp' => '11940449920',
                'opening_hours' => 'Seg-Sex 8h30-18h30 · Sáb 9h-13h',
                'sort_order' => 7,
            ],
            [
                'name' => 'Penha Moto Shop',
                'address' => 'Rua Dr. João Ribeiro', 'number' => '288',
                'neighborhood' => 'Penha', 'city' => 'São Paulo', 'zip' => '03634-010',
                'latitude' => -23.5280, 'longitude' => -46.5420,
                'phone' => '(11) 2295-6633', 'whatsapp' => '11932956633',
                'opening_hours' => 'Seg-Sex 8h-18h · Sáb 8h-13h',
                'sort_order' => 8,
            ],
            [
                'name' => 'Guarulhos Peças & Acessórios',
                'address' => 'Av. Tiradentes', 'number' => '1540',
                'neighborhood' => 'Centro', 'city' => 'Guarulhos', 'zip' => '07090-000',
                'latitude' => -23.4620, 'longitude' => -46.5330,
                'phone' => '(11) 2408-7712', 'whatsapp' => '11924087712',
                'opening_hours' => 'Seg-Sex 8h-18h · Sáb 8h-12h',
                'is_featured' => true, 'sort_order' => 9,
            ],
            [
                'name' => 'ABC Moto Parts',
                'address' => 'Av. Industrial', 'number' => '890',
                'neighborhood' => 'Jardim', 'city' => 'Santo André', 'zip' => '09080-510',
                'latitude' => -23.6640, 'longitude' => -46.5280,
                'phone' => '(11) 4438-2200', 'whatsapp' => '11944382200',
                'opening_hours' => 'Seg-Sex 8h-18h · Sáb 8h-13h',
                'sort_order' => 10,
            ],
            [
                'name' => 'Osasco Moto Center',
                'address' => 'Av. dos Autonomistas', 'number' => '3210',
                'neighborhood' => 'Vila Yara', 'city' => 'Osasco', 'zip' => '06090-020',
                'latitude' => -23.5320, 'longitude' => -46.7710,
                'phone' => '(11) 3681-5540', 'whatsapp' => '11936815540',
                'opening_hours' => 'Seg-Sex 8h-18h · Sáb 8h-12h',
                'sort_order' => 11,
            ],
            [
                'name' => 'Vila Mariana Bikes',
                'address' => 'Rua Domingos de Morais', 'number' => '2020',
                'neighborhood' => 'Vila Mariana', 'city' => 'São Paulo', 'zip' => '04035-000',
                'latitude' => -23.5960, 'longitude' => -46.6380,
                'phone' => '(11) 5573-1180', 'whatsapp' => '11955731180',
                'instagram' => 'vilamarianabikes',
                'opening_hours' => 'Seg-Sex 9h-18h30 · Sáb 9h-13h',
                'sort_order' => 12,
            ],
        ];

        foreach ($items as $data) {
            Reseller::updateOrCreate(
                ['name' => $data['name'], 'city' => $data['city']],
                array_merge($data, [
                    'state_id'  => $spId,
                    'is_active' => true,
                    'notes'     => '[DEMO] Registro fictício para teste do mapa — apagar antes de publicar.',
                ])
            );
        }

        $this->command?->info('✔ '.count($items).' revendedores de teste criados (Grande São Paulo).');
        $this->command?->warn('⚠  São dados FICTÍCIOS. Para remover: php artisan resellers:clear-demo');
    }
}
