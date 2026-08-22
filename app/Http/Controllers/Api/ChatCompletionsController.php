<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiCredit;
use App\Models\AiModel;
use App\Models\AiProvider;
use App\Models\AiTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Endpoint OpenAI-compatible utk customer (Hermes agent, code editor, dsb).
 * Auth: Authorization: Bearer <api_key customer>.
 *
 * RINGAN: hanya autentikasi + limit saldo + catat pemakaian token. Request
 * diteruskan (passthrough) mentah ke gateway AI (9router → provider), sehingga
 * stream/format respons apa adanya dari upstream — bukan di-remap di sini.
 */
class ChatCompletionsController extends Controller
{
    public function chat(Request $request): JsonResponse|StreamedResponse
    {
        if (mb_strlen((string) $request->getContent()) > 1_048_576) {
            return $this->error('Payload terlalu besar. Maksimal 1 MB.', 'invalid_request_error', 400);
        }

        $credit = $this->authenticate($request);

        if (! $credit) {
            return $this->error('Invalid API key', 'invalid_api_key', 401);
        }

        $payload = $request->all();

        if (empty($payload['messages']) || ! is_array($payload['messages'])) {
            return $this->error('messages tidak boleh kosong. Kirim setidaknya satu pesan berisi role dan content.', 'invalid_request_error', 400);
        }

        // Resolusi model aktif (untuk rate/billing). Tanpa model → pakai yang pertama aktif.
        $modelKey = $payload['model'] ?? null;
        $model = $modelKey
            ? AiModel::where('model_key', $modelKey)->where('is_active', true)->first()
            : AiModel::where('is_active', true)->orderBy('sort_order')->first();

        if ($modelKey && ! $model) {
            return $this->error("Model '{$modelKey}' tidak ditemukan.", 'model_not_found', 404);
        }

        // Pre-check saldo (limit) — kunci baris biar anti race, dilepas sebelum stream panjang.
        $credit = AiCredit::where('customer_id', $credit->customer_id)->lockForUpdate()->first();

        if (! $credit || $credit->balance < 1) {
            return $this->error('Saldo kredit AI Anda tidak mencukupi. Silakan beli paket kredit di portal customer.', 'insufficient_quota', 429);
        }

        // Resolve upstream gateway AI (provider aktif → 9router).
        $provider = AiProvider::where('is_active', true)->orderBy('sort_order')->first();
        $upstream = rtrim($provider->endpoint, '/') . '/chat/completions';
        $apiKey = $provider->api_key
            ? Crypt::decryptString($provider->api_key)
            : (string) config('services.ai.api_key', env('AI_API_KEY', ''));

        return ! empty($payload['stream'])
            ? $this->proxyStream($upstream, $apiKey, $credit, $model, $payload)
            : $this->proxyJson($upstream, $apiKey, $credit, $model, $payload);
    }

