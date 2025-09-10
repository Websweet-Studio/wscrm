<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BulkPricingConfig;
use App\Models\HostingPlan;
use App\Models\PricingTier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BulkPricingController extends Controller
{
    public function index(): Response
    {
        $pricingTiers = PricingTier::active()->ordered()->get();
        $hostingPlans = HostingPlan::all();
        $savedConfigs = BulkPricingConfig::active()->orderBy('name')->get();
        $defaultConfig = BulkPricingConfig::getDefaultConfig();

        // If no tiers exist, create default ones
        if ($pricingTiers->isEmpty()) {
            foreach (PricingTier::getDefaultTiers() as $tier) {
                PricingTier::create($tier);
            }
            $pricingTiers = PricingTier::active()->ordered()->get();
        }

        // Run initial simulation with default config
        $initialSimulation = $this->runSimulation(
            $defaultConfig['base_price_per_gb'],
            $defaultConfig['cost_per_gb'],
            $defaultConfig['plan_multipliers'],
            $defaultConfig['tier_discounts']
        );

        return Inertia::render('Admin/BulkPricing/Index', [
            'pricingTiers' => $pricingTiers,
            'hostingPlans' => $hostingPlans,
            'savedConfigs' => $savedConfigs,
            'defaultConfig' => $defaultConfig,
            'simulationResults' => [
                'simulation' => $initialSimulation,
            ],
        ]);
    }

    public function simulate(Request $request)
    {
        $request->validate([
            'base_price_per_gb' => 'required|numeric|min:0',
            'cost_per_gb' => 'required|numeric|min:0',
            'plan_multipliers' => 'required|array',
            'tier_discounts' => 'required|array',
        ]);

        $simulation = $this->runSimulation(
            $request->base_price_per_gb,
            $request->cost_per_gb,
            $request->plan_multipliers,
            $request->tier_discounts
        );

        return Inertia::render('Admin/BulkPricing/Index', [
            'pricingTiers' => PricingTier::orderBy('sort_order')->get(),
            'hostingPlans' => HostingPlan::all(),
            'savedConfigs' => BulkPricingConfig::all(),
            'defaultConfig' => [
                'base_price_per_gb' => 150000,
                'cost_per_gb' => 112500,
                'plan_multipliers' => [
                    'starter' => 1.0,
                    'professional' => 1.2,
                    'business' => 1.5,
                    'enterprise' => 2.0,
                ],
                'tier_discounts' => [
                    ['storage_gb' => 1, 'discount_percentage' => 0],
                    ['storage_gb' => 5, 'discount_percentage' => 5],
                    ['storage_gb' => 20, 'discount_percentage' => 10],
                    ['storage_gb' => 50, 'discount_percentage' => 15],
                    ['storage_gb' => 100, 'discount_percentage' => 20],
                    ['storage_gb' => 200, 'discount_percentage' => 25],
                ],
            ],
            'simulationResults' => [
                'simulation' => $simulation,
            ],
        ]);
    }

    public function apply(Request $request)
    {
        $request->validate([
            'base_price_per_gb' => 'required|numeric|min:0',
            'cost_per_gb' => 'required|numeric|min:0',
            'plan_multipliers' => 'required|array',
            'tier_discounts' => 'required|array',
            'plan_ids' => 'required|array',
        ]);

        $basePricePerGb = $request->base_price_per_gb;
        $costPerGb = $request->cost_per_gb;
        $planMultipliers = $request->plan_multipliers;
        $tierDiscounts = $request->tier_discounts;
        $planIds = $request->plan_ids;

        // Update pricing tiers
        PricingTier::truncate();
        foreach ($tierDiscounts as $index => $tierData) {
            PricingTier::create([
                'storage_gb' => $tierData['storage_gb'],
                'discount_percentage' => $tierData['discount_percentage'],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }

        // Update hosting plans
        foreach ($planIds as $planId) {
            $plan = HostingPlan::findOrFail($planId);
            $planType = strtolower($plan->plan_name);
            $multiplier = $planMultipliers[$planType] ?? 1.0;

            // Find discount for this plan's storage
            $discount = 0;
            foreach ($tierDiscounts as $tierData) {
                if ($tierData['storage_gb'] == $plan->storage_gb) {
                    $discount = $tierData['discount_percentage'];
                    break;
                }
            }

            // Calculate new price
            $pricePerGb = $basePricePerGb * $multiplier;
            $discountedPricePerGb = $pricePerGb * (1 - $discount / 100);
            $newPrice = $discountedPricePerGb * $plan->storage_gb;

            $plan->update([
                'selling_price' => $newPrice,
                'base_price_per_gb' => $basePricePerGb,
                'plan_multiplier' => $multiplier,
                'cost_per_gb' => $costPerGb,
                'use_bulk_pricing' => true,
            ]);
        }

        return redirect()->back()->with('success', 'Bulk pricing berhasil diterapkan!');
    }

    public function saveConfig(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price_per_gb' => 'required|numeric|min:0',
            'cost_per_gb' => 'required|numeric|min:0',
            'plan_multipliers' => 'required|array',
            'tier_discounts' => 'required|array',
            'is_default' => 'boolean',
        ]);

        // If setting as default, remove default from others
        if ($request->is_default) {
            BulkPricingConfig::where('is_default', true)->update(['is_default' => false]);
        }

        BulkPricingConfig::create([
            'name' => $request->name,
            'description' => $request->description,
            'base_price_per_gb' => $request->base_price_per_gb,
            'cost_per_gb' => $request->cost_per_gb,
            'plan_multipliers' => $request->plan_multipliers,
            'tier_discounts' => $request->tier_discounts,
            'is_active' => true,
            'is_default' => $request->is_default ?? false,
        ]);

        return redirect()->back()->with('success', 'Konfigurasi berhasil disimpan!');
    }

    public function loadConfig($id)
    {
        $config = BulkPricingConfig::findOrFail($id);

        return response()->json([
            'base_price_per_gb' => $config->base_price_per_gb,
            'cost_per_gb' => $config->cost_per_gb,
            'plan_multipliers' => $config->plan_multipliers,
            'tier_discounts' => $config->tier_discounts,
        ]);
    }

    public function deleteConfig($id)
    {
        $config = BulkPricingConfig::findOrFail($id);

        if ($config->is_default) {
            return redirect()->back()->withErrors(['error' => 'Tidak dapat menghapus konfigurasi default!']);
        }

        $config->delete();

        return redirect()->back()->with('success', 'Konfigurasi berhasil dihapus!');
    }

    private function runSimulation(float $basePricePerGb, float $costPerGb, array $planMultipliers, array $tierDiscounts): array
    {
        $simulation = [];
        $hostingPlans = HostingPlan::all();

        foreach ($hostingPlans as $plan) {
            $planType = strtolower($plan->plan_name);
            $multiplier = $planMultipliers[$planType] ?? 1.0;

            // Find matching discount tier for this plan's storage
            $discount = 0;
            foreach ($tierDiscounts as $tierData) {
                if ($tierData['storage_gb'] <= $plan->storage_gb) {
                    $discount = $tierData['discount_percentage'];
                }
            }

            // Calculate new price
            $pricePerGb = $basePricePerGb * $multiplier;
            $discountedPricePerGb = $pricePerGb * (1 - $discount / 100);
            $newTotalPrice = $discountedPricePerGb * $plan->storage_gb;

            // Calculate profit
            $totalCost = $costPerGb * $plan->storage_gb;
            $profit = $newTotalPrice - $totalCost;
            $profitPerGb = $profit / $plan->storage_gb;
            $profitMargin = $totalCost > 0 ? ($profit / $totalCost) * 100 : 0;

            if (! isset($simulation[$planType])) {
                $simulation[$planType] = [];
            }

            $simulation[$planType][] = [
                'plan_id' => $plan->id,
                'plan_name' => $plan->plan_name,
                'storage_gb' => $plan->storage_gb,
                'cpu_cores' => $plan->cpu_cores,
                'ram_gb' => $plan->ram_gb,
                'current_price' => $plan->selling_price,
                'discount_percentage' => $discount,
                'price_per_gb' => $discountedPricePerGb,
                'new_total_price' => $newTotalPrice,
                'price_difference' => $newTotalPrice - $plan->selling_price,
                'total_cost' => $totalCost,
                'profit' => $profit,
                'profit_per_gb' => $profitPerGb,
                'profit_margin' => $profitMargin,
            ];
        }

        return $simulation;
    }
}
