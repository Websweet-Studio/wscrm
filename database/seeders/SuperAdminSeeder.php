<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
<<<<<<< HEAD
            'password' => Hash::make('password'),
=======
            'username' => 'superadmin',
            'password' => 'password',
>>>>>>> a9fc1a8da374e6e9381ff8f4aece1dcb9dcd8a2b
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);
    }
}
