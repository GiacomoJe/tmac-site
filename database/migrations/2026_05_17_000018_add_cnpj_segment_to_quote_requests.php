<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->string('cnpj', 20)->nullable()->after('company');
            $table->string('segment', 50)->nullable()->after('cnpj');
        });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropColumn(['cnpj', 'segment']);
        });
    }
};
