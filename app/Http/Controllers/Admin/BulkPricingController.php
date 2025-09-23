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
                    'basic' => 1.0,
                ],
                'tier_discounts' => [
                    ['storage_gb' => 1, 'discount_percentage' => 0.00],
                    ['storage_gb' => 3, 'discount_percentage' => 3.00],
                    ['storage_gb' => 5, 'discount_percentage' => 7.00],
                    ['storage_gb' => 10, 'discount_percentage' => 12.00],
                    ['storage_gb' => 20, 'discount_percentage' => 20.00],
                    ['storage_gb' => 50, 'discount_percentage' => 30.00],
                    ['storage_gb' => 100, 'discount_percentage' => 40.00],
                    ['storage_gb' => 200, 'discount_percentage' => 45.00],
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
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:bulk_pricing_configs,name',
                'description' => 'nullable|string|max:1000',
                'base_price_per_gb' => 'required|numeric|min:1|max:999999999',
                'cost_per_gb' => 'required|numeric|min:1|max:999999999',
                'plan_multipliers' => 'required|array|min:1',
                'plan_multipliers.*' => 'required|numeric|min:0.1|max:10',
                'tier_discounts' => 'required|array|min:1',
                'tier_discounts.*.storage_gb' => 'required|integer|min:1|max:999999',
                'tier_discounts.*.discount_percentage' => 'required|numeric|min:0|max:100',
                'is_default' => 'nullable|boolean',
            ], [
                'name.required' => 'Nama konfigurasi wajib diisi.',
                'name.unique' => 'Nama konfigurasi sudah digunakan.',
                'base_price_per_gb.required' => 'Harga dasar per GB wajib diisi.',
                'base_price_per_gb.min' => 'Harga dasar per GB minimal 1.',
                'cost_per_gb.required' => 'Biaya per GB wajib diisi.',
                'cost_per_gb.min' => 'Biaya per GB minimal 1.',
                'plan_multipliers.required' => 'Plan multipliers wajib diisi.',
                'tier_discounts.required' => 'Tier discounts wajib diisi.',
                'tier_discounts.*.storage_gb.required' => 'Storage GB pada tier discount wajib diisi.',
                'tier_discounts.*.storage_gb.min' => 'Storage GB minimal 1.',
                'tier_discounts.*.discount_percentage.required' => 'Persentase diskon wajib diisi.',
                'tier_discounts.*.discount_percentage.max' => 'Persentase diskon maksimal 100%.',
            ]);

            // Additional business logic validation
            if ($validated['base_price_per_gb'] <= $validated['cost_per_gb']) {
                return redirect()->back()->withErrors([
                    'base_price_per_gb' => 'Harga dasar per GB harus lebih besar dari biaya per GB.'
                ])->withInput();
            }

            // Validate tier discounts sorting
            $tierDiscounts = collect($validated['tier_discounts'])->sortBy('storage_gb')->values()->all();
            $validated['tier_discounts'] = $tierDiscounts;

            \DB::transaction(function () use ($validated) {
                // If setting as default, remove default from others
                if ($validated['is_default'] ?? false) {
                    BulkPricingConfig::where('is_default', true)->update(['is_default' => false]);
                }

                BulkPricingConfig::create([
                    'name' => $validated['name'],
                    'description' => $validated['description'],
                    'base_price_per_gb' => $validated['base_price_per_gb'],
                    'cost_per_gb' => $validated['cost_per_gb'],
                    'plan_multipliers' => $validated['plan_multipliers'],
                    'tier_discounts' => $validated['tier_discounts'],
                    'is_active' => true,
                    'is_default' => $validated['is_default'] ?? false,
                ]);
            });

            return redirect()->back()->with('success', 'Konfigurasi berhasil disimpan!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Bulk pricing config validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            \Log::error('Failed to save bulk pricing config', [
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors([
                'error' => 'Terjadi kesalahan saat menyimpan konfigurasi. Silakan coba lagi.'
            ])->withInput();
        }
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

        // Get all hosting plans, but only simulate for ones that have multipliers OR are basic/lite
        $hostingPlans = HostingPlan::all();

        foreach ($hostingPlans as $plan) {
            $planType = strtolower($plan->plan_name);

            // Set default multipliers for common plan types if not provided
            $multiplier = $planMultipliers[$planType] ?? ($planType === 'basic' ? 1.0 : ($planType === 'lite' ? 0.77 : null));

            // Skip plans that don't have a multiplier
            if ($multiplier === null) {
                continue;
            }

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
