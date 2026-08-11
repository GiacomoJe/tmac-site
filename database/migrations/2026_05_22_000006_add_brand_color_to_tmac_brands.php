<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tmac_brands', function (Blueprint $table) {
            // Cor sólida da marca (hex). Ex: Corami #E97B0B, L3J #006B2D, etc.
            $table->string('brand_color', 9)->nullable()->after('accent_color');
            // Tema de texto sobre a cor: light (branco) ou dark (preto)
            $table->string('text_theme', 10)->default('light')->after('brand_color');
        });
    }

    public function down(): void
    {
        Schema::table('tmac_brands', function (Blueprint $table) {
            $table->dropColumn(['brand_color', 'text_theme']);
        });
    }
};
