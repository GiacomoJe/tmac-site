<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 180)->unique();
            $table->string('title', 200);
            $table->longText('content')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('seo_title', 180)->nullable();
            $table->string('seo_description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
