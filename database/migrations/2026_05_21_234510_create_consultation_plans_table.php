<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->jsonb('name_translations');
            $table->jsonb('description_translations');
            $table->jsonb('included_features');
            $table->smallInteger('duration_minutes');
            $table->integer('price_centimes');
            $table->string('format', 16);
            $table->boolean('is_recommended')->default(false);
            $table->boolean('is_active')->default(true);
            $table->smallInteger('display_order');
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_plans');
    }
};
