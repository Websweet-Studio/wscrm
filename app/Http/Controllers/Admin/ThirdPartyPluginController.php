<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThirdPartyPlugin;
use App\Models\WebsiteClient;
use App\Services\PluginDeployService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ThirdPartyPluginController extends Controller
{
    private function checkAdmin(): void
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function index(): Response
    {
        $this->checkAdmin();

        $plugins = ThirdPartyPlugin::orderBy('name')->get();
        $websites = WebsiteClient::orderBy('name')->get(['id', 'name', 'url']);

        return Inertia::render('Admin/Websites/Plugins', [
            'plugins' => $plugins,
            'websites' => $websites,
        ]);
    }

    public function store(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:100', 'unique:third_party_plugins'],
            'version' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'file' => ['required', 'file', 'mimes:zip', 'max:51200'],
        ]);

        $slug = $validated['slug'];
        $filePath = $request->file('file')->storeAs('plugins', $slug . '.zip', 'public');

        ThirdPartyPlugin::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'version' => $validated['version'] ?? null,
            'description' => $validated['description'] ?? null,
            'file_path' => $filePath,
            'file_name' => $request->file('file')->getClientOriginalName(),
            'file_size' => $request->file('file')->getSize(),
        ]);

        return redirect()->route('admin.websites.plugins')
            ->with('success', "Plugin '{$validated['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, ThirdPartyPlugin $plugin)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:100', 'unique:third_party_plugins,slug,' . $plugin->id],
            'version' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:zip', 'max:51200'],
        ]);

        $data = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'version' => $validated['version'] ?? null,
            'description' => $validated['description'] ?? null,
        ];

        if ($request->hasFile('file')) {
            if ($plugin->file_path) {
                Storage::disk('public')->delete($plugin->file_path);
            }

            $filePath = $request->file('file')->storeAs('plugins', $validated['slug'] . '.zip', 'public');

            $data['file_path'] = $filePath;
            $data['file_name'] = $request->file('file')->getClientOriginalName();
            $data['file_size'] = $request->file('file')->getSize();
        }

        $plugin->update($data);

        return redirect()->route('admin.websites.plugins')
            ->with('success', "Plugin '{$validated['name']}' berhasil diperbarui.");
    }

    public function destroy(ThirdPartyPlugin $plugin)
    {
        $this->checkAdmin();

        if ($plugin->file_path) {
            Storage::disk('public')->delete($plugin->file_path);
        }
        $plugin->delete();

        return redirect()->route('admin.websites.plugins')
            ->with('success', "Plugin '{$plugin->name}' berhasil dihapus.");
    }

    public function deploy(Request $request, ThirdPartyPlugin $plugin, PluginDeployService $deployService): JsonResponse
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'website_id' => ['required', 'exists:website_clients,id'],
        ]);

        $website = WebsiteClient::find($validated['website_id']);
        $result = $deployService->deploy($plugin, $website);

        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
