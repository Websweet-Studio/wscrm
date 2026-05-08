<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ];

        if (Schema::hasColumn('users', 'username')) {
            $attributes['username'] = 'superadmin';
        }

        if (Schema::hasColumn('users', 'role')) {
            $attributes['role'] = 'super_admin';
        }

        User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            $attributes
        );
    }
}
