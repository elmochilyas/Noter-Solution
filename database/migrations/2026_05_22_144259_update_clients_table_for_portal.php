<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->change();
            $table->string('preferred_channel', 20)->default('email')->after('preferred_locale');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('phone', 20)->nullable(false)->change();
            $table->dropColumn('preferred_channel');
        });
    }
};
