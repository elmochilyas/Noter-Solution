<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('category', 32);
            $table->jsonb('question_translations');
            $table->jsonb('answer_translations');
            $table->boolean('is_published')->default(true);
            $table->smallInteger('display_order');
            $table->integer('view_count')->default(0);
            $table->timestamps();

            $table->index(['category', 'is_published']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE faqs ADD COLUMN embedding_fr vector(1024) NULL');
            DB::statement('ALTER TABLE faqs ADD COLUMN embedding_ar vector(1024) NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
