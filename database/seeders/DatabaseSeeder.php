<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::create([
             'nik' => '6307062609010001',
             'name' => 'Afrizal Miqdad',
             'email' => 'miqdad@gmail.com'
        ]);
        $user->assignRole('admin');
    }
}
