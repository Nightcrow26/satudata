<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('walidata', function (Blueprint $table) {
            $table->timestamp('verifikasi_data')->nullable()->after('data');
        });
    }

    public function down(): void
    {
        Schema::table('walidata', function (Blueprint $table) {
            $table->dropColumn('verifikasi_data');
        });
    }
};
