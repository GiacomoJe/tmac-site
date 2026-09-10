<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();

            $table->string('cod', 60);
            $table->string('descricao', 255);
            $table->string('marca', 60)->nullable();

            $table->decimal('preco_tabela', 12, 2)->default(0);
            $table->decimal('desconto_percent', 5, 2)->default(0);
            $table->unsignedInteger('quantidade')->default(0);
            $table->decimal('valor_final', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->boolean('bloqueado')->default(false);

            $table->timestamps();

            $table->index('sales_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
    }
};
