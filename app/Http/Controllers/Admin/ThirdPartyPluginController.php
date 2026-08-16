<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThirdPartyPlugin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ThirdPartyPluginController extends Controller
{
    public function index(): Response
    {
        $plugins = ThirdPartyPlugin::orderBy('name')->get();

        return Inertia::render('Admin/Websites/Plugins', [
            'plugins' => $plugins,
        ]);
    }

    /**
     * Endpoint publik untuk theme wsbase (WP side) menarik metadata plugin.
     * URL: GET /api/plugins
     */
    public function publicIndex()
    {
        $plugins = ThirdPartyPlugin::where('is_active', true)->orderBy('name')->get();

        return response()->json([
            'plugins' => $plugins->map(function (ThirdPartyPlugin $plugin) {
                return [
                    'name' => $plugin->name,
                    'slug' => $plugin->slug,
                    'version' => $plugin->version,
                    'description' => $plugin->description,
                    'file_name' => $plugin->file_name,
                    'file_size' => $plugin->file_size,
                    'file_url' => $plugin->file_path ? Storage::disk('public')->url($plugin->file_path) : null,
                    'updated_at' => $plugin->updated_at?->toIso8601String(),
                ];
            })->values(),
        ]);
    }

    public function store(Request $request)
    {
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
        if ($plugin->file_path) {
            Storage::disk('public')->delete($plugin->file_path);
        }
        $plugin->delete();

        return redirect()->route('admin.websites.plugins')
            ->with('success', "Plugin '{$plugin->name}' berhasil dihapus.");
    }
}
