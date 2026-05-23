<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('preferred_channel', 16)->default('email')->after('user_agent');
            $table->boolean('accepted_marketing')->default(false)->after('preferred_channel');
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn(['preferred_channel', 'accepted_marketing']);
        });
    }
};
