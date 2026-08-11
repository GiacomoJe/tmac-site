<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('stock_label', 40)->nullable()->after('is_active');
            $table->enum('stock_level', ['ok', 'low', 'out'])->default('ok')->after('stock_label');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock_label', 'stock_level']);
        });
    }
};
