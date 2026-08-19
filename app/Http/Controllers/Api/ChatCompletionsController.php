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
        // Batas ukuran payload total (cegah messages/tools/response_format jumbo).
        if (mb_strlen((string) $request->getContent()) > 1_048_576) {
            return $this->error('Payload terlalu besar. Maksimal 1 MB.', 'invalid_request_error', 400);
        }

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

        // Teruskan parameter OpenAI tambahan agar function calling (Trae/Claude Code) jalan.
        $options = $this->extractOptions($payload);

        // Streaming (Trae/Claude Code): langsung stream dari provider. Jangan panggil
        // chat() non-streaming dulu — itu memicu 2x generasi penuh + 2x biaya token.
        if (! empty($payload['stream'])) {
            return $this->streamCompletion($gateway, $customerId, $modelKey, $messages, $temperature, $maxTokens, $options);
        }

        try {
            $result = $gateway->chat($customerId, $modelKey, $messages, $temperature, $maxTokens, $options);
        } catch (\RuntimeException $e) {
            $message = $e->getMessage();

            // Saldo habis → kode 429 seperti OpenAI (insufficient_quota).
            if (str_contains($message, 'Saldo AI tidak mencukupi')) {
                return $this->error('Saldo kredit AI Anda tidak mencukupi. Silakan beli paket kredit di portal customer.', 'insufficient_quota', 429);
            }

            // Jangan bocorkan detail internal provider/gateway ke client; catat di log server.
            \Illuminate\Support\Facades\Log::warning('Chat completions gagal: '.$message);

            return $this->error('Terjadi kesalahan saat memproses request. Coba lagi nanti.', 'server_error', 502);
        }

        $completion = $this->buildCompletion($result);

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
            'data' => $models->map(fn($m) => [
                'id' => $m->model_key,
                'object' => 'model',
                'created' => 0,
                'owned_by' => 'wscrm',
            ])->values(),
        ]);
    }

    /**
     * Normalisasi messages: terima content string ATAU array bagian (format Claude Code/Trae),
     * role `tool` + `tool_calls`/`tool_call_id` (function calling).
     * Kembalikan null bila format tak dikenal atau kosong.
     */
    private function normalizeMessages(mixed $raw): ?array
    {
        if (! is_array($raw) || count($raw) === 0) {
            return null;
        }

        // Cap jumlah pesan & panjang konten (cegah input-token jumbo / memori).
        if (count($raw) > 100) {
            return null;
        }

        $out = [];

        foreach ($raw as $m) {
            if (! is_array($m) || ! isset($m['role']) || ! in_array($m['role'], ['system', 'user', 'assistant', 'tool'], true)) {
                return null;
            }

            $role = $m['role'];

            // Role `tool` wajib punya tool_call_id (hasil eksekusi tool dari Trae/Claude Code).
            if ($role === 'tool') {
                if (! isset($m['tool_call_id']) || ! is_string($m['tool_call_id'])) {
                    return null;
                }

                $out[] = [
                    'role' => 'tool',
                    'tool_call_id' => $m['tool_call_id'],
                    'content' => is_string($m['content'] ?? '') ? $m['content'] : json_encode($m['content'] ?? ''),
                ];
                continue;
            }

            if (! isset($m['content'])) {
                $m['content'] = null;
            }

            $content = $m['content'];
            $normalizedContent = '';

            if (is_string($content)) {
                $normalizedContent = $content;
            } elseif (is_array($content)) {
                // Array bagian teks: [{"type":"text","text":"..."}] — gabungkan jadi string.
                $text = '';
                foreach ($content as $part) {
                    if (is_string($part)) {
                        $text .= $part;
                    } elseif (is_array($part) && isset($part['text']) && is_string($part['text'])) {
                        $text .= $part['text'];
                    }
                }
                $normalizedContent = $text;
            } elseif ($content === null) {
                // Assistant yang memanggil tool sering mengirim content null/'' —
                // jangan ditolak, cukup kosongkan.
                $normalizedContent = '';
            } else {
                return null;
            }

            // Batas panjang konten per pesan (~64KB karakter).
            if (mb_strlen($normalizedContent) > 65536) {
                return null;
            }

            $normalized = ['role' => $role, 'content' => $normalizedContent];

            // Pertahankan tool_calls pada pesan assistant (penting utk function calling).
            if ($role === 'assistant' && isset($m['tool_calls']) && is_array($m['tool_calls'])) {
                $normalized['tool_calls'] = $this->normalizeToolCalls($m['tool_calls']);
            }

            $out[] = $normalized;
        }

        return $out;
    }

    /**
     * Normalisasi array tool_calls (pastikan id & function.arguments valid string).
     */
    private function normalizeToolCalls(array $toolCalls): array
    {
        $out = [];

        foreach ($toolCalls as $tc) {
            if (! is_array($tc) || ! isset($tc['id'])) {
                continue;
            }

            $function = $tc['function'] ?? [];
            $arguments = $function['arguments'] ?? '';

            if (is_array($arguments)) {
                $arguments = json_encode($arguments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $out[] = [
                'id' => (string) $tc['id'],
                'type' => $tc['type'] ?? 'function',
                'function' => [
                    'name' => (string) ($function['name'] ?? ''),
                    'arguments' => (string) $arguments,
                ],
            ];
        }

        return $out;
    }

    /**
     * Ambil parameter OpenAI tambahan (tools, tool_choice, response_format, dll)
     * dari payload agar gateway bersifat transparan (function calling).
     */
    private function extractOptions(array $payload): array
    {
        $options = [];

        foreach (['tools', 'tool_choice', 'response_format', 'top_p', 'frequency_penalty', 'presence_penalty', 'stop', 'seed'] as $key) {
            if (array_key_exists($key, $payload) && $payload[$key] !== null) {
                if ($key === 'tools' && is_array($payload['tools'])) {
                    // Cap jumlah tools (cek skema function-calling jumbo).
                    $options['tools'] = array_slice($payload['tools'], 0, 32);
                } else {
                    $options[$key] = $payload[$key];
                }
            }
        }

        return $options;
    }

    private function buildCompletion(array $result): array
    {
        $message = ['role' => 'assistant', 'content' => $result['content'] ?? ''];

        if (! empty($result['tool_calls'])) {
            $message['tool_calls'] = $result['tool_calls'];
        }

        $finishReason = $result['finish_reason'] ?? 'stop';
        if ($finishReason === 'tool_calls' && ! empty($result['tool_calls'])) {
            // Beberapa gateway memakai finish_reason yang berbeda; jaga konsistensi.
            $finishReason = 'tool_calls';
        }

        return [
            'id' => 'chatcmpl-' . bin2hex(random_bytes(8)),
            'object' => 'chat.completion',
            'created' => now()->timestamp,
            'model' => $result['model_key'],
            'provider' => $result['provider_name'],
            'choices' => [[
                'index' => 0,
                'message' => $message,
                'finish_reason' => $finishReason,
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
     * Streaming SSE nyata: pipa tiap chunk token dari provider (AiGateway/streamChat)
     * langsung ke client OpenAI-compatible, lalu kirim chunk final berisi usage
     * + sisa kredit, dan `data: [DONE]`.
     */
    private function streamCompletion(AiGateway $gateway, int $customerId, ?string $modelKey, array $messages, float $temperature, int $maxTokens, array $options): StreamedResponse
    {
        return response()->stream(function () use ($gateway, $customerId, $modelKey, $messages, $temperature, $maxTokens, $options) {
            $onChunk = function (array $raw) {
                $raw['object'] = 'chat.completion.chunk';
                echo 'data: '.json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n\n";
                @ob_flush();
                flush();
            };

            try {
                $result = $gateway->streamChat($customerId, $modelKey, $messages, $temperature, $maxTokens, $options, $onChunk);

                // Chunk final: usage + sisa kredit (trailing chunk ala OpenAI).
                $final = [
                    'id' => 'chatcmpl-'.bin2hex(random_bytes(8)),
                    'object' => 'chat.completion.chunk',
                    'created' => now()->timestamp,
                    'model' => $result['model_key'],
                    'provider' => $result['provider_name'],
                    'choices' => [[
                        'index' => 0,
                        'delta' => [],
                        'finish_reason' => $result['finish_reason'],
                    ]],
                    'usage' => [
                        'prompt_tokens' => $result['usage']['prompt_tokens'],
                        'completion_tokens' => $result['usage']['completion_tokens'],
                        'total_tokens' => $result['usage']['prompt_tokens'] + $result['usage']['completion_tokens'],
                    ],
                    'credits_used' => $result['credits_used'],
                    'balance_after' => $result['balance_after'],
                ];
                echo 'data: '.json_encode($final, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n\n";
            } catch (\RuntimeException $e) {
                $message = $e->getMessage();
                $type = 'server_error';
                $safeMessage = 'Terjadi kesalahan saat memproses request. Coba lagi nanti.';

                if (str_contains($message, 'Saldo AI tidak mencukupi')) {
                    $type = 'insufficient_quota';
                    $safeMessage = 'Saldo kredit AI Anda tidak mencukupi. Silakan beli paket kredit di portal customer.';
                } else {
                    \Illuminate\Support\Facades\Log::warning('Chat completions stream gagal: '.$message);
                }

                echo 'data: '.json_encode(['error' => ['message' => $safeMessage, 'type' => $type]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n\n";
            }

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
