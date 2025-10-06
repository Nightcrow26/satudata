<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('walidata', function (Blueprint $table) {
            $table->uuid('id')->primary(); // dari idtransaksi
            $table->string('satuan');
            $table->string('tahun');               
            $table->string('data');                 
            $table->timestamps();

            // skpd_id bertipe string -> definisikan manual
            $table->string('skpd_id')->nullable(); 
        });

        Schema::table('walidata', function (Blueprint $table) {
              $table->foreign('skpd_id')
                  ->references('id')
                  ->on('skpd')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignUuid('user_id')->nullable()
                  ->constrained('users', 'id')
                  ->nullOnDelete();

            $table->foreignUuid('aspek_id')->nullable()
                  ->constrained('aspeks', 'id')
                  ->nullOnDelete();

            $table->foreignUuid('indikator_id')->nullable()
                  ->constrained('indikators', 'id')
                  ->nullOnDelete();

            $table->foreignUuid('bidang_id')->nullable()
                  ->constrained('bidangs', 'id')
                  ->nullOnDelete();

            // (opsional) indeks yang sering dipakai
            $table->index(['tahun']);
            $table->index(['skpd_id']);
            $table->index(['indikator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('walidata');
    }
};
