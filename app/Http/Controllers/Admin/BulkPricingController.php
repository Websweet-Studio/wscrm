<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        // If no tiers exist, create default ones
        if ($pricingTiers->isEmpty()) {
            foreach (PricingTier::getDefaultTiers() as $tier) {
                PricingTier::create($tier);
            }
            $pricingTiers = PricingTier::active()->ordered()->get();
        }

        return Inertia::render('Admin/BulkPricing/Index', [
            'pricingTiers' => $pricingTiers,
            'hostingPlans' => $hostingPlans,
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

        $basePricePerGb = $request->base_price_per_gb;
        $costPerGb = $request->cost_per_gb;
        $planMultipliers = $request->plan_multipliers;
        $tierDiscounts = $request->tier_discounts;

        $simulation = [];

        foreach ($planMultipliers as $planType => $multiplier) {
            $planData = [];

            foreach ($tierDiscounts as $tierData) {
                $storageGb = $tierData['storage_gb'];
                $discountPercentage = $tierData['discount_percentage'];

                // Calculate price
                $pricePerGb = $basePricePerGb * $multiplier;
                $discountedPricePerGb = $pricePerGb * (1 - $discountPercentage / 100);
                $totalPrice = $discountedPricePerGb * $storageGb;

                // Calculate profit
                $totalCost = $costPerGb * $storageGb;
                $profit = $totalPrice - $totalCost;
                $profitPerGb = $profit / $storageGb;
                $profitMargin = $totalCost > 0 ? ($profit / $totalCost) * 100 : 0;

                $planData[] = [
                    'storage_gb' => $storageGb,
                    'discount_percentage' => $discountPercentage,
                    'price_per_gb' => $discountedPricePerGb,
                    'total_price' => $totalPrice,
                    'total_cost' => $totalCost,
                    'profit' => $profit,
                    'profit_per_gb' => $profitPerGb,
                    'profit_margin' => $profitMargin,
                ];
            }

            $simulation[$planType] = $planData;
        }

        return response()->json([
            'simulation' => $simulation,
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
}
