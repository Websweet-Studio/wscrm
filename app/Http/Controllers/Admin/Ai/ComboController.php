<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiCombo;
use App\Models\AiModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComboController extends Controller
{
    public function index(): Response
    {
        $combos = AiCombo::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->with(['models' => function ($q) {
                $q->with('provider:id,name')->orderByPivot('priority');
            }])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        $models = AiModel::with('provider:id,name')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'model_key', 'display_name', 'provider_id']);

        return Inertia::render('Admin/Ai/Combos/Index', [
            'combos' => $combos,
            'models' => $models,
            'filters' => request()->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'models' => 'required|array|min:1',
            'models.*.ai_model_id' => 'required|exists:ai_models,id',
            'models.*.priority' => 'required|integer|min:0',
        ]);

        $combo = AiCombo::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        foreach ($validated['models'] as $item) {
            $combo->comboModels()->create([
                'ai_model_id' => $item['ai_model_id'],
                'priority' => $item['priority'],
            ]);
        }

        return redirect()->back()->with('success', 'Combo berhasil ditambahkan.');
    }

    public function update(Request $request, AiCombo $combo): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'models' => 'required|array|min:1',
            'models.*.ai_model_id' => 'required|exists:ai_models,id',
            'models.*.priority' => 'required|integer|min:0',
        ]);

        $combo->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        // Sync models: hapus lama, buat baru.
        $combo->comboModels()->delete();
        foreach ($validated['models'] as $item) {
            $combo->comboModels()->create([
                'ai_model_id' => $item['ai_model_id'],
                'priority' => $item['priority'],
            ]);
        }

        return redirect()->back()->with('success', 'Combo berhasil diperbarui.');
    }

    public function destroy(AiCombo $combo): RedirectResponse
    {
        $combo->delete();

        return redirect()->back()->with('success', 'Combo berhasil dihapus.');
    }
}
