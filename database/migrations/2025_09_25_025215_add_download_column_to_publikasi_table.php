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
            $table->unsignedBigInteger('download')->default(0)->after('view');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publikasi', function (Blueprint $table) {
            $table->dropColumn('download');
        });
    }
};
