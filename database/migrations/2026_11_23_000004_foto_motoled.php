<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Foto da linha Motoled (moto com farol de LED aceso, fundo azul).
 * O arquivo fica versionado em public/images/brands/motoled/foto.webp.
 * Pode ser trocada a qualquer momento no painel (Universo TMAC → Motoled → Foto principal).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('tmac_brands')->where('slug', 'motoled')->update([
            'image_path' => 'images/brands/motoled/foto.webp',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('tmac_brands')->where('slug', 'motoled')
            ->where('image_path', 'images/brands/motoled/foto.webp')
            ->update(['image_path' => null]);
    }
};
