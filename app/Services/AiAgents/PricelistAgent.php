<?php

namespace App\Services\AiAgents;

use App\Models\DomainPrice;
use App\Models\HostingPlan;

/**
 * Sub-agent: manajemen pricelist — harga domain & paket hosting.
 */
class PricelistAgent
{
    public function listDomainPrices(?string $extension = null, ?callable $onEvent = null): array
    {
        if ($onEvent) {
            $onEvent('Menarik daftar harga domain...', 'loading', 'Pricelist Agent');
        }

        $query = DomainPrice::query()
            ->when($extension, fn ($q) => $q->where('extension', 'like', "%{$extension}%"))
            ->orderBy('extension');

        $list = $query->get()->map(fn (DomainPrice $d) => [
            'id' => $d->id,
            'extension' => $d->extension,
            'base_cost' => (float) $d->base_cost,
            'renewal_cost' => (float) $d->renewal_cost,
            'selling_price' => (float) $d->selling_price,
            'renewal_price_with_tax' => (float) $d->renewal_price_with_tax,
            'is_active' => (bool) $d->is_active,
        ])->values()->all();

        if ($onEvent) {
            $onEvent("Ditemukan " . count($list) . " harga domain", 'done', 'Pricelist Agent');
        }

        return [
            'domain_prices' => $list,
            'total' => count($list),
            'summary' => count($list) . ' harga domain ditemukan.',
        ];
    }

    public function updateDomainPrice(int $id, array $data, ?callable $onEvent = null): array
    {
        $domain = DomainPrice::find($id);
        if (!$domain) {
            return ['error' => 'Harga domain tidak ditemukan.'];
        }

        if ($onEvent) {
            $onEvent("Memperbarui harga domain {$domain->extension}...", 'loading', 'Pricelist Agent');
        }

        $fields = ['extension', 'base_cost', 'renewal_cost', 'selling_price', 'renewal_price_with_tax', 'is_active'];

        $validated = validator($data, [
            'extension' => 'nullable|string|unique:domain_prices,extension,'.$domain->id,
            'base_cost' => 'nullable|numeric|min:0',
            'renewal_cost' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'renewal_price_with_tax' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ])->validate();

        $updates = array_intersect_key($validated, array_flip($fields));
        if (empty($updates)) {
            return ['error' => 'Tidak ada data harga domain yang valid untuk diubah.'];
        }

        $domain->update($updates);

        if ($onEvent) {
            $onEvent("Harga domain {$domain->extension} berhasil diperbarui", 'done', 'Pricelist Agent');
        }

        return [
            'success' => true,
            'id' => $domain->id,
            'extension' => $domain->extension,
            'selling_price' => (float) $domain->selling_price,
            'message' => 'Harga domain ' . $domain->extension . ' berhasil diperbarui (harga jual: Rp '
                . number_format((float) $domain->selling_price, 0, ',', '.') . ').',
        ];
    }

    public function listHostingPlans(?string $planName = null, ?callable $onEvent = null): array
    {
        if ($onEvent) {
            $onEvent('Menarik daftar paket hosting...', 'loading', 'Pricelist Agent');
        }

        $query = HostingPlan::query()
            ->when($planName, fn ($q) => $q->where('plan_name', 'like', "%{$planName}%"))
            ->orderBy('plan_name');

        $list = $query->get()->map(fn (HostingPlan $h) => [
            'id' => $h->id,
            'plan_name' => $h->plan_name,
            'service_type' => $h->service_type,
            'storage_gb' => (float) $h->storage_gb,
            'cpu_cores' => (float) $h->cpu_cores,
            'ram_gb' => (float) $h->ram_gb,
            'modal_cost' => (float) $h->modal_cost,
            'maintenance_cost' => (float) $h->maintenance_cost,
            'discount_percent' => (float) $h->discount_percent,
            'selling_price' => (float) $h->selling_price,
            'final_price' => round($h->finalPrice(), 2),
            'is_active' => (bool) $h->is_active,
        ])->values()->all();

        if ($onEvent) {
            $onEvent("Ditemukan " . count($list) . " paket hosting", 'done', 'Pricelist Agent');
        }

        return [
            'hosting_plans' => $list,
            'total' => count($list),
            'summary' => count($list) . ' paket hosting ditemukan.',
        ];
    }

    public function updateHostingPrice(int $id, array $data, ?callable $onEvent = null): array
    {
        $plan = HostingPlan::find($id);
        if (!$plan) {
            return ['error' => 'Paket hosting tidak ditemukan.'];
        }

        if ($onEvent) {
            $onEvent("Memperbarui harga paket {$plan->plan_name}...", 'loading', 'Pricelist Agent');
        }

        $validated = validator($data, [
            'selling_price' => 'nullable|numeric|min:0',
            'modal_cost' => 'nullable|numeric|min:0',
            'maintenance_cost' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ])->validate();

        $fields = ['selling_price', 'modal_cost', 'maintenance_cost', 'discount_percent', 'is_active'];
        $updates = array_intersect_key($validated, array_flip($fields));
        if (empty($updates)) {
            return ['error' => 'Tidak ada data harga hosting yang valid untuk diubah.'];
        }

        $plan->update($updates);

        if ($onEvent) {
            $onEvent("Harga paket {$plan->plan_name} berhasil diperbarui", 'done', 'Pricelist Agent');
        }

        return [
            'success' => true,
            'id' => $plan->id,
            'plan_name' => $plan->plan_name,
            'selling_price' => (float) $plan->selling_price,
            'final_price' => round($plan->finalPrice(), 2),
            'message' => 'Harga paket ' . $plan->plan_name . ' berhasil diperbarui (harga jual: Rp '
                . number_format((float) $plan->selling_price, 0, ',', '.') . ').',
        ];
    }
}
