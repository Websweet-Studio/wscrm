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

        return Inertia::render('Admin/Branding', [
            'settings' => $settings,
        ]);
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
                $setting->update([
                    'value' => $settingData['value'],
                ]);
            }
        }

        return redirect()->back()->with('success', 'Pengaturan branding berhasil diperbarui.');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB
            'key' => 'required|string',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time().'_'.$image->getClientOriginalName();
            $path = $image->storeAs('branding', $filename, 'public');

            // Update the setting with the new image path
            BrandingSetting::setValue($request->key, '/storage/'.$path);

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
    }

    public function deleteImage(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
        ]);

        $setting = BrandingSetting::where('key', $request->key)->first();

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
    }
}
