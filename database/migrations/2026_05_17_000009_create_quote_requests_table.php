<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('customer_name', 150);
            $table->string('company', 150)->nullable();
            $table->string('email', 150);
            $table->string('phone', 30);
            $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->string('city', 120)->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'won', 'lost'])->default('pending');
            $table->foreignId('assigned_representative_id')->nullable()
                ->constrained('representatives')->nullOnDelete();
            $table->string('source', 80)->nullable();
            $table->json('utm_payload')->nullable();
            $table->string('ip', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('state_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
