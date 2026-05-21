<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availability_exceptions', function (Blueprint $table) {
            $table->id();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('reason', 255)->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->timestamps();

            $table->index('starts_at');
            $table->index('ends_at');
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_exceptions');
    }
};
