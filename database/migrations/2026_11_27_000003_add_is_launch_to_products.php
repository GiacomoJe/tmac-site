<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Selo "Lançamento" no card do produto (coluna TAG da planilha do cliente). */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'is_launch')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_launch')->default(false)->after('is_featured');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'is_launch')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('is_launch');
            });
        }
    }
};
