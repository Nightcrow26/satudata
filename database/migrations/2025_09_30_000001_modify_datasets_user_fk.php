<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create a new table with the desired schema (user_id nullable and ON DELETE SET NULL)
        Schema::create('datasets_new', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama');
            $table->enum('status', ['draft', 'pending', 'published'])->default('draft');
            $table->string('excel')->nullable();
            $table->year('tahun');
            $table->string('metadata')->nullable();
            $table->text('catatan_verif')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('keyword')->nullable();
            $table->unsignedBigInteger('view')->default(0);

        // instansi_id stored as string to match current skpd.id column type
        $table->string('instansi_id')->nullable();
        $table->foreign('instansi_id')->references('id')->on('skpd')->onDelete('set null');

            // Make user_id nullable and set FK to set null on delete
            $table->foreignUuid('user_id')
                  ->nullable()
                  ->constrained('users', 'id')
                  ->onDelete('set null');

            $table->foreignUuid('aspek_id')
                  ->nullable()
                  ->constrained('aspeks', 'id')
                  ->onDelete('set null');

            $table->timestamps();
        });

        // Copy data from old table to new (preserve values)
        $rows = DB::table('datasets')->get();
        foreach ($rows as $r) {
            DB::table('datasets_new')->insert([
                'id' => $r->id,
                'nama' => $r->nama,
                'status' => $r->status,
                'excel' => $r->excel,
                'tahun' => $r->tahun,
                'metadata' => $r->metadata,
                'catatan_verif' => $r->catatan_verif,
                'deskripsi' => $r->deskripsi,
                'keyword' => $r->keyword,
                'view' => $r->view,
                'instansi_id' => $r->instansi_id,
                'user_id' => $r->user_id,
                'aspek_id' => $r->aspek_id,
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
            ]);
        }

        // Drop old table and rename new
        Schema::dropIfExists('datasets');
        Schema::rename('datasets_new', 'datasets');
    }

    public function down(): void
    {
        // Recreate original table with cascade delete (reverse of up)
        Schema::create('datasets_old', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama');
            $table->enum('status', ['draft', 'pending', 'published'])->default('draft');
            $table->string('excel')->nullable();
            $table->year('tahun');
            $table->string('metadata')->nullable();
            $table->text('catatan_verif')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('keyword')->nullable();
            $table->unsignedBigInteger('view')->default(0);

        // instansi_id stored as string to match current skpd.id column type
        $table->string('instansi_id')->nullable();
        $table->foreign('instansi_id')->references('id')->on('skpd')->onDelete('set null');

            $table->foreignUuid('user_id')
                  ->constrained('users', 'id')
                  ->onDelete('cascade');

            $table->foreignUuid('aspek_id')
                  ->nullable()
                  ->constrained('aspeks', 'id')
                  ->onDelete('set null');

            $table->timestamps();
        });

        // Copy back
        $rows = DB::table('datasets')->get();
        foreach ($rows as $r) {
            DB::table('datasets_old')->insert([
                'id' => $r->id,
                'nama' => $r->nama,
                'status' => $r->status,
                'excel' => $r->excel,
                'tahun' => $r->tahun,
                'metadata' => $r->metadata,
                'catatan_verif' => $r->catatan_verif,
                'deskripsi' => $r->deskripsi,
                'keyword' => $r->keyword,
                'view' => $r->view,
                'instansi_id' => $r->instansi_id,
                'user_id' => $r->user_id,
                'aspek_id' => $r->aspek_id,
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
            ]);
        }

        Schema::dropIfExists('datasets');
        Schema::rename('datasets_old', 'datasets');
    }
};
