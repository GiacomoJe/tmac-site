<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('representatives', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('representatives');
    }
};
