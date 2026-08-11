<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('tmac_brand_id')
                ->nullable()
                ->after('brand_id')
                ->constrained('tmac_brands')
                ->nullOnDelete();

            $table->index(['tmac_brand_id', 'is_active'], 'products_tmac_brand_idx');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_tmac_brand_idx');
            $table->dropConstrainedForeignId('tmac_brand_id');
        });
    }
};
