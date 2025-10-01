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
    /**
     * Display branding settings page
     */
    public function index()
    {
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
            BrandingSetting::where('key', $settingData['key'])
                ->where('type', '!=', 'image')
                ->update(['value' => $settingData['value']]);
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
            $this->deleteImageFile($setting->value);

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
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
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
            $this->deleteImageFile($setting->value);

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
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper method to delete image file from storage
     */
    private function deleteImageFile(?string $imagePath): void
    {
        if (empty($imagePath)) {
            return;
        }

        $relativePath = str_replace('/storage/', '', $imagePath);

        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}
