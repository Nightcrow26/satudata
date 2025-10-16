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
        // Add 'revisi' to datasets status enum
        DB::statement("ALTER TABLE datasets DROP CONSTRAINT IF EXISTS datasets_status_check");
        DB::statement("ALTER TABLE datasets ALTER COLUMN status TYPE VARCHAR(50)");
        DB::statement("ALTER TABLE datasets ADD CONSTRAINT datasets_status_check CHECK (status IN ('draft', 'revisi', 'pending', 'published'))");

        // Add 'revisi' to publikasi status enum  
        DB::statement("ALTER TABLE publikasi DROP CONSTRAINT IF EXISTS publikasi_status_check");
        DB::statement("ALTER TABLE publikasi ALTER COLUMN status TYPE VARCHAR(50)");
        DB::statement("ALTER TABLE publikasi ADD CONSTRAINT publikasi_status_check CHECK (status IN ('draft', 'revisi', 'pending', 'published'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove constraints first
        DB::statement("ALTER TABLE datasets DROP CONSTRAINT IF EXISTS datasets_status_check");
        DB::statement("ALTER TABLE publikasi DROP CONSTRAINT IF EXISTS publikasi_status_check");
        
        // Revert to original enum values
        DB::statement("ALTER TABLE datasets ADD CONSTRAINT datasets_status_check CHECK (status IN ('draft', 'pending', 'published'))");
        DB::statement("ALTER TABLE publikasi ADD CONSTRAINT publikasi_status_check CHECK (status IN ('draft', 'pending', 'published'))");
    }
};
