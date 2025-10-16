<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the role name from 'user' to 'produsen data'
        Role::where('name', 'user')->update(['name' => 'produsen data']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the role name from 'produsen data' back to 'user'
        Role::where('name', 'produsen data')->update(['name' => 'user']);
    }
};
