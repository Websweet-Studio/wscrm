<?php

namespace Database\Seeders;

use App\Models\HostingPlan;
use Illuminate\Database\Seeder;

class HostingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $basicPlans = [
            ['storage_gb' => 0.50, 'cpu_cores' => 1.00, 'ram_gb' => 1.00, 'modal_cost' => 41250, 'maintenance_cost' => 65000, 'discount_percent' => 15, 'selling_price' => 75000],
            ['storage_gb' => 0.75, 'cpu_cores' => 1.00, 'ram_gb' => 1.00, 'modal_cost' => 61875, 'maintenance_cost' => 97500, 'discount_percent' => 0, 'selling_price' => 100000],
            ['storage_gb' => 1.00, 'cpu_cores' => 1.30, 'ram_gb' => 2.60, 'modal_cost' => 82500, 'maintenance_cost' => 130000, 'discount_percent' => 0, 'selling_price' => 130000],
            ['storage_gb' => 2.00, 'cpu_cores' => 1.30, 'ram_gb' => 2.60, 'modal_cost' => 165000, 'maintenance_cost' => 260000, 'discount_percent' => -15, 'selling_price' => 220000],
            ['storage_gb' => 3.00, 'cpu_cores' => 1.75, 'ram_gb' => 3.50, 'modal_cost' => 247500, 'maintenance_cost' => 390000, 'discount_percent' => -15, 'selling_price' => 330000],
            ['storage_gb' => 4.00, 'cpu_cores' => 1.75, 'ram_gb' => 3.50, 'modal_cost' => 330000, 'maintenance_cost' => 520000, 'discount_percent' => -15, 'selling_price' => 440000],
            ['storage_gb' => 5.00, 'cpu_cores' => 2.00, 'ram_gb' => 4.00, 'modal_cost' => 412500, 'maintenance_cost' => 650000, 'discount_percent' => -15, 'selling_price' => 555000],
            ['storage_gb' => 6.00, 'cpu_cores' => 2.00, 'ram_gb' => 4.00, 'modal_cost' => 495000, 'maintenance_cost' => 780000, 'discount_percent' => -15, 'selling_price' => 665000],
            ['storage_gb' => 7.00, 'cpu_cores' => 2.00, 'ram_gb' => 4.00, 'modal_cost' => 577500, 'maintenance_cost' => 910000, 'discount_percent' => -15, 'selling_price' => 775000],
            ['storage_gb' => 8.00, 'cpu_cores' => 2.00, 'ram_gb' => 4.00, 'modal_cost' => 660000, 'maintenance_cost' => 1040000, 'discount_percent' => -15, 'selling_price' => 885000],
            ['storage_gb' => 9.00, 'cpu_cores' => 2.00, 'ram_gb' => 4.00, 'modal_cost' => 742500, 'maintenance_cost' => 1170000, 'discount_percent' => -15, 'selling_price' => 995000],
            ['storage_gb' => 10.00, 'cpu_cores' => 2.50, 'ram_gb' => 5.00, 'modal_cost' => 825000, 'maintenance_cost' => 1300000, 'discount_percent' => -20, 'selling_price' => 1040000],
            ['storage_gb' => 20.00, 'cpu_cores' => 2.50, 'ram_gb' => 5.00, 'modal_cost' => 1650000, 'maintenance_cost' => 2600000, 'discount_percent' => -20, 'selling_price' => 2080000],
            ['storage_gb' => 30.00, 'cpu_cores' => 2.50, 'ram_gb' => 5.00, 'modal_cost' => 2475000, 'maintenance_cost' => 3900000, 'discount_percent' => -20, 'selling_price' => 3120000],
            ['storage_gb' => 40.00, 'cpu_cores' => 2.50, 'ram_gb' => 5.00, 'modal_cost' => 3300000, 'maintenance_cost' => 5200000, 'discount_percent' => -20, 'selling_price' => 4160000],
            ['storage_gb' => 50.00, 'cpu_cores' => 3.00, 'ram_gb' => 6.00, 'modal_cost' => 4125000, 'maintenance_cost' => 6500000, 'discount_percent' => -20, 'selling_price' => 5200000],
            ['storage_gb' => 60.00, 'cpu_cores' => 3.00, 'ram_gb' => 6.00, 'modal_cost' => 4950000, 'maintenance_cost' => 7800000, 'discount_percent' => -25, 'selling_price' => 5850000],
            ['storage_gb' => 70.00, 'cpu_cores' => 3.00, 'ram_gb' => 6.00, 'modal_cost' => 5775000, 'maintenance_cost' => 9100000, 'discount_percent' => -25, 'selling_price' => 6825000],
            ['storage_gb' => 80.00, 'cpu_cores' => 3.00, 'ram_gb' => 6.00, 'modal_cost' => 6600000, 'maintenance_cost' => 10400000, 'discount_percent' => -25, 'selling_price' => 7800000],
            ['storage_gb' => 90.00, 'cpu_cores' => 3.00, 'ram_gb' => 6.00, 'modal_cost' => 7425000, 'maintenance_cost' => 11700000, 'discount_percent' => -25, 'selling_price' => 8775000],
            ['storage_gb' => 100.00, 'cpu_cores' => 3.00, 'ram_gb' => 6.00, 'modal_cost' => 8250000, 'maintenance_cost' => 13000000, 'discount_percent' => -25, 'selling_price' => 9750000],
            ['storage_gb' => 110.00, 'cpu_cores' => 4.00, 'ram_gb' => 8.00, 'modal_cost' => 9075000, 'maintenance_cost' => 14300000, 'discount_percent' => -25, 'selling_price' => 10725000],
        ];

        $litePlans = [
            ['storage_gb' => 0.50, 'cpu_cores' => 0.75, 'ram_gb' => 0.50, 'modal_cost' => 37275, 'maintenance_cost' => 50000, 'discount_percent' => 0, 'selling_price' => 50000],
            ['storage_gb' => 0.75, 'cpu_cores' => 0.75, 'ram_gb' => 0.50, 'modal_cost' => 55912.50, 'maintenance_cost' => 75000, 'discount_percent' => 0, 'selling_price' => 75000],
            ['storage_gb' => 1.00, 'cpu_cores' => 0.75, 'ram_gb' => 0.50, 'modal_cost' => 74550, 'maintenance_cost' => 100000, 'discount_percent' => 0, 'selling_price' => 100000],
            ['storage_gb' => 2.00, 'cpu_cores' => 1.00, 'ram_gb' => 1.00, 'modal_cost' => 149100, 'maintenance_cost' => 200000, 'discount_percent' => -26, 'selling_price' => 150000],
            ['storage_gb' => 3.00, 'cpu_cores' => 1.00, 'ram_gb' => 1.00, 'modal_cost' => 223650, 'maintenance_cost' => 300000, 'discount_percent' => -26, 'selling_price' => 225000],
            ['storage_gb' => 4.00, 'cpu_cores' => 1.00, 'ram_gb' => 1.00, 'modal_cost' => 298200, 'maintenance_cost' => 400000, 'discount_percent' => -26, 'selling_price' => 300000],
            ['storage_gb' => 5.00, 'cpu_cores' => 1.00, 'ram_gb' => 2.00, 'modal_cost' => 372750, 'maintenance_cost' => 500000, 'discount_percent' => -26, 'selling_price' => 375000],
            ['storage_gb' => 6.00, 'cpu_cores' => 1.00, 'ram_gb' => 2.00, 'modal_cost' => 447300, 'maintenance_cost' => 600000, 'discount_percent' => -26, 'selling_price' => 445000],
            ['storage_gb' => 7.00, 'cpu_cores' => 1.00, 'ram_gb' => 2.00, 'modal_cost' => 521850, 'maintenance_cost' => 700000, 'discount_percent' => -26, 'selling_price' => 520000],
            ['storage_gb' => 8.00, 'cpu_cores' => 1.00, 'ram_gb' => 2.00, 'modal_cost' => 596400, 'maintenance_cost' => 800000, 'discount_percent' => -26, 'selling_price' => 595000],
            ['storage_gb' => 9.00, 'cpu_cores' => 1.00, 'ram_gb' => 2.00, 'modal_cost' => 670950, 'maintenance_cost' => 900000, 'discount_percent' => -26, 'selling_price' => 670000],
            ['storage_gb' => 10.00, 'cpu_cores' => 2.00, 'ram_gb' => 3.00, 'modal_cost' => 745500, 'maintenance_cost' => 1000000, 'discount_percent' => -34, 'selling_price' => 660000],
            ['storage_gb' => 20.00, 'cpu_cores' => 2.00, 'ram_gb' => 3.00, 'modal_cost' => 1491000, 'maintenance_cost' => 2000000, 'discount_percent' => -34, 'selling_price' => 1320000],
            ['storage_gb' => 30.00, 'cpu_cores' => 2.00, 'ram_gb' => 3.00, 'modal_cost' => 2236500, 'maintenance_cost' => 3000000, 'discount_percent' => -34, 'selling_price' => 1980000],
            ['storage_gb' => 40.00, 'cpu_cores' => 2.00, 'ram_gb' => 3.00, 'modal_cost' => 2982000, 'maintenance_cost' => 4000000, 'discount_percent' => -34, 'selling_price' => 2640000],
            ['storage_gb' => 50.00, 'cpu_cores' => 2.00, 'ram_gb' => 3.00, 'modal_cost' => 3727500, 'maintenance_cost' => 5000000, 'discount_percent' => -34, 'selling_price' => 3300000],
            ['storage_gb' => 60.00, 'cpu_cores' => 3.00, 'ram_gb' => 3.00, 'modal_cost' => 4473000, 'maintenance_cost' => 6000000, 'discount_percent' => -43, 'selling_price' => 3450000],
            ['storage_gb' => 70.00, 'cpu_cores' => 3.00, 'ram_gb' => 3.00, 'modal_cost' => 5218500, 'maintenance_cost' => 7000000, 'discount_percent' => -43, 'selling_price' => 4025000],
            ['storage_gb' => 80.00, 'cpu_cores' => 3.00, 'ram_gb' => 3.00, 'modal_cost' => 5964000, 'maintenance_cost' => 8000000, 'discount_percent' => -43, 'selling_price' => 4600000],
            ['storage_gb' => 90.00, 'cpu_cores' => 3.00, 'ram_gb' => 3.00, 'modal_cost' => 6709500, 'maintenance_cost' => 9000000, 'discount_percent' => -43, 'selling_price' => 5175000],
            ['storage_gb' => 100.00, 'cpu_cores' => 3.00, 'ram_gb' => 3.00, 'modal_cost' => 7455000, 'maintenance_cost' => 10000000, 'discount_percent' => -43, 'selling_price' => 5750000],
            ['storage_gb' => 110.00, 'cpu_cores' => 3.00, 'ram_gb' => 4.00, 'modal_cost' => 8200500, 'maintenance_cost' => 11000000, 'discount_percent' => -43, 'selling_price' => 6325000],
        ];

        // Create Basic plans
        foreach ($basicPlans as $plan) {
            $values = array_merge($plan, [
                'plan_name' => 'Basic',
                'bandwidth' => 'Unlimited',
                'features' => [
                    'email_accounts' => 'unlimited',
                    'databases' => 'unlimited',
                    'ssl_certificate' => true,
                    'backup' => 'daily',
                ],
                'is_active' => true,
            ]);

            HostingPlan::updateOrCreate(
                [
                    'plan_name' => $values['plan_name'],
                    'storage_gb' => $values['storage_gb'],
                    'cpu_cores' => $values['cpu_cores'],
                    'ram_gb' => $values['ram_gb'],
                ],
                $values
            );
        }

        // Create Lite plans
        foreach ($litePlans as $plan) {
            $values = array_merge($plan, [
                'plan_name' => 'Lite',
                'bandwidth' => 'Unlimited',
                'features' => [
                    'email_accounts' => 10,
                    'databases' => 5,
                    'ssl_certificate' => true,
                    'backup' => 'weekly',
                ],
                'is_active' => true,
            ]);

            HostingPlan::updateOrCreate(
                [
                    'plan_name' => $values['plan_name'],
                    'storage_gb' => $values['storage_gb'],
                    'cpu_cores' => $values['cpu_cores'],
                    'ram_gb' => $values['ram_gb'],
                ],
                $values
            );
        }
    }
}
