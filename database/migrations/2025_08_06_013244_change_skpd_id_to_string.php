<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // PostgreSQL: convert UUID ke VARCHAR via USING
        DB::statement('ALTER TABLE skpd ALTER COLUMN id TYPE VARCHAR(255) USING id::VARCHAR');
    }

    public function down(): void
    {
        // Pastikan hanya jika rollback diperlukan
        DB::statement('ALTER TABLE skpd ALTER COLUMN id TYPE UUID USING id::UUID');
    }
};
