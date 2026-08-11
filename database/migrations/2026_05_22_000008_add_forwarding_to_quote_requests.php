<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->timestamp('forwarded_to_representative_at')->nullable()->after('assigned_representative_id');
            $table->unsignedTinyInteger('forwarded_count')->default(0)->after('forwarded_to_representative_at');
        });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropColumn(['forwarded_to_representative_at', 'forwarded_count']);
        });
    }
};
