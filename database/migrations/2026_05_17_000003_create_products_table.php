<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('sku', 80)->unique();
            $table->string('name', 200);
            $table->string('slug', 220)->unique();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->json('specifications')->nullable();
            $table->string('main_image')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('seo_title', 180)->nullable();
            $table->string('seo_description', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'is_featured']);
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
