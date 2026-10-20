<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandingSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BrandingController extends Controller
{
    /** Ekstensi berkas yang boleh dipakai sebagai gambar branding. */
    private const MEDIA_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico', 'bmp', 'avif'];

    /**
     * Display branding settings page
     */
    public function index()
    {
        BrandingSetting::ensureDefaultsIfEmpty();
        $settings = BrandingSetting::getAllActive()->groupBy('type');

        return Inertia::render('Admin/Branding', [
            'settings' => $settings,
            'timestamp' => now()->timestamp,
        ]);
    }

    /**
     * Share branding settings with all views
     */
    public static function shareBrandingSettings(): void
    {
        $brandingSettings = BrandingSetting::getAllActive()
            ->pluck('value', 'key')
            ->toArray();

        Inertia::share('brandingSettings', $brandingSettings);
    }

    /**
     * Update non-image settings only
     * Image settings are handled separately via dedicated endpoints
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|exists:branding_settings,key',
            'settings.*.value' => 'nullable|string',
            'settings.*.type' => ['required', Rule::in(['text', 'textarea', 'color'])],
        ]);

        // Only update non-image settings
        collect($validated['settings'])->each(function (array $settingData): void {
            $value = $settingData['value'];

            BrandingSetting::where('key', $settingData['key'])
                ->where('type', '!=', 'image')
                ->update([
                    'value' => $value,
                    'type' => $settingData['type'],
                ]);
        });

        // Reload fresh data after update
        $settings = BrandingSetting::getAllActive()->groupBy('type');

        // Refresh Inertia shared props with updated branding settings
        self::shareBrandingSettings();

        return Inertia::render('Admin/Branding', [
            'settings' => $settings,
            'timestamp' => now()->timestamp,
        ])->with('success', 'Pengaturan branding berhasil diperbarui.');
    }

    /**
     * Upload image and update branding setting
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->headers->set('Accept', 'application/json');

        try {
            $validated = $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'key' => 'required|string|exists:branding_settings,key',
            ]);

            $setting = BrandingSetting::where('key', $validated['key'])
                ->where('type', 'image')
                ->firstOrFail();

            // Delete old image if exists
            $this->deleteImageFile($setting->value, $setting->key);

            // Upload new image
            $path = $validated['image']->store('branding', 'public');
            $publicPath = Storage::url($path);

            // Update setting
            $setting->update(['value' => $publicPath]);

            // Refresh Inertia shared props with updated branding settings
            self::shareBrandingSettings();

            return response()->json([
                'success' => true,
                'path' => $publicPath,
                'message' => 'Gambar berhasil diupload.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete image and clear branding setting
     */
    public function deleteImage(Request $request): JsonResponse
    {
        $request->headers->set('Accept', 'application/json');

        try {
            $validated = $request->validate([
                'key' => 'required|string|exists:branding_settings,key',
            ]);

            $setting = BrandingSetting::where('key', $validated['key'])
                ->where('type', 'image')
                ->firstOrFail();

            // Delete file
            $this->deleteImageFile($setting->value, $setting->key);

            // Clear setting
            $setting->update(['value' => null]);

            // Refresh Inertia shared props with updated branding settings
            self::shareBrandingSettings();

            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil dihapus.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Daftar gambar yang pernah diunggah (storage/app/public) untuk dipilih
     * sebagai logo/favicon tanpa mengunggah ulang.
     */
    public function media(Request $request): JsonResponse
    {
        $request->headers->set('Accept', 'application/json');

        $search = trim((string) $request->query('q', ''));
        $disk = Storage::disk('public');

        // Peta gambar -> kolom branding mana saja yang memakainya.
        // Satu gambar boleh dipakai di beberapa kolom sekaligus.
        $usedBy = [];

        foreach (BrandingSetting::where('type', 'image')->get(['key', 'value']) as $setting) {
            $relative = $this->relativePath($setting->value);

            if ($relative === '') {
                continue;
            }

            $usedBy[$relative][] = $setting->key;
        }

        $items = [];

        foreach ($disk->allFiles() as $path) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if (! in_array($extension, self::MEDIA_EXTENSIONS, true)) {
                continue;
            }

            if ($search !== '' && stripos($path, $search) === false) {
                continue;
            }

            $items[] = [
                'path' => $path,
                'url' => $disk->url($path),
                'name' => basename($path),
                'folder' => dirname($path) === '.' ? '' : dirname($path),
                'size' => (int) $disk->size($path),
                'modified' => (int) $disk->lastModified($path),
                'used_by' => $usedBy[$path] ?? [],
            ];
        }

        usort($items, fn (array $a, array $b) => $b['modified'] <=> $a['modified']);

        $total = count($items);

        return response()->json([
            'success' => true,
            'media' => array_slice($items, 0, 300),
            'total' => $total,
        ]);
    }

    /**
     * Pakai gambar yang sudah ada di galeri media sebagai gambar branding.
     */
    public function selectImage(Request $request): JsonResponse
    {
        $request->headers->set('Accept', 'application/json');

        try {
            $validated = $request->validate([
                'key' => 'required|string|exists:branding_settings,key',
                'path' => 'required|string|max:500',
            ]);

            $setting = BrandingSetting::where('key', $validated['key'])
                ->where('type', 'image')
                ->firstOrFail();

            $path = $this->relativePath($validated['path']);

            if ($path === '' || str_contains($path, '..')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Path gambar tidak valid.',
                ], 422);
            }

            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (! in_array($extension, self::MEDIA_EXTENSIONS, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Berkas tersebut bukan gambar yang didukung.',
                ], 422);
            }

            $disk = Storage::disk('public');
            if (! $disk->exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gambar tidak ditemukan di galeri media.',
                ], 404);
            }

            $previous = $setting->value;
            $publicPath = $disk->url($path);

            $setting->update(['value' => $publicPath]);

            if ($previous && $previous !== $publicPath) {
                $this->deleteImageFile($previous, $setting->key);
            }

            self::shareBrandingSettings();

            return response()->json([
                'success' => true,
                'path' => $publicPath,
                'name' => basename($path),
                'message' => 'Gambar dari galeri berhasil dipakai.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Normalisasi path media menjadi path relatif disk 'public'.
     */
    private function relativePath(?string $path): string
    {
        $path = ltrim(str_replace('\\', '/', (string) $path), '/');

        return (string) preg_replace('#^storage/#', '', $path);
    }

    /**
     * Helper method to delete image file from storage
     *
     * Hanya berkas hasil upload branding (folder branding/) yang dihapus fisik.
     * Gambar yang dipakai bersama galeri media lain (blog, demo, atau setting
     * branding lain) dibiarkan agar tidak memutus halaman lain.
     */
    private function deleteImageFile(?string $imagePath, ?string $exceptKey = null): void
    {
        if (empty($imagePath)) {
            return;
        }

        $relativePath = $this->relativePath($imagePath);

        if ($relativePath === '' || str_contains($relativePath, '..')) {
            return;
        }

        if (! str_starts_with($relativePath, 'branding/')) {
            return;
        }

        $stillUsed = BrandingSetting::where('type', 'image')
            ->when($exceptKey, fn ($query) => $query->where('key', '!=', $exceptKey))
            ->where(function ($query) use ($imagePath, $relativePath) {
                $query->where('value', $imagePath)
                    ->orWhere('value', '/storage/'.$relativePath)
                    ->orWhere('value', $relativePath);
            })
            ->exists();

        if ($stillUsed) {
            return;
        }

        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}
