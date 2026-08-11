<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CatalogWipe extends Command
{
    protected $signature = 'catalog:wipe
                            {--products : Apaga apenas produtos}
                            {--categories : Apaga apenas categorias}
                            {--brands : Apaga apenas marcas}
                            {--all : Apaga produtos, categorias e marcas}
                            {--force : Não pede confirmação}';

    protected $description = 'Remove dados fictícios do catálogo (produtos, categorias, marcas)';

    public function handle(): int
    {
        $doProducts   = $this->option('products')   || $this->option('all');
        $doCategories = $this->option('categories') || $this->option('all');
        $doBrands     = $this->option('brands')     || $this->option('all');

        if (! $doProducts && ! $doCategories && ! $doBrands) {
            $this->error('Informe o que apagar: --products, --categories, --brands ou --all');
            return self::FAILURE;
        }

        // ── Resumo do que será apagado ──────────────────────────
        $this->newLine();
        $this->info('═══ Resumo da limpeza ═══');

        $counts = [];
        if ($doProducts) {
            $counts['Produtos'] = Product::withTrashed()->count();
        }
        if ($doCategories) {
            $counts['Categorias'] = Category::count();
        }
        if ($doBrands) {
            $counts['Marcas'] = Brand::count();
        }

        foreach ($counts as $label => $n) {
            $this->line("  {$label}: <fg=yellow>{$n}</> registro(s)");
        }
        $this->newLine();

        if (array_sum($counts) === 0) {
            $this->info('Nada para apagar. Catálogo já está limpo.');
            return self::SUCCESS;
        }

        // ── Confirmação ─────────────────────────────────────────
        if (! $this->option('force')) {
            $this->warn('⚠  Esta ação é IRREVERSÍVEL. Faça backup do banco antes.');
            if (! $this->confirm('Confirma a exclusão?', false)) {
                $this->info('Cancelado.');
                return self::SUCCESS;
            }
        }

        // ── Execução ────────────────────────────────────────────
        $this->newLine();
        DB::transaction(function () use ($doProducts, $doCategories, $doBrands) {

            // Desliga checagem de FK durante a limpeza
            Schema::disableForeignKeyConstraints();

            if ($doProducts) {
                $this->line('Apagando produtos…');

                // Tabelas dependentes primeiro
                if (Schema::hasTable('category_product')) {
                    DB::table('category_product')->delete();
                }
                if (Schema::hasTable('product_images')) {
                    DB::table('product_images')->delete();
                }
                if (Schema::hasTable('motorcycle_fitments')) {
                    DB::table('motorcycle_fitments')->delete();
                }
                // Itens de cotação referenciam produto — anula a FK preservando o snapshot
                if (Schema::hasTable('quote_request_items')) {
                    DB::table('quote_request_items')->update(['product_id' => null]);
                }

                DB::table('products')->delete();
                DB::statement('ALTER TABLE products AUTO_INCREMENT = 1');
                $this->line('  <fg=green>✓</> Produtos removidos');
            }

            if ($doCategories) {
                $this->line('Apagando categorias e subcategorias…');

                if (Schema::hasTable('category_product')) {
                    DB::table('category_product')->delete();
                }
                // Zera parent_id antes para não travar em FK self-referencing
                DB::table('categories')->update(['parent_id' => null]);
                DB::table('categories')->delete();
                DB::statement('ALTER TABLE categories AUTO_INCREMENT = 1');
                $this->line('  <fg=green>✓</> Categorias removidas');
            }

            if ($doBrands) {
                $this->line('Apagando marcas…');

                // Produtos que ainda apontem para marcas (se produtos não foram apagados)
                if (Schema::hasTable('products')) {
                    DB::table('products')->update(['brand_id' => null]);
                }

                DB::table('brands')->delete();
                DB::statement('ALTER TABLE brands AUTO_INCREMENT = 1');
                $this->line('  <fg=green>✓</> Marcas removidas');
            }

            Schema::enableForeignKeyConstraints();
        });

        // ── Limpa caches que possam guardar listas ──────────────
        $this->newLine();
        $this->line('Limpando cache…');
        cache()->flush();

        $this->newLine();
        $this->info('✔ Limpeza concluída.');
        $this->newLine();
        $this->comment('Próximos passos:');
        $this->line('  1. Acesse /admin e cadastre marcas reais em Catálogo → Marcas');
        $this->line('  2. Monte a árvore de categorias em Catálogo → Categorias');
        $this->line('  3. Cadastre os produtos em Catálogo → Produtos');
        $this->newLine();

        return self::SUCCESS;
    }
}
