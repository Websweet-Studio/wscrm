<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use App\Models\AiPackage;
use App\Models\AiProvider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModelController extends Controller
{
    /**
     * Kolom detail spesifikasi model (opsional) — dipakai di store() & update().
     */
    private const DETAIL_COLUMNS = [
        'description', 'upstream_slug', 'cli_command', 'intelligence_index', 'output_speed',
        'context_window', 'cache_read_rate', 'agent_loop_cost', 'released_at',
    ];

    /**
     * Aturan validasi untuk kolom detail.
     */
    private function detailRules(): array
    {
        return [
            'description' => 'nullable|string|max:255',
            'upstream_slug' => 'nullable|string|max:255',
            'cli_command' => 'nullable|string|max:255',
            'intelligence_index' => 'nullable|numeric|min:0|max:999.9',
            'output_speed' => 'nullable|numeric|min:0|max:99999999',
            'context_window' => 'nullable|string|max:32',
            'cache_read_rate' => 'nullable|numeric|min:0',
            'agent_loop_cost' => 'nullable|numeric|min:0',
            'released_at' => 'nullable|date',
        ];
    }

    /**
     * Ambil nilai kolom detail dari data tervalidasi (string kosong → null).
     */
    private function detailAttributes(array $validated): array
    {
        $attributes = [];

        foreach (self::DETAIL_COLUMNS as $column) {
            $value = $validated[$column] ?? null;
            $attributes[$column] = $value === '' ? null : $value;
        }

        return $attributes;
    }

    public function index(): Response
    {
        $models = AiModel::query()
            ->with('provider')
            ->when(request('provider_id'), function ($query, $providerId) {
                $query->where('provider_id', $providerId);
            })
            ->when(request('search'), function ($query, $search) {
                $query->where('model_key', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        // Referensi harga 1 kredit (Rp) = paket aktif termurah per kredit, utk estimasi Rp/1M.
        $creditPrice = AiPackage::active()
            ->where('credits', '>', 0)
            ->get()
            ->map(fn ($p) => (float) $p->final_price / (int) $p->credits)
            ->min();

        return Inertia::render('Admin/Ai/Models/Index', [
            'models' => $models,
            'providers' => AiProvider::orderBy('name')->get(['id', 'name']),
            'filters' => request()->only(['search', 'provider_id']),
            'credit_price' => $creditPrice !== null ? round($creditPrice, 2) : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:ai_providers,id',
            'model_key' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'label' => 'nullable|string|max:50',
            'input_rate' => 'required|numeric|min:0',
            'output_rate' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'supports_vision' => 'boolean',
            'supports_deep_thinking' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ] + $this->detailRules());

        AiModel::create([
            'provider_id' => $validated['provider_id'],
            'model_key' => $validated['model_key'],
            'display_name' => $validated['display_name'] ?? null,
            'label' => $validated['label'] ?: null,
            'input_rate' => $validated['input_rate'],
            'output_rate' => $validated['output_rate'],
            'is_active' => $validated['is_active'] ?? true,
            'supports_vision' => $validated['supports_vision'] ?? false,
            'supports_deep_thinking' => $validated['supports_deep_thinking'] ?? false,
            'sort_order' => $validated['sort_order'] ?? 0,
        ] + $this->detailAttributes($validated));

        return redirect()->back()->with('success', 'Model AI berhasil ditambahkan.');
    }

    public function update(Request $request, AiModel $model)
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:ai_providers,id',
            'model_key' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'label' => 'nullable|string|max:50',
            'input_rate' => 'required|numeric|min:0',
            'output_rate' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'supports_vision' => 'boolean',
            'supports_deep_thinking' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ] + $this->detailRules());

        $model->update([
            'provider_id' => $validated['provider_id'],
            'model_key' => $validated['model_key'],
            'display_name' => $validated['display_name'] ?? null,
            'label' => $validated['label'] ?: null,
            'input_rate' => $validated['input_rate'],
            'output_rate' => $validated['output_rate'],
            'is_active' => $validated['is_active'] ?? true,
            'supports_vision' => $validated['supports_vision'] ?? false,
            'supports_deep_thinking' => $validated['supports_deep_thinking'] ?? false,
            'sort_order' => $validated['sort_order'] ?? 0,
        ] + $this->detailAttributes($validated));

        return redirect()->back()->with('success', 'Model AI berhasil diperbarui.');
    }

    public function destroy(AiModel $model)
    {
        $model->delete();

        return redirect()->back()->with('success', 'Model AI berhasil dihapus.');
    }
}
