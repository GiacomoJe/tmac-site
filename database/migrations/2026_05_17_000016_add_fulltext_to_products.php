<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Útil para busca no MVP. Em MySQL/InnoDB requer 5.6+. Falhar silenciosamente caso o driver não suporte.
        try {
            DB::statement('ALTER TABLE products ADD FULLTEXT INDEX products_fulltext_idx (name, sku, short_description)');
        } catch (\Throwable $e) {
            // Driver não suporta FULLTEXT — manter LIKE como fallback no scope search().
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE products DROP INDEX products_fulltext_idx');
        } catch (\Throwable $e) {
            //
        }
    }
};
