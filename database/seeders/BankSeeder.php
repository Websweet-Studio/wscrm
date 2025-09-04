<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            [
                'bank_name' => 'Bank Central Asia',
                'bank_code' => 'BCA',
                'account_number' => '1234567890',
                'account_name' => 'PT. Web Solutions CRM',
                'branch' => 'Jakarta Pusat',
                'swift_code' => 'CENAIDJA',
                'description' => 'Bank utama untuk pembayaran lokal',
                'is_active' => true,
                'admin_fee' => 2500.00,
                'bank_type' => 'local',
            ],
            [
                'bank_name' => 'Bank Mandiri',
                'bank_code' => 'MANDIRI',
                'account_number' => '9876543210',
                'account_name' => 'PT. Web Solutions CRM',
                'branch' => 'Jakarta Selatan',
                'swift_code' => 'BMRIIDJA',
                'description' => 'Bank alternatif untuk pembayaran',
                'is_active' => true,
                'admin_fee' => 3000.00,
                'bank_type' => 'local',
            ],
            [
                'bank_name' => 'Bank Rakyat Indonesia',
                'bank_code' => 'BRI',
                'account_number' => '5555666677',
                'account_name' => 'PT. Web Solutions CRM',
                'branch' => 'Jakarta Barat',
                'swift_code' => 'BRINIDJA',
                'description' => 'Bank untuk pembayaran UMKM',
                'is_active' => true,
                'admin_fee' => 2000.00,
                'bank_type' => 'local',
            ],
            [
                'bank_name' => 'Bank Negara Indonesia',
                'bank_code' => 'BNI',
                'account_number' => '1111222233',
                'account_name' => 'PT. Web Solutions CRM',
                'branch' => 'Jakarta Timur',
                'swift_code' => 'BNINIDJA',
                'description' => 'Bank untuk pembayaran internasional',
                'is_active' => true,
                'admin_fee' => 2750.00,
                'bank_type' => 'international',
            ],
            [
                'bank_name' => 'Bank CIMB Niaga',
                'bank_code' => 'CIMB',
                'account_number' => '7777888899',
                'account_name' => 'PT. Web Solutions CRM',
                'branch' => 'Jakarta Utara',
                'swift_code' => 'BNIAIDJA',
                'description' => 'Bank untuk pembayaran corporate',
                'is_active' => false,
                'admin_fee' => 3500.00,
                'bank_type' => 'local',
            ],
        ];

        // Create banks from the predefined data
        foreach ($banks as $bankData) {
            Bank::updateOrCreate(
                ['bank_code' => $bankData['bank_code']],
                $bankData
            );
        }

        // Create additional random banks only if we don't have enough
        $currentCount = Bank::count();
        if ($currentCount < 10) {
            Bank::factory(10 - $currentCount)->create();
        }

        $this->command->info('Banks seeded successfully!');
    }
}
