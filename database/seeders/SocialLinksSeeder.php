<?php

namespace Database\Seeders;

use App\Models\SocialLink;
use Illuminate\Database\Seeder;

class SocialLinksSeeder extends Seeder
{
    public function run(): void
    {
        // Demos — admin pode editar/desativar/adicionar mais via Filament.
        $items = [
            ['instagram', 'https://www.instagram.com/tmacimport',  10],
            ['facebook',  'https://www.facebook.com/tmacimport',   20],
            ['youtube',   'https://www.youtube.com/@tmacimport',   30],
            ['tiktok',    'https://www.tiktok.com/@tmacimport',    40],
            ['linkedin',  'https://www.linkedin.com/company/tmac', 50],
        ];

        foreach ($items as [$platform, $url, $sort]) {
            SocialLink::updateOrCreate(
                ['platform' => $platform],
                ['url' => $url, 'sort_order' => $sort, 'is_active' => true]
            );
        }
    }
}
