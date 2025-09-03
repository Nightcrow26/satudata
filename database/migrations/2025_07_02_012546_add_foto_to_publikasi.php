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
        Schema::table('publikasi', function (Blueprint $table) {
            if (! Schema::hasColumn('publikasi', 'foto')) {
                $table->string('foto')->nullable()->after('pdf');
            }
        });
    }

    public function down(): void
    {
        Schema::table('publikasi', function (Blueprint $table) {
            if (Schema::hasColumn('publikasi', 'foto')) {
                $table->dropColumn('foto');
            }
        });
    }

};
