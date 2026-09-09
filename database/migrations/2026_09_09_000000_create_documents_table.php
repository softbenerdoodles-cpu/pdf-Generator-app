<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Saved documents belong to a signed-in user. Guests generate and download
 * without ever writing a row here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('template');
            $table->string('title');
            $table->json('data');
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->string('checksum', 64);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('template');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
