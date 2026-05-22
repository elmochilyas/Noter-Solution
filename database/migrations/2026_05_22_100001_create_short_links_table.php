<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('short_links', function (Blueprint $table) {
            $table->id();
            $table->string('hash', 8)->unique();
            $table->string('target_url', 2048);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('short_links');
    }
};
