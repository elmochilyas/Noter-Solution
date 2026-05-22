<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_holds', function (Blueprint $table) {
            $table->id();
            $table->timestamp('slot_starts_at');
            $table->timestamp('slot_ends_at');
            $table->foreignId('client_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id', 128);
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['slot_starts_at', 'slot_ends_at']);
            $table->index('expires_at');
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_holds');
    }
};
