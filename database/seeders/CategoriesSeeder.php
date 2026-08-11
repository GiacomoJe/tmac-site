<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Categorias oficiais da TMAC.
 *
 * O seeder é idempotente:
 *  - cria/atualiza as categorias da lista
 *  - remove categorias fora da lista que não tenham produtos vinculados
 */
class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'ACESSÓRIOS',
            'CABOS DE COMANDO',
            'CARENAGEM',
            'CHASSI',
            'ELÉTRICA',
            'FERRAMENTAS E EQUIPAMENTOS',
            'FIXAÇÃO',
            'FREIO',
            'INJEÇÃO',
            'MOTOR',
            'RODA',
            'SUSPENSÃO',
            'TRANSMISSÃO',
        ];

        $keepSlugs = [];
        $sort = 0;

        foreach ($categories as $name) {
            $sort += 10;
            $slug = Str::slug($name);
            $keepSlugs[] = $slug;

            Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name'       => $name,
                    'parent_id'  => null,
                    'sort_order' => $sort,
                    'is_active'  => true,
                ]
            );
        }

        // ── Limpa categorias antigas que não estão na lista oficial ──
        $orphans = Category::whereNotIn('slug', $keepSlugs)->get();
        $removed = 0;
        $skipped = [];

        foreach ($orphans as $orphan) {
            if ($orphan->products()->exists()) {
                $skipped[] = $orphan->name;
                continue; // preserva se já tiver produto vinculado
            }
            $orphan->delete();
            $removed++;
        }

        $this->command?->info('✔ '.count($categories).' categorias oficiais cadastradas.');

        if ($removed > 0) {
            $this->command?->warn("✔ {$removed} categoria(s) antiga(s) removida(s).");
        }

        if (! empty($skipped)) {
            $this->command?->warn('⚠ Mantidas por terem produtos vinculados: '.implode(', ', $skipped));
        }
    }
}
