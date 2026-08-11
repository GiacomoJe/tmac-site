<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180)->nullable();
            $table->string('subtitle', 250)->nullable();
            $table->string('image_mobile')->nullable();
            $table->string('image_desktop')->nullable();
            $table->string('link_url')->nullable();
            $table->string('cta_label', 60)->nullable();
            $table->enum('position', ['home_top', 'home_mid'])->default('home_top');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['position', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