    /**
     * Passthrough non-stream: forward ke upstream, kembalikan JSON apa adanya,
     * catat usage dari response lalu potong saldo.
     */
    private function proxyJson(string $upstream, string $apiKey, AiCredit $credit, ?AiModel $model, array $payload): JsonResponse
    {
        try {
            $resp = Http::withToken($apiKey)
                ->withHeaders(['Accept' => 'application/json'])
                ->timeout(300)
                ->post($upstream, $payload);
        } catch (\Throwable $e) {
            Log::warning('AI proxy non-stream gagal: ' . $e->getMessage());

            return $this->error('Terjadi kesalahan saat memproses request. Coba lagi nanti.', 'server_error', 502);
        }

        $body = $resp->json() ?? [];

        $this->billFromUsage($credit, $model, $body['choices'][0]['finish_reason'] ?? '', $body['usage'] ?? null);

        return response()->json($body, $resp->status(), [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Passthrough stream: pipa SSE dari upstream verbatim ke client, tangkap
     * usage dari trailing chunk untuk billing, potong saldo di akhir.
     */
    private function proxyStream(string $upstream, string $apiKey, AiCredit $credit, ?AiModel $model, array $payload): StreamedResponse
    {
        return response()->stream(function () use ($upstream, $apiKey, $credit, $model, $payload) {
            $usage = null;
            $finish = '';

            // Stream via curl: kontrol header & baca SSE line-by-line dapat diandalkan.
            $ch = curl_init($upstream);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_HEADER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 600,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: text/event-stream',
                    'Authorization: Bearer ' . $apiKey,
                    'X-Accel-Buffering: no',
                ],
                CURLOPT_WRITEFUNCTION => function ($ch, $data) use (&$usage, &$finish) {
                    // Forward verbatim ke client.
                    echo $data;
                    @ob_flush();
                    flush();

                    // Tangkap usage + finish_reason dari chunk SSE untuk billing.
                    foreach (explode("\n", $data) as $line) {
                        if (str_starts_with(trim($line), 'data:') && ! str_contains($line, '[DONE]')) {
                            $json = json_decode(trim(substr($line, 5)), true);
                            if (is_array($json)) {
                                if (! empty($json['usage']['prompt_tokens'])) {
                                    $usage = $json['usage'];
                                }
                                $fr = $json['choices'][0]['finish_reason'] ?? '';
                                if ($fr !== '') {
                                    $finish = $fr;
                                }
                            }
                        }
                    }

                    return strlen($data);
                },
            ]);

            $ok = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if (! $ok && $usage === null) {
                Log::warning('AI proxy stream error: ' . $err);
            }

            // Billing berdasarkan usage yang tertangkap dari stream.
            $this->billFromUsage($credit, $model, $finish, $usage);

            if (! $ok && $usage === null) {
                echo 'data: ' . json_encode(['error' => ['message' => 'Terjadi kesalahan saat memproses request. Coba lagi nanti.', 'type' => 'server_error']]) . "\n\n";
            }

            // 9router mengakhiri stream tanpa `data: [DONE]`. Klien OpenAI-compatible
            // (Trae/agent) menunggu penanda selesai ini; tanpanya bisa hang/loop/-1.
            echo "data: [DONE]\n\n";
            @ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Hitung kredit dari usage (token) lalu potong saldo + catat transaksi.
     */
    private function billFromUsage(AiCredit $credit, ?AiModel $model, string $finish, ?array $usage): void
    {
        if (! $model) {
            return;
        }

        $input = (int) ($usage['prompt_tokens'] ?? 0);
        $output = (int) ($usage['completion_tokens'] ?? 0);

        // Fallback estimasi bila usage kosong (beberapa provider omit usage di stream).
        if ($input === 0 && $output === 0) {
            return; // tak bisa dihitung akurat; jangan tebak-hitam utk hindari charge keliru.
        }

        $credits = max(1, (int) round(
            ($input / 1_000_000) * (float) $model->input_rate
            + ($output / 1_000_000) * (float) $model->output_rate
        ));

        try {
            DB::transaction(function () use ($credit, $model, $input, $output, $credits) {
                $row = AiCredit::where('customer_id', $credit->customer_id)->lockForUpdate()->first();

                if (! $row || $row->balance < $credits) {
                    return;
                }

                AiTransaction::create([
                    'customer_id' => $credit->customer_id,
                    'type' => 'out',
                    'source' => 'usage',
                    'credits' => -$credits,
                    'ai_model_id' => $model->id,
                    'tokens_input' => $input,
                    'tokens_output' => $output,
                    'description' => "Chat AI pakai model {$model->model_key}",
                ]);

                AiTransaction::consumeFifo($credit->customer_id, $credits);
            });
        } catch (\Throwable $e) {
            Log::warning('AI proxy billing gagal: ' . $e->getMessage());
        }
    }

    /**
     * Daftar model aktif, format OpenAI /v1/models — dipakai Trae/editor utk discover model.
     */
    public function models(): JsonResponse
    {
        $models = AiModel::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['model_key', 'display_name']);

        return response()->json([
            'object' => 'list',
            'data' => $models->map(fn ($m) => [
                'id' => $m->model_key,
                'object' => 'model',
                'created' => 0,
                'owned_by' => 'wscrm',
            ])->values(),
        ]);
    }

    private function authenticate(Request $request): ?AiCredit
    {
        $header = $request->header('Authorization', '');

        if (! preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return null;
        }

        $token = trim($matches[1]);
        $hash = hash('sha256', $token);

        // Lookup O(1) via hash index; fallback ke dekripsi utk key lama tanpa hash.
        $credit = AiCredit::where('api_key_hash', $hash)->first();

        if ($credit) {
            return $credit;
        }

        foreach (AiCredit::whereNotNull('api_key')->whereNull('api_key_hash')->get() as $legacy) {
            try {
                if (hash_equals(Crypt::decryptString($legacy->api_key), $token)) {
                    // Backfill hash utk lookup cepat berikutnya.
                    $legacy->update(['api_key_hash' => $hash]);

                    return $legacy;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    private function error(string $message, string $type, int $status): JsonResponse
    {
        return response()->json([
            'error' => ['message' => $message, 'type' => $type],
        ], $status);
    }
}
