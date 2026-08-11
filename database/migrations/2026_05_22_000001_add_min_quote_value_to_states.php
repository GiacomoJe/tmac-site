<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('states', function (Blueprint $table) {
            // Valor mínimo (em R$) para o cliente poder solicitar cotação a partir desse estado.
            // Nullable porque pode haver estado sem mínimo definido (cai num default global).
            $table->decimal('min_quote_value', 12, 2)->nullable()->after('region');
        });
    }

    public function down(): void
    {
        Schema::table('states', function (Blueprint $table) {
            $table->dropColumn('min_quote_value');
        });
    }
};
