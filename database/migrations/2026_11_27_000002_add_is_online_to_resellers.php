<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lojas online (Mercado Livre, site próprio): ficam fora do mapa e aparecem
 * na lista "Compre online" da página Onde comprar.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('resellers', 'is_online')) {
            Schema::table('resellers', function (Blueprint $table) {
                $table->boolean('is_online')->default(false)->after('is_featured');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('resellers', 'is_online')) {
            Schema::table('resellers', function (Blueprint $table) {
                $table->dropColumn('is_online');
            });
        }
    }
};
