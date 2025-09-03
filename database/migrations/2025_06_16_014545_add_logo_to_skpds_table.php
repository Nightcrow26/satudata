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
        Schema::table('skpd', function (Blueprint $table) {
            if (! Schema::hasColumn('skpd', 'foto')) {
                $table->string('foto')->nullable()->after('telepon');
            }
        });
    }

    public function down(): void
    {
        Schema::table('skpd', function (Blueprint $table) {
            if (Schema::hasColumn('skpd', 'foto')) {
                $table->dropColumn('foto');
            }
        });
    }

};
