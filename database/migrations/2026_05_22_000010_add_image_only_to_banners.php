<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            // Quando true: renderiza só a imagem, sem overlay/título/subtítulo/CTA
            $table->boolean('image_only')->default(false)->after('cta_label');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('image_only');
        });
    }
};
