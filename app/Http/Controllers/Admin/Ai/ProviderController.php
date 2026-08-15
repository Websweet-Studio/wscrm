<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiProvider;
use App\Services\AiGateway;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProviderController extends Controller
{
    public function index(): Response
    {
        $providers = AiProvider::query()
            ->withCount('models')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        // Jangan bocorkan api_key (terenkripsi) ke frontend — cukup status "sudah diisi".
        $providers->getCollection()->transform(fn ($provider) => $provider
            ->setAttribute('api_key_set', ! empty($provider->api_key))
            ->setAttribute('health', AiGateway::health($provider->id))
            ->makeHidden('api_key'));

        return Inertia::render('Admin/Ai/Providers/Index', [
            'providers' => $providers,
            'filters' => request()->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'endpoint' => 'required|url|max:255',
            'api_key' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        AiProvider::create([
            'name' => $validated['name'],
            'endpoint' => rtrim($validated['endpoint'], '/'),
            'api_key' => ! empty($validated['api_key']) ? Crypt::encryptString($validated['api_key']) : null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->back()->with('success', 'Provider AI berhasil ditambahkan.');
    }

    public function update(Request $request, AiProvider $provider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'endpoint' => 'required|url|max:255',
            'api_key' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $provider->update([
            'name' => $validated['name'],
            'endpoint' => rtrim($validated['endpoint'], '/'),
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        // Kolom api_key dikosongkan di form → pertahankan key lama.
        if (! empty($validated['api_key'])) {
            $provider->update(['api_key' => Crypt::encryptString($validated['api_key'])]);
        }

        return redirect()->back()->with('success', 'Provider AI berhasil diperbarui.');
    }

    public function destroy(AiProvider $provider)
    {
        $provider->delete();

        return redirect()->back()->with('success', 'Provider AI berhasil dihapus.');
    }
}
