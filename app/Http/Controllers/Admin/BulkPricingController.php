<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BulkPricingConfig;
use App\Models\HostingPlan;
use App\Models\PricingTier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function simulate(Request $request): Response
    {
        $validated = $request->validate([
            'base_price_per_gb' => ['required', 'numeric', 'min:0', 'gt:cost_per_gb'],
            'cost_per_gb' => ['required', 'numeric', 'min:0'],
            'plan_multipliers' => ['required', 'array', 'min:1'],
            'plan_multipliers.*' => ['numeric', 'min:0.1', 'max:10'],
            'tier_discounts' => ['required', 'array', 'min:1'],
            'tier_discounts.*.storage_gb' => ['required', 'numeric', 'min:0.01'],
            'tier_discounts.*.discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $planMultipliers = $this->normalizePlanMultipliers($validated['plan_multipliers']);
        $tierDiscounts = $this->normalizeTierDiscounts($validated['tier_discounts']);
        $simulation = $this->runSimulation(
            (float) $validated['base_price_per_gb'],
            (float) $validated['cost_per_gb'],
            $planMultipliers,
            $tierDiscounts
        );

        return Inertia::render('Admin/BulkPricing/Index', [
            'pricingTiers' => PricingTier::active()->ordered()->get(),
            'hostingPlans' => HostingPlan::all(),
            'savedConfigs' => BulkPricingConfig::active()->orderBy('name')->get(),
            'defaultConfig' => [
                'base_price_per_gb' => (float) $validated['base_price_per_gb'],
                'cost_per_gb' => (float) $validated['cost_per_gb'],
                'plan_multipliers' => $planMultipliers,
                'tier_discounts' => $tierDiscounts,
            ],
            'simulationResults' => [
                'simulation' => $simulation,
            ],
        ]);
    }

    public function apply(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'base_price_per_gb' => ['required', 'numeric', 'min:0', 'gt:cost_per_gb'],
            'cost_per_gb' => ['required', 'numeric', 'min:0'],
            'plan_multipliers' => ['required', 'array', 'min:1'],
            'plan_multipliers.*' => ['numeric', 'min:0.1', 'max:10'],
            'tier_discounts' => ['required', 'array', 'min:1'],
            'tier_discounts.*.storage_gb' => ['required', 'numeric', 'min:0.01'],
            'tier_discounts.*.discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'plan_ids' => ['required', 'array', 'min:1'],
            'plan_ids.*' => ['integer', 'distinct', 'exists:hosting_plans,id'],
        ]);

        $basePricePerGb = (float) $validated['base_price_per_gb'];
        $costPerGb = (float) $validated['cost_per_gb'];

        $planMultipliers = $this->normalizePlanMultipliers($validated['plan_multipliers']);
        $tierDiscounts = $this->normalizeTierDiscounts($validated['tier_discounts']);
        $planIds = array_values(array_unique(array_map('intval', $validated['plan_ids'])));

        $result = DB::transaction(function () use ($basePricePerGb, $costPerGb, $planMultipliers, $tierDiscounts, $planIds) {
            PricingTier::query()->delete();
            foreach ($tierDiscounts as $index => $tierData) {
                PricingTier::create([
                    'storage_gb' => (int) round((float) $tierData['storage_gb']),
                    'discount_percentage' => (float) $tierData['discount_percentage'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }

            $plans = HostingPlan::whereIn('id', $planIds)->get()->keyBy('id');

            $updated = 0;
            $skipped = [];
            foreach ($planIds as $planId) {
                $plan = $plans->get($planId);
                if (! $plan) {
                    continue;
                }

                $planType = strtolower((string) $plan->plan_name);
                $multiplier = $planMultipliers[$planType] ?? null;
                if ($multiplier === null) {
                    $skipped[] = "{$plan->plan_name} (#{$plan->id})";
                    continue;
                }

                $storageGb = (float) $plan->storage_gb;
                $discount = $this->determineDiscountForStorage($storageGb, $tierDiscounts);

                $pricePerGb = $basePricePerGb * (float) $multiplier;
                $discountedPricePerGb = $pricePerGb * (1 - $discount / 100);
                $newPrice = $discountedPricePerGb * $storageGb;

                $plan->update([
                    'selling_price' => $newPrice,
                    'base_price_per_gb' => $basePricePerGb,
                    'plan_multiplier' => (float) $multiplier,
                    'cost_per_gb' => $costPerGb,
                    'use_bulk_pricing' => true,
                ]);

                $updated++;
            }

            return [
                'updated' => $updated,
                'skipped' => $skipped,
            ];
        });

        $message = "{$result['updated']} hosting plan berhasil diupdate.";
        if (! empty($result['skipped'])) {
            $message .= ' Dilewati (multiplier tidak tersedia): '.implode(', ', array_slice($result['skipped'], 0, 10)).(count($result['skipped']) > 10 ? '…' : '');
        }

        return redirect()->back()->with('success', $message);
    }

    public function saveConfig(Request $request): RedirectResponse
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
                    'base_price_per_gb' => 'Harga dasar per GB harus lebih besar dari biaya per GB.',
                ])->withInput();
            }

            // Validate tier discounts sorting
            $tierDiscounts = collect($validated['tier_discounts'])->sortBy('storage_gb')->values()->all();
            $validated['tier_discounts'] = $tierDiscounts;

            DB::transaction(function () use ($validated) {
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
            Log::error('Bulk pricing config validation failed', [
                'errors' => $e->errors(),
                'input_keys' => array_keys($request->all()),
            ]);

            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Failed to save bulk pricing config', [
                'error' => $e->getMessage(),
                'input_keys' => array_keys($request->all()),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withErrors([
                'error' => 'Terjadi kesalahan saat menyimpan konfigurasi. Silakan coba lagi.',
            ])->withInput();
        }
    }

    public function loadConfig($id): JsonResponse
    {
        $config = BulkPricingConfig::findOrFail($id);

        return response()->json([
            'base_price_per_gb' => $config->base_price_per_gb,
            'cost_per_gb' => $config->cost_per_gb,
            'plan_multipliers' => $config->plan_multipliers,
            'tier_discounts' => $config->tier_discounts,
        ]);
    }

    public function deleteConfig($id): RedirectResponse
    {
        $config = BulkPricingConfig::findOrFail($id);

        if ($config->is_default) {
            return redirect()->back()->withErrors(['error' => 'Tidak dapat menghapus konfigurasi default!']);
        }

        $config->delete();

        return redirect()->back()->with('success', 'Konfigurasi berhasil dihapus!');
    }

    private function normalizePlanMultipliers(array $planMultipliers): array
    {
        $normalized = [];
        foreach ($planMultipliers as $key => $value) {
            $normalizedKey = strtolower(trim((string) $key));
            $normalized[$normalizedKey] = (float) $value;
        }

        return $normalized;
    }

    private function normalizeTierDiscounts(array $tierDiscounts): array
    {
        $normalized = collect($tierDiscounts)
            ->map(function ($tier) {
                return [
                    'storage_gb' => (float) ($tier['storage_gb'] ?? 0),
                    'discount_percentage' => (float) ($tier['discount_percentage'] ?? 0),
                ];
            })
            ->filter(fn ($tier) => $tier['storage_gb'] > 0)
            ->sortBy('storage_gb')
            ->values()
            ->all();

        if (empty($normalized)) {
            return [
                ['storage_gb' => 1, 'discount_percentage' => 0.0],
            ];
        }

        return $normalized;
    }

    private function determineDiscountForStorage(float $storageGb, array $tierDiscounts): float
    {
        $discount = 0.0;
        foreach ($tierDiscounts as $tierData) {
            $tierStorage = (float) ($tierData['storage_gb'] ?? 0);
            if ($tierStorage <= 0) {
                continue;
            }

            if ($tierStorage <= $storageGb) {
                $discount = (float) ($tierData['discount_percentage'] ?? 0);
                continue;
            }

            break;
        }

        return $discount;
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

            $storageGb = (float) $plan->storage_gb;
            $discount = $this->determineDiscountForStorage($storageGb, $tierDiscounts);

            // Calculate new price
            $pricePerGb = $basePricePerGb * $multiplier;
            $discountedPricePerGb = $pricePerGb * (1 - $discount / 100);
            $newTotalPrice = $discountedPricePerGb * $storageGb;

            // Calculate profit
            $totalCost = $costPerGb * $storageGb;
            $profit = $newTotalPrice - $totalCost;
            $profitPerGb = $profit / $storageGb;
            $profitMargin = $totalCost > 0 ? ($profit / $totalCost) * 100 : 0;

            if (! isset($simulation[$planType])) {
                $simulation[$planType] = [];
            }

            $simulation[$planType][] = [
                'plan_id' => $plan->id,
                'plan_name' => $plan->plan_name,
                'storage_gb' => $storageGb,
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
