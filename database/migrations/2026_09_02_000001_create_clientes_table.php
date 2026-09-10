<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('company', 150)->nullable();
            $table->string('cnpj', 20)->nullable();
            $table->string('email')->unique();
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('uf', 2)->nullable();
            $table->string('city', 120)->nullable();

            // Código da "tabela" (estado de preço) padrão deste cliente na Tabela de Vendas.
            // Ex.: 'SP', 'RS', 'NET'... corresponde ao código publicado em sales_states.
            $table->string('tabela_padrao', 30)->nullable();

            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
