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
        Schema::create('user_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 255);
            $table->ipAddress('ip_address');
            $table->integer('rating')->unsigned()->comment('Rating 1-5 bintang');
            $table->text('feedback')->nullable()->comment('Masukan dan saran perbaikan');
            $table->string('user_agent', 512)->nullable();
            $table->timestamps();
            
            $table->index(['session_id', 'ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_surveys');
    }
};
