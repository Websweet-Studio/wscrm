<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Fix all negative discounts
$count = App\Models\HostingPlan::where('discount_percent', '<', 0)
    ->update(['discount_percent' => \DB::raw('ABS(discount_percent)')]);

echo "✅ Fixed {$count} hosting plans with negative discounts\n";

// Verify the fix
$plan = App\Models\HostingPlan::where('plan_name', 'Basic')
    ->where('storage_gb', '2.00')
    ->first();

if ($plan) {
    $finalPrice = $plan->selling_price * (1 - $plan->discount_percent / 100);
    echo "✅ 2GB Basic Plan: {$plan->selling_price} - {$plan->discount_percent}% = Rp" . number_format($finalPrice, 0, ',', '.') . "\n";
}