<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 16)->unique();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('consultation_plan_id')->constrained()->restrictOnDelete();
            $table->string('service_category', 32);
            $table->text('description');
            $table->string('format', 16);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('status', 20)->default('pending_payment');
            $table->string('meeting_url', 500)->nullable();
            $table->integer('total_centimes');
            $table->string('currency', 3)->default('MAD');
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('client_id');
            $table->index('starts_at');
            $table->index(['status', 'starts_at']);
            $table->index(['consultation_plan_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
