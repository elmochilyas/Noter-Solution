<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availability_rules', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('day_of_week');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('format', 16);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('day_of_week');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_rules');
    }
};
