<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lista de "tabelas" (códigos de estado/grupo de preço) publicadas em cada
     * versão da planilha — vem da aba Config, coluna A. Ex.: SP, RS, NET, BA, EXT...
     */
    public function up(): void
    {
        Schema::create('sales_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_price_list_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['sales_price_list_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_states');
    }
};
