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
        // Drop all possible existing constraints that might be blocking revisi status
        DB::statement("ALTER TABLE datasets DROP CONSTRAINT IF EXISTS datasets_status_check");
        DB::statement("ALTER TABLE datasets DROP CONSTRAINT IF EXISTS datasets_new_status_check");
        
        // Add the correct constraint that includes 'revisi'
        DB::statement("ALTER TABLE datasets ADD CONSTRAINT datasets_status_check CHECK (status IN ('draft', 'revisi', 'pending', 'published'))");

        // Do the same for publikasi table
        DB::statement("ALTER TABLE publikasi DROP CONSTRAINT IF EXISTS publikasi_status_check");
        DB::statement("ALTER TABLE publikasi DROP CONSTRAINT IF EXISTS publikasi_new_status_check");
        
        // Add the correct constraint that includes 'revisi'
        DB::statement("ALTER TABLE publikasi ADD CONSTRAINT publikasi_status_check CHECK (status IN ('draft', 'revisi', 'pending', 'published'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the new constraints
        DB::statement("ALTER TABLE datasets DROP CONSTRAINT IF EXISTS datasets_status_check");
        DB::statement("ALTER TABLE publikasi DROP CONSTRAINT IF EXISTS publikasi_status_check");
        
        // Restore original constraints (without revisi)
        DB::statement("ALTER TABLE datasets ADD CONSTRAINT datasets_status_check CHECK (status IN ('draft', 'pending', 'published'))");
        DB::statement("ALTER TABLE publikasi ADD CONSTRAINT publikasi_status_check CHECK (status IN ('draft', 'pending', 'published'))");
    }
};