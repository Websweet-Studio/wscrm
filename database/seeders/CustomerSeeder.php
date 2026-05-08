<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create specific test customers
        Customer::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'phone' => '+62812345678',
            'address' => 'Jl. Test Address No. 123',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'postal_code' => '12345',
            'status' => 'active',
            ]
        );

        Customer::updateOrCreate(
            ['email' => 'john@customer.com'],
            [
            'name' => 'John Doe',
            'email' => 'john@customer.com',
            'password' => Hash::make('password'),
            'phone' => '+62856789012',
            'address' => 'Jl. Sample Street No. 456',
            'city' => 'Bandung',
            'country' => 'Indonesia',
            'postal_code' => '54321',
            'status' => 'active',
            ]
        );

        // Create additional random customers using factory
        Customer::factory()->count(18)->create();

        // Create some inactive customers
        Customer::factory()->count(5)->inactive()->create();

        // Create customers from different cities
        $indonesianCities = [
            'Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang',
            'Makassar', 'Palembang', 'Tangerang', 'Depok', 'Bekasi',
        ];

        foreach ($indonesianCities as $city) {
            Customer::factory()->create([
                'city' => $city,
                'country' => 'Indonesia',
            ]);
        }

        $this->command->info('Customers created successfully!');
    }
}
