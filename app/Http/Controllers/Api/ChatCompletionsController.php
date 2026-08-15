<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiCredit;
use App\Models\AiModel;
use App\Services\AiGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Endpoint OpenAI-compatible utk customer (Hermes agent, code editor, dsb).
 * Auth: Authorization: Bearer <api_key customer>.
 */
class ChatCompletionsController extends Controller
{
    public function chat(Request $request, AiGateway $gateway): JsonResponse|StreamedResponse
    {
        $credit = $this->authenticate($request);

        if (! $credit) {
            return $this->error('Invalid API key', 'invalid_api_key', 401);
        }

        $customerId = $credit->customer_id;

        $payload = $request->all();
        $messages = $this->normalizeMessages($payload['messages'] ?? null);

        if ($messages === null) {
            return $this->error('messages tidak boleh kosong. Kirim setidaknya satu pesan berisi role dan content.', 'invalid_request_error', 400);
        }

        $modelKey = $payload['model'] ?? null;

        if ($modelKey && ! AiModel::where('model_key', $modelKey)->where('is_active', true)->exists()) {
            return $this->error("Model '{$modelKey}' tidak ditemukan.", 'model_not_found', 404);
        }

        $temperature = (float) ($payload['temperature'] ?? 0.3);
        $maxTokens = (int) ($payload['max_tokens'] ?? 2000);

        // Batasi parameter untuk mencegah abuse biaya provider.
        $temperature = min(2.0, max(0.0, $temperature));
        $maxTokens = min(4096, max(1, $maxTokens));

        try {
            $result = $gateway->chat($customerId, $modelKey, $messages, $temperature, $maxTokens);
        } catch (\RuntimeException $e) {
            $message = $e->getMessage();

            // Saldo habis → kode 429 seperti OpenAI (insufficient_quota).
            if (str_contains($message, 'Saldo AI tidak mencukupi')) {
                return $this->error('Saldo kredit AI Anda tidak mencukupi. Silakan beli paket kredit di portal customer.', 'insufficient_quota', 429);
            }

            return $this->error($message, 'server_error', 502);
        }

        $completion = $this->buildCompletion($result);

        // Trae / code editor mengirim stream=true dan mengharapkan SSE.
        if (! empty($payload['stream'])) {
            return $this->streamCompletion($completion);
        }

        return response()->json($completion);
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

    /**
     * Normalisasi messages: terima content string ATAU array bagian (format Claude Code/Trae).
     * Kembalikan null bila format tak dikenal atau kosong.
     */
    private function normalizeMessages(mixed $raw): ?array
    {
        if (! is_array($raw) || count($raw) === 0) {
            return null;
        }

        $out = [];

        foreach ($raw as $m) {
            if (! is_array($m) || ! isset($m['role'], $m['content']) || ! in_array($m['role'], ['system', 'user', 'assistant'], true)) {
                return null;
            }

            $content = $m['content'];

            if (is_string($content)) {
                $out[] = ['role' => $m['role'], 'content' => $content];
                continue;
            }

            // Array bagian teks: [{"type":"text","text":"..."}] — gabungkan jadi string.
            if (is_array($content)) {
                $text = '';
                foreach ($content as $part) {
                    if (is_string($part)) {
                        $text .= $part;
                    } elseif (is_array($part) && isset($part['text']) && is_string($part['text'])) {
                        $text .= $part['text'];
                    }
                }
                if ($text === '') {
                    return null;
                }
                $out[] = ['role' => $m['role'], 'content' => $text];
                continue;
            }

            return null;
        }

        return $out;
    }

    private function buildCompletion(array $result): array
    {
        return [
            'id' => 'chatcmpl-'.bin2hex(random_bytes(8)),
            'object' => 'chat.completion',
            'created' => now()->timestamp,
            'model' => $result['model_key'],
            'provider' => $result['provider_name'],
            'choices' => [[
                'index' => 0,
                'message' => ['role' => 'assistant', 'content' => $result['content']],
                'finish_reason' => 'stop',
            ]],
            'usage' => [
                'prompt_tokens' => $result['usage']['prompt_tokens'],
                'completion_tokens' => $result['usage']['completion_tokens'],
                'total_tokens' => $result['usage']['prompt_tokens'] + $result['usage']['completion_tokens'],
            ],
            'credits_used' => $result['credits_used'],
            'balance_after' => $result['balance_after'],
        ];
    }

    /**
     * Emulasikan SSE: provider kita bersifat blocking, jadi kirim satu chunk lengkap
     * lalu [DONE]. Cukup utk klien OpenAI-compatible (Trae/Claude Code, dsb).
     * ponytail: ganti jadi streaming nyata bila provider utama sudah mendukung SSE.
     */
    private function streamCompletion(array $completion): StreamedResponse
    {
        $chunk = $completion;
        $chunk['object'] = 'chat.completion.chunk';
        unset($chunk['choices'][0]['message']);
        $chunk['choices'][0]['delta'] = ['role' => 'assistant', 'content' => $completion['choices'][0]['message']['content']];

        return response()->stream(function () use ($chunk) {
            echo 'data: '.json_encode($chunk)."\n\n";
            echo "data: [DONE]\n\n";
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
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
