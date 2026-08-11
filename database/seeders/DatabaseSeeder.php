<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Seeders de fundação (dados estruturais reais) ──
        $this->call([
            StatesSeeder::class,       // 27 UFs + valores mínimos de cotação
            SettingsSeeder::class,     // chaves de configuração do site
            PagesSeeder::class,        // páginas institucionais
            MotorcyclesSeeder::class,  // marcas/modelos de motos (compatibilidade)
            SocialLinksSeeder::class,  // redes sociais (editar URLs no admin)
            TmacBrandsSeeder::class,   // Universo TMAC: Corami, LBJ, Atrox, Motoled
            CategoriesSeeder::class,   // categorias reais (importadas de categorias-tmac.xlsx)
        ]);

        // ── Seeders de DEMONSTRAÇÃO (NÃO rodar em produção) ──
        // Descomente apenas para popular ambiente de teste com dados fictícios.
        // Para limpar: php artisan catalog:wipe --all
        //
        // $this->call([
        //     BrandsSeeder::class,      // marcas distribuídas de exemplo
        //     ProductsSeeder::class,    // ~50 produtos fictícios
        // ]);

        User::firstOrCreate(
            ['email' => 'admin@tmacimport.com.br'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('tmac@admin'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
