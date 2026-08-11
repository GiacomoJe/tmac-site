<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Preço INTERNO de referência para cálculo do mínimo de cotação.
            // NUNCA exibido ao cliente final — usado apenas no servidor para somar contra o mínimo do estado.
            $table->decimal('quote_price', 12, 2)->nullable()->after('sku');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('quote_price');
        });
    }
};
