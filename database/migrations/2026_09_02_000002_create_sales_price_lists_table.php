<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada linha aqui representa UMA publicação da planilha .xlsx da Tabela de Vendas.
     * Apenas uma fica com is_active = true por vez — é ela que os clientes enxergam.
     * Mantemos as anteriores no banco (não apagamos) como histórico/auditoria.
     */
    public function up(): void
    {
        Schema::create('sales_price_lists', function (Blueprint $table) {
            $table->id();
            $table->string('original_filename', 255);
            $table->string('file_path', 255)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('products_count')->default(0);
            $table->unsignedInteger('states_count')->default(0);
            $table->text('import_notes')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_price_lists');
    }
};
