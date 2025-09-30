<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clean up SK penunjukan yang masih dalam format URL
        // Convert dari URL S3 ke path saja, atau set null jika invalid
        DB::table('users')
            ->whereNotNull('sk_penunjukan')
            ->where('sk_penunjukan', 'like', 'http%')
            ->orderBy('id')
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    $url = $user->sk_penunjukan;
                    
                    // Extract path dari S3 URL jika memungkinkan
                    if (preg_match('/amazonaws\.com\/(.+)$/', $url, $matches)) {
                        $path = $matches[1];
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['sk_penunjukan' => $path]);
                    } else {
                        // Jika tidak bisa diparse, set null
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['sk_penunjukan' => null]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu rollback karena ini adalah data cleanup
    }
};
