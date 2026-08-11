<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motorcycle_fitments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('motorcycle_model_id')->constrained('motorcycle_models')->cascadeOnDelete();
            $table->unsignedSmallInteger('year_from')->nullable();
            $table->unsignedSmallInteger('year_to')->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'motorcycle_model_id', 'year_from'], 'fitment_unique');
            $table->index(['motorcycle_model_id', 'year_from', 'year_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motorcycle_fitments');
    }
};
