<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BrandingSetting;
use App\Models\DomainPrice;
use App\Models\HostingPlan;
use App\Models\ServicePlan;
use App\Services\AiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Chatbot AI untuk PENGUNJUNG publik (customer service) di halaman website.
 * Berbeda dari AiAgentController (admin): ini tidak memakai kredit, langsung
 * memakai AiClient (AI_ENDPOINT/AI_API_KEY/AI_MODEL dari .env), dan hanya
 * menjawab pertanyaan umum seputar layanan hosting/domain/jasa. Bila AI tidak
 * bisa menjawab, respons mengarahkan pengunjung ke WhatsApp perusahaan.
 */
class PublicAiChatController extends Controller
{
    public function chat(Request $request, AiClient $aiClient): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array|max:20',
            'history.*.role' => 'required|in:user,assistant',
            'history.*.content' => 'required|string|max:2000',
        ]);

        $whatsapp = $this->normalizeWhatsapp(
            BrandingSetting::getValue('company_whatsapp') ?: BrandingSetting::getValue('company_phone')
        );

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt($whatsapp)],
        ];

        foreach (($validated['history'] ?? []) as $h) {
            $messages[] = [
                'role' => $h['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => (string) $h['content'],
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $validated['message']];

        try {
            if (! $aiClient->hasApiKey()) {
                return response()->json([
                    'reply' => 'Layanan chat sedang tidak tersedia. Silakan hubungi kami via WhatsApp.',
                    'fallback_whatsapp' => $whatsapp,
                ]);
            }

            $content = $aiClient->chat($messages, 0.3, 600);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('PublicAiChat failed: ' . $e->getMessage());

            return response()->json([
                'reply' => 'Maaf, saya sedang kesulitan menjawab saat ini. Silakan hubungi kami via WhatsApp.',
                'fallback_whatsapp' => $whatsapp,
            ]);
        }

        // Deteksi apakah AI "mentok" / menyarankan kontak manusia.
        $needsHuman = $this->needsHuman($content);

        return response()->json([
            'reply' => $content,
            'fallback_whatsapp' => $needsHuman ? $whatsapp : null,
        ]);
    }

    private function systemPrompt(?string $whatsapp): string
    {
        $domains = DomainPrice::active()->orderBy('selling_price')->get(['extension', 'selling_price', 'renewal_price_with_tax']);
        $hosting = HostingPlan::active()->orderBy('selling_price')->get(['plan_name', 'service_type', 'storage_gb', 'cpu_cores', 'ram_gb', 'bandwidth', 'selling_price', 'features']);
        $services = ServicePlan::where('is_active', true)->orderBy('price')->get(['name', 'category', 'price', 'description']);

        $whatsappLine = $whatsapp
            ? "Nomor WhatsApp perusahaan untuk eskalasi: {$whatsapp}."
            : 'Nomor WhatsApp belum diatur.';

        $catalog = json_encode([
            'domain_prices' => $domains,
            'hosting_plans' => $hosting,
            'service_plans' => $services,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
Kamu adalah asisten Customer Service ramah untuk perusahaan jasa pembuatan website, hosting, dan domain (WebSweetStudio/WSCRM). Tugasmu membantu pengunjung website menjawab pertanyaan seputar layanan, harga, fitur, dan proses pemesanan.

## Katalog layanan (harga dalam Rupiah):
```json
{$catalog}
```

## Aturan:
- Jawab dalam bahasa Indonesia yang ramah, singkat, dan jelas.
- Gunakan data katalog di atas untuk menjawab pertanyaan harga/fitur. Jangan mengarang harga yang tidak ada di katalog.
- Jika pertanyaan di luar cakupan layanan (misal teknis akun internal, keluhan spesifik pelanggan, permintaan yang butuh keputusan manusia, atau kamu tidak tahu jawabannya), katakan dengan sopan bahwa kamu akan menghubungkan ke tim kami via WhatsApp, dan minta pengunjung menghubungi nomor tersebut.
- {$whatsappLine}
- Jangan menjanjikan diskon/harga spesial yang tidak ada di data. Jangan meminta data sensitif (password, kartu kredit).
PROMPT;
    }

    private function normalizeWhatsapp(mixed $raw): ?string
    {
        if (! $raw) {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string) $raw);

        return $digits !== '' ? $digits : null;
    }

    /**
     * Heuristik sederhana: AI dianggap "mentok" bila menyebut eskalasi/manusia/
     * WhatsApp/tidak tahu. Ini memicu frontend menampilkan tombol WhatsApp.
     */
    private function needsHuman(string $content): bool
    {
        $needle = ['whatsapp', 'hubungi', 'tim kami', 'customer service', 'cs kami', 'tidak tahu', 'tidak bisa', 'di luar', 'manusia', 'staf'];

        $lower = mb_strtolower($content);

        foreach ($needle as $n) {
            if (str_contains($lower, $n)) {
                return true;
            }
        }

        return false;
    }
}
