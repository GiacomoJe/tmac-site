<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 150);
            $table->string('phone', 30)->nullable();
            $table->string('company', 150)->nullable();
            $table->string('city', 120)->nullable();
            $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->string('subject', 120)->nullable();
            $table->text('message')->nullable();
            $table->string('interest', 80)->nullable(); // tipo de interesse (produtos, representar, etc)
            $table->string('source', 60)->default('contact'); // qual formulário gerou: contact, newsletter, etc
            $table->string('source_url', 255)->nullable(); // página onde o lead foi captado
            $table->json('utm_payload')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->enum('status', ['new', 'contacted', 'qualified', 'converted', 'discarded'])->default('new');
            $table->text('admin_notes')->nullable();
            $table->timestamp('rd_synced_at')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();

            $table->index('source');
            $table->index('status');
            $table->index('created_at');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
