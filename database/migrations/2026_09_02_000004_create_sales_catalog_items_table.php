<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo já "resolvido" por tabela (estado): um snapshot, calculado no momento
     * da publicação, do que a tela do cliente precisa mostrar para cada item em cada
     * tabela — preço final (já considerando promoção/câmara de ar), status de estoque
     * e se o item entra ou não no total do pedido. Equivalente ao que o app antigo
     * calculava "na hora" a partir da planilha (função getCatalogo).
     *
     * Fica maior que a planilha original (produtos × tabelas), mas deixa a tela do
     * cliente e a exportação em .xlsx extremamente simples e rápidas de consultar.
     */
    public function up(): void
    {
        Schema::create('sales_catalog_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_price_list_id')->constrained()->cascadeOnDelete();
            $table->string('tabela', 30);

            $table->string('cod', 60);
            $table->string('descricao', 255);
            $table->string('marca', 60)->nullable();
            $table->string('grupo', 60)->nullable();
            $table->string('caixa_master', 30)->nullable();
            $table->string('sub_embalagem', 30)->nullable();
            $table->string('tag', 60)->nullable();

            // 'PROMOÇÃO' | 'PROMOÇÃO EXTRA' | null
            $table->string('promo', 30)->nullable();
            $table->string('status', 60)->default('EM ESTOQUE');
            $table->boolean('bloqueado')->default(false);

            $table->decimal('preco', 12, 2)->default(0);
            $table->boolean('achou_preco')->default(true);

            $table->timestamps();

            $table->unique(['sales_price_list_id', 'tabela', 'cod'], 'sales_catalog_unique');
            $table->index(['sales_price_list_id', 'tabela', 'promo']);
            $table->index('cod');
            $table->index('descricao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_catalog_items');
    }
};
