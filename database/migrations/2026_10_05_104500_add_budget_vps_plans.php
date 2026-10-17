<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ponytail: insert data saja, upgrade path = seeder bila butuh varian period lain
        $plans = [
            ['plan_name' => 'Budget VPS 1', 'storage_gb' => 40, 'cpu_cores' => 4, 'ram_gb' => 8, 'bandwidth' => '1.5 TB', 'modal_cost' => 149000, 'selling_price' => 298000],
            ['plan_name' => 'Budget VPS 2', 'storage_gb' => 75, 'cpu_cores' => 6, 'ram_gb' => 16, 'bandwidth' => '2.5 TB', 'modal_cost' => 275000, 'selling_price' => 550000],
            ['plan_name' => 'Budget VPS 3', 'storage_gb' => 100, 'cpu_cores' => 8, 'ram_gb' => 24, 'bandwidth' => '3.5 TB', 'modal_cost' => 447000, 'selling_price' => 894000],
            ['plan_name' => 'Budget VPS 4', 'storage_gb' => 150, 'cpu_cores' => 10, 'ram_gb' => 32, 'bandwidth' => '5 TB', 'modal_cost' => 778000, 'selling_price' => 1556000],
        ];

        foreach ($plans as $p) {
            DB::table('hosting_plans')->updateOrInsert(
                ['plan_name' => $p['plan_name']],
                array_merge($p, [
                    'service_type' => 'vps',
                    'billing_period' => 'monthly',
                    'maintenance_cost' => 0,
                    'discount_percent' => 0,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        DB::table('hosting_plans')
            ->whereIn('plan_name', ['Budget VPS 1', 'Budget VPS 2', 'Budget VPS 3', 'Budget VPS 4'])
            ->delete();
    }
};
