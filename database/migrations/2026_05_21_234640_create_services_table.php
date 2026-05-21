<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->jsonb('title_translations');
            $table->jsonb('intro_translations');
            $table->jsonb('body_translations');
            $table->jsonb('transactions_translations');
            $table->jsonb('required_documents_translations');
            $table->string('icon', 64);
            $table->smallInteger('display_order');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
