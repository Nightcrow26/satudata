<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // DROP FK dari publikasi.instansi_id
        Schema::table('publikasi', function (Blueprint $table) {
            $table->dropForeign(['instansi_id']);
        });

        // DROP FK dari datasets.instansi_id
        Schema::table('datasets', function (Blueprint $table) {
            $table->dropForeign(['instansi_id']);
        });

        // DROP FK dari users.instansi_id (jika ada)
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['skpd_uuid']);
        });
    }

    public function down(): void
    {
        // Anda bisa menambahkan restore FK di sini jika dibutuhkan
        Schema::table('publikasi', function (Blueprint $table) {
            $table->foreign('instansi_id')->references('id')->on('skpd')->onDelete('set null');
        });

        Schema::table('datasets', function (Blueprint $table) {
            $table->foreign('instansi_id')->references('id')->on('skpd')->onDelete('set null');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('instansi_id')->references('id')->on('skpd')->onDelete('set null');
        });
    }
};

