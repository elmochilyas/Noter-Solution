<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $hasData = DB::table('faqs')->whereNotNull('embedding_fr')->exists();
        if (! $hasData) {
            return;
        }

        DB::statement('
            CREATE INDEX IF NOT EXISTS faqs_embedding_fr_idx
            ON faqs USING hnsw (embedding_fr vector_cosine_ops)
            WITH (m = 16, ef_construction = 64)
        ');

        DB::statement('
            CREATE INDEX IF NOT EXISTS faqs_embedding_ar_idx
            ON faqs USING hnsw (embedding_ar vector_cosine_ops)
            WITH (m = 16, ef_construction = 64)
        ');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS faqs_embedding_fr_idx');
        DB::statement('DROP INDEX IF EXISTS faqs_embedding_ar_idx');
    }
};
