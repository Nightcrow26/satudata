<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('datasets', function (Blueprint $table) {
            if (! Schema::hasColumn('datasets', 'bukti_dukung')) {
                $table->string('bukti_dukung')->nullable()->after('metadata');
            }
        });
    }

    public function down(): void
    {
        Schema::table('datasets', function (Blueprint $table) {
            if (Schema::hasColumn('datasets', 'bukti_dukung')) {
                $table->dropColumn('bukti_dukung');
            }
        });
    }
};
