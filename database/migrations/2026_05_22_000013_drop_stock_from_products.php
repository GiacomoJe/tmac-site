<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'stock_label')) {
                $table->dropColumn('stock_label');
            }
            if (Schema::hasColumn('products', 'stock_level')) {
                $table->dropColumn('stock_level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('stock_label', 40)->nullable();
            $table->enum('stock_level', ['ok', 'low', 'out'])->default('ok');
        });
    }
};
