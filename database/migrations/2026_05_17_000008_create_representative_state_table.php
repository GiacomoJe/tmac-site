<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('representative_state', function (Blueprint $table) {
            $table->foreignId('representative_id')->constrained()->cascadeOnDelete();
            $table->foreignId('state_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);

            $table->primary(['representative_id', 'state_id']);
            $table->index(['state_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('representative_state');
    }
};
