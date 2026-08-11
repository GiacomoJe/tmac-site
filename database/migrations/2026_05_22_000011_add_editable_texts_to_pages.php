<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            // Campos do hero (topo da página)
            $table->string('hero_eyebrow', 120)->nullable()->after('hero_image');
            $table->string('hero_title', 200)->nullable()->after('hero_eyebrow');
            $table->string('hero_highlight', 100)->nullable()->after('hero_title'); // palavra em destaque colorido
            $table->text('hero_description')->nullable()->after('hero_highlight');

            // Dados estruturados variáveis por página (regras, cards, etc.)
            $table->json('extra_data')->nullable()->after('hero_description');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['hero_eyebrow', 'hero_title', 'hero_highlight', 'hero_description', 'extra_data']);
        });
    }
};
