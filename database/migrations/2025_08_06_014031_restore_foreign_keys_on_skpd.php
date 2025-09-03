<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('publikasi', function (Blueprint $table) {
            $table->foreign('instansi_id')->references('id')->on('skpd')->onDelete('set null');
        });

        Schema::table('datasets', function (Blueprint $table) {
            $table->foreign('instansi_id')->references('id')->on('skpd')->onDelete('set null');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('skpd_uuid')->references('id')->on('skpd')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('publikasi', function (Blueprint $table) {
            $table->dropForeign(['instansi_id']);
        });

        Schema::table('datasets', function (Blueprint $table) {
            $table->dropForeign(['instansi_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['skpd_uuid']);
        });
    }
};

