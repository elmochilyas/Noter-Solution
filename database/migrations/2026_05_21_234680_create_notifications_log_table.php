<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications_log', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_type', 16);
            $table->bigInteger('recipient_id')->nullable();
            $table->string('channel', 16);
            $table->string('template_key', 64);
            $table->string('status', 16);
            $table->string('provider_message_id', 255)->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['recipient_type', 'recipient_id']);
            $table->index(['template_key', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_log');
    }
};
