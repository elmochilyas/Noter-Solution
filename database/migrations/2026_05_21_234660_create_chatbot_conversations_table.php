<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('session_id', 128);
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('locale', 5);
            $table->string('intent_resolved', 64)->nullable();
            $table->foreignId('led_to_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('last_message_at');
            $table->timestamp('ended_at')->nullable();
            $table->boolean('is_reviewed')->default(false);

            $table->index('uuid');
            $table->index('session_id');
            $table->index('client_id');
            $table->index('started_at');
            $table->index('is_reviewed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_conversations');
    }
};
