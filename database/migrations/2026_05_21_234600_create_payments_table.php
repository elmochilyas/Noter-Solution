<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->string('gateway', 16);
            $table->string('gateway_intent_id', 255);
            $table->string('gateway_charge_id', 255)->nullable();
            $table->integer('amount_centimes');
            $table->string('currency', 3);
            $table->string('status', 20);
            $table->timestamp('paid_at')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index('booking_id');
            $table->unique(['gateway', 'gateway_intent_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
