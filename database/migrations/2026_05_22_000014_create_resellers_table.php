<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resellers', function (Blueprint $table) {
            $table->id();

            // Identificação
            $table->string('name', 160);                    // nome fantasia da loja
            $table->string('company_name', 160)->nullable(); // razão social
            $table->string('cnpj', 20)->nullable();
            $table->string('slug', 180)->unique();

            // Endereço
            $table->string('zip', 12)->nullable();
            $table->string('address', 200)->nullable();
            $table->string('number', 20)->nullable();
            $table->string('complement', 100)->nullable();
            $table->string('neighborhood', 120)->nullable();
            $table->string('city', 140);
            $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();

            // Coordenadas (para o mapa e cálculo de distância)
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Contato
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('email', 160)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('instagram', 120)->nullable();

            // Extras
            $table->text('opening_hours')->nullable();   // "Seg-Sex 8h-18h · Sáb 8h-12h"
            $table->text('notes')->nullable();           // observações internas
            $table->json('tmac_brands')->nullable();     // linhas que revende (slugs)

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false); // parceiro destaque
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->index(['is_active', 'city']);
            $table->index(['latitude', 'longitude']);
            $table->index('state_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resellers');
    }
};
