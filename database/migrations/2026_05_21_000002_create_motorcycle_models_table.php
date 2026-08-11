<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motorcycle_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motorcycle_make_id')->constrained('motorcycle_makes')->cascadeOnDelete();
            $table->string('name', 140);
            $table->string('slug', 160);
            $table->unsignedSmallInteger('displacement')->nullable(); // ex: 160, 250, 650
            $table->string('category', 40)->nullable(); // street, sport, trail, scooter, custom, touring
            $table->unsignedSmallInteger('year_start')->nullable(); // ano de lançamento no Brasil
            $table->unsignedSmallInteger('year_end')->nullable();   // null = ainda em linha
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['motorcycle_make_id', 'slug']);
            $table->index(['is_active', 'sort_order']);
            $table->index('displacement');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motorcycle_models');
    }
};
