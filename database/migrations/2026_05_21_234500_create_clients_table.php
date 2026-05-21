<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('email', 160)->unique();
            $table->string('phone', 20);
            $table->string('full_name', 160);
            $table->string('preferred_locale', 5)->default('ar');
            $table->text('national_id')->nullable();
            $table->string('national_id_last4', 8)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
