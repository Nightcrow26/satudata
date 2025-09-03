<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Pastikan foreign key sudah di-drop sebelumnya
        DB::statement('ALTER TABLE publikasi ALTER COLUMN instansi_id TYPE VARCHAR(255) USING instansi_id::VARCHAR');
        DB::statement('ALTER TABLE datasets ALTER COLUMN instansi_id TYPE VARCHAR(255) USING instansi_id::VARCHAR');
        DB::statement('ALTER TABLE users ALTER COLUMN skpd_uuid TYPE VARCHAR(255) USING skpd_uuid::VARCHAR');
    }

    public function down(): void
    {
        // Rollback jika perlu kembali ke UUID
        DB::statement('ALTER TABLE publikasi ALTER COLUMN instansi_id TYPE UUID USING instansi_id::UUID');
        DB::statement('ALTER TABLE datasets ALTER COLUMN instansi_id TYPE UUID USING instansi_id::UUID');
        DB::statement('ALTER TABLE users ALTER COLUMN skpd_id TYPE UUID USING skpd_id::UUID');
    }
};
