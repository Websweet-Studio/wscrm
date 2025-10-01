<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BrandingController extends Controller
{
    public function index()
    {
        $settings = BrandingSetting::getAllActive()->groupBy('type');

        // Add timestamp to force fresh data
        return Inertia::render('Admin/Branding', [
            'settings' => $settings,
            'timestamp' => now()->timestamp,
        ]);
    }

    /**
     * Share branding settings with all views
     */
    public static function shareBrandingSettings()
    {
        $brandingSettings = [];
        $settings = BrandingSetting::getAllActive();

        foreach ($settings as $setting) {
            $brandingSettings[$setting->key] = $setting->value;
        }

        Inertia::share('brandingSettings', $brandingSettings);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable|string',
            'settings.*.type' => ['required', Rule::in(['text', 'textarea', 'color', 'image'])],
        ]);

        foreach ($validated['settings'] as $settingData) {
            $setting = BrandingSetting::where('key', $settingData['key'])->first();

            if ($setting) {
                if ($setting->type === 'image') {
                    // For image settings, only update if value is explicitly provided
                    // Don't overwrite existing image URLs with null
                    if ($settingData['value'] !== null) {
                        $setting->update([
                            'value' => $settingData['value'],
                        ]);
                    }
                } else {
                    // Update non-image settings normally
                    $setting->update([
                        'value' => $settingData['value'],
                    ]);
                }
            }
        }

        // Reload fresh data after update
        $settings = BrandingSetting::getAllActive()->groupBy('type');

        return Inertia::render('Admin/Branding', [
            'settings' => $settings,
            'timestamp' => now()->timestamp,
        ])->with('success', 'Pengaturan branding berhasil diperbarui.');
    }

    public function uploadImage(Request $request)
    {
        // Ensure JSON response for validation errors
        $request->headers->set('Accept', 'application/json');

        try {
            $validated = $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB
                'key' => 'required|string',
            ]);

            if ($request->hasFile('image')) {
                // Find existing setting to delete old image
                $existingSetting = BrandingSetting::where('key', $validated['key'])->first();
                if ($existingSetting && $existingSetting->value) {
                    // Delete old image file from storage
                    $oldImagePath = str_replace('/storage/', '', $existingSetting->value);
                    if (Storage::disk('public')->exists($oldImagePath)) {
                        Storage::disk('public')->delete($oldImagePath);
                    }
                }

                $image = $request->file('image');
                $filename = time().'_'.$image->getClientOriginalName();
                $path = $image->storeAs('branding', $filename, 'public');

                // Update the setting with the new image path
                BrandingSetting::setValue($validated['key'], '/storage/'.$path);

                return response()->json([
                    'success' => true,
                    'path' => '/storage/'.$path,
                    'message' => 'Gambar berhasil diupload.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload gambar.',
            ], 400);
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

    public function deleteImage(Request $request)
    {
        // Ensure JSON response for validation errors
        $request->headers->set('Accept', 'application/json');

        try {
            $validated = $request->validate([
                'key' => 'required|string',
            ]);

            $setting = BrandingSetting::where('key', $validated['key'])->first();

            if ($setting && $setting->value) {
                // Delete the file from storage
                $imagePath = str_replace('/storage/', '', $setting->value);
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }

                // Clear the setting value
                $setting->update(['value' => null]);

                return response()->json([
                    'success' => true,
                    'message' => 'Gambar berhasil dihapus.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gambar tidak ditemukan.',
            ], 404);
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
}
