<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - parent_id: submarcas (ex.: LBJ Premium dentro da LBJ). Submarcas não entram
 *   na contagem "Quatro marcas" nem na vitrine principal do Universo TMAC.
 * - page_content: textos da página própria de cada marca (/universo-tmac/{slug}),
 *   editáveis no painel (Universo TMAC → editar → "Página da marca").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tmac_brands', function (Blueprint $table) {
            if (! Schema::hasColumn('tmac_brands', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('id')
                    ->constrained('tmac_brands')->nullOnDelete();
            }
            if (! Schema::hasColumn('tmac_brands', 'page_content')) {
                $table->json('page_content')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tmac_brands', function (Blueprint $table) {
            if (Schema::hasColumn('tmac_brands', 'parent_id')) {
                $table->dropConstrainedForeignId('parent_id');
            }
            if (Schema::hasColumn('tmac_brands', 'page_content')) {
                $table->dropColumn('page_content');
            }
        });
    }
};
