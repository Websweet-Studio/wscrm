<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiCredit;
use App\Models\AiModel;
use App\Services\AiGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * Endpoint OpenAI-compatible utk customer (Hermes agent, code editor, dsb).
 * Auth: Authorization: Bearer <api_key customer>.
 */
class ChatCompletionsController extends Controller
{
    public function chat(Request $request, AiGateway $gateway): JsonResponse
    {
        $credit = $this->authenticate($request);

        if (! $credit) {
            return $this->error('Invalid API key', 'invalid_api_key', 401);
        }

        $customerId = $credit->customer_id;

        $payload = $request->all();
        $messages = $payload['messages'] ?? null;

        if (! is_array($messages) || count($messages) === 0) {
            return $this->error('messages tidak boleh kosong', 'invalid_request_error', 400);
        }

        foreach ($messages as $m) {
            if (! isset($m['role'], $m['content']) || ! in_array($m['role'], ['system', 'user', 'assistant'], true) || ! is_string($m['content'])) {
                return $this->error('Format messages tidak valid. Tiap pesan butuh role (system/user/assistant) dan content.', 'invalid_request_error', 400);
            }
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

        return response()->json([
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
