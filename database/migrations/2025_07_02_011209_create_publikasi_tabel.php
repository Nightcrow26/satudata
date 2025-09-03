<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publikasi', function (Blueprint $table) {
            $table->uuid('id')->primary();                  // primary key
            $table->string('nama');             // nama dataset
            $table->enum('status', ['draft', 'pending', 'published'])->default('draft');
            $table->string('pdf')->nullable();    // path file Excel jika ada
            $table->year('tahun');              // tahun
            $table->text('catatan_verif')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('keyword')->nullable();
            $table->unsignedBigInteger('view')->default(0);
            // relasi ke instansi (skpd), user, dan aspek
            $table->foreignid('instansi_id')
                  ->nullable()
                  ->constrained('skpd', 'id')
                  ->onDelete('set null');
            $table->foreignUuid('user_id')
                  ->constrained('users', 'id')
                  ->onDelete('cascade');
            $table->foreignUuid('aspek_id')
                  ->nullable()
                  ->constrained('aspeks', 'id')
                  ->onDelete('set null');
            $table->timestamps();  // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publikasi');
    }
};
