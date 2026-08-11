<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tmac_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('badge_label', 60)->nullable();      // ex: Motor, Premium, Acessórios
            $table->string('tagline', 160)->nullable();          // chamada curta
            $table->text('description')->nullable();             // descrição completa
            $table->string('image_path', 255)->nullable();       // foto principal (capa)
            $table->string('logo_path', 255)->nullable();        // logo da linha
            $table->string('accent_color', 20)->default('signal');// signal | accent | ink | whatsapp
            $table->string('link_url', 255)->nullable();         // link opcional (catálogo externo)
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            // SEO
            $table->string('meta_title', 160)->nullable();
            $table->string('meta_description', 255)->nullable();

            $table->timestamps();

            $table->index('is_active');
            $table->index('is_featured');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tmac_brands');
    }
};
