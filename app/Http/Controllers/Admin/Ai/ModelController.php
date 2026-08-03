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
            'credit_price' => $creditPrice ? round($creditPrice, 2) : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:ai_providers,id',
            'model_key' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'input_rate' => 'required|numeric|min:0',
            'output_rate' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        AiModel::create([
            'provider_id' => $validated['provider_id'],
            'model_key' => $validated['model_key'],
            'display_name' => $validated['display_name'] ?? null,
            'input_rate' => $validated['input_rate'],
            'output_rate' => $validated['output_rate'],
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->back()->with('success', 'Model AI berhasil ditambahkan.');
    }

    public function update(Request $request, AiModel $model)
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:ai_providers,id',
            'model_key' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'input_rate' => 'required|numeric|min:0',
            'output_rate' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $model->update([
            'provider_id' => $validated['provider_id'],
            'model_key' => $validated['model_key'],
            'display_name' => $validated['display_name'] ?? null,
            'input_rate' => $validated['input_rate'],
            'output_rate' => $validated['output_rate'],
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->back()->with('success', 'Model AI berhasil diperbarui.');
    }

    public function destroy(AiModel $model)
    {
        $model->delete();

        return redirect()->back()->with('success', 'Model AI berhasil dihapus.');
    }
}
