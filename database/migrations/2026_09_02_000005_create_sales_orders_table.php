<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('sales_price_list_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tabela', 30);

            // Snapshot dos dados do cliente/pedido no momento do envio (o cadastro do
            // cliente pode mudar depois; o pedido preserva o que foi informado).
            $table->string('razao_social', 150)->nullable();
            $table->string('cnpj', 20)->nullable();
            $table->string('responsavel', 150)->nullable();
            $table->string('telefone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('cnpj_transportadora', 20)->nullable();
            $table->string('transportadora', 150)->nullable();
            $table->string('vendedor', 150)->nullable();
            $table->string('prazo_pagamento', 60)->nullable();
            $table->text('observacoes')->nullable();

            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('total_bloqueado', 12, 2)->default(0);
            $table->unsignedInteger('itens_count')->default(0);
            $table->unsignedInteger('pecas_count')->default(0);

            $table->enum('status', ['novo', 'em_atendimento', 'faturado', 'cancelado'])->default('novo');

            $table->string('ip', 64)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
