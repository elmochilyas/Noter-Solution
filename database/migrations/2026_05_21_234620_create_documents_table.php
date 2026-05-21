<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('original_filename', 255);
            $table->string('mime_type', 100);
            $table->bigInteger('size_bytes');
            $table->string('storage_path', 500);
            $table->string('scan_status', 16)->default('pending');
            $table->timestamp('scanned_at')->nullable();
            $table->timestamp('purge_after');
            $table->timestamps();

            $table->index('booking_id');
            $table->index('client_id');
            $table->index('purge_after');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
