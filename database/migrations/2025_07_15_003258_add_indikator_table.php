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
        Schema::create('indikators', function (Blueprint $table) {
            $table->uuid('id')->primary(); // dari idtransaksi
            $table->string('kode_indikator');
            $table->text('uraian_indikator');
            $table->foreignUuid('bidang_id')->constrained('bidangs')->onDelete('cascade');
            $table->timestamps(); // created
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indikators');
    }
};
