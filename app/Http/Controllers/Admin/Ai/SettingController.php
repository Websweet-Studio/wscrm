<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Services\AiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        $settings = AiClient::settings();

        // Jangan bocorkan api_key ke frontend — cukup status "sudah diisi".
        $masked = [
            'endpoint' => $settings['endpoint'],
            'api_key' => $settings['api_key'] !== '' ? '********' : '',
            'api_key_set' => $settings['api_key'] !== '',
            'model' => $settings['model'],
        ];

        return Inertia::render('Admin/Ai/Settings/Index', [
            'settings' => $masked,
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|url|max:255',
            'api_key' => 'nullable|string|max:500',
            'model' => 'required|string|max:255',
        ]);

        AiSetting::setValue('endpoint', rtrim($validated['endpoint'], '/'));
        AiSetting::setValue('model', $validated['model']);

        // api_key kosong / masih "********" → pertahankan yang tersimpan.
        if (! empty($validated['api_key']) && $validated['api_key'] !== '********') {
            AiSetting::setValue('api_key', Crypt::encryptString($validated['api_key']));
        }

        return redirect()->back()->with('success', 'Pengaturan AI tersimpan.');
    }
}
