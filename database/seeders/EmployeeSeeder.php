<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'name' => 'Agus Setiawan',
                'email' => 'agus@wscrm.local',
                'username' => 'agus',
                'password' => 'password',
                'nik' => 'EMP-0001',
                'position' => 'System Administrator',
                'department' => 'IT',
                'phone' => '+628111111111',
                'address' => 'Jakarta',
                'hire_date' => now()->subYears(2)->format('Y-m-d'),
                'salary' => 8500000,
                'status' => 'active',
                'notes' => null,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@wscrm.local',
                'username' => 'budi',
                'password' => 'password',
                'nik' => 'EMP-0002',
                'position' => 'Customer Support',
                'department' => 'Support',
                'phone' => '+628122222222',
                'address' => 'Bandung',
                'hire_date' => now()->subYears(1)->format('Y-m-d'),
                'salary' => 6000000,
                'status' => 'active',
                'notes' => null,
            ],
            [
                'name' => 'Citra Dewi',
                'email' => 'citra@wscrm.local',
                'username' => 'citra',
                'password' => 'password',
                'nik' => 'EMP-0003',
                'position' => 'Finance Officer',
                'department' => 'Finance',
                'phone' => '+628133333333',
                'address' => 'Surabaya',
                'hire_date' => now()->subMonths(8)->format('Y-m-d'),
                'salary' => 7000000,
                'status' => 'active',
                'notes' => null,
            ],
            [
                'name' => 'Dian Prasetyo',
                'email' => 'dian@wscrm.local',
                'username' => 'dian',
                'password' => 'password',
                'nik' => 'EMP-0004',
                'position' => 'Sales Executive',
                'department' => 'Sales',
                'phone' => '+628144444444',
                'address' => 'Semarang',
                'hire_date' => now()->subMonths(6)->format('Y-m-d'),
                'salary' => 6500000,
                'status' => 'active',
                'notes' => null,
            ],
            [
                'name' => 'Eka Putri',
                'email' => 'eka@wscrm.local',
                'username' => 'eka',
                'password' => 'password',
                'nik' => 'EMP-0005',
                'position' => 'Web Developer',
                'department' => 'IT',
                'phone' => '+628155555555',
                'address' => 'Depok',
                'hire_date' => now()->subMonths(3)->format('Y-m-d'),
                'salary' => 9000000,
                'status' => 'active',
                'notes' => null,
            ],
        ];

        foreach ($employees as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'password' => Hash::make($data['password']),
                    'role' => 'user',
                    'email_verified_at' => now(),
                ]
            );

            Employee::updateOrCreate(
                ['nik' => $data['nik']],
                [
                    'user_id' => $user->id,
                    'position' => $data['position'],
                    'department' => $data['department'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'hire_date' => $data['hire_date'],
                    'salary' => $data['salary'],
                    'status' => $data['status'],
                    'notes' => $data['notes'],
                ]
            );
        }
    }
}
