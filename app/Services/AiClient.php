<?php

namespace App\Services;

use App\Models\AiProvider;
use App\Models\AiSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HTTP client untuk AI gateway (OpenAI-compatible chat completions).
 */
class AiClient
{
    private const MAX_ATTEMPTS = 3;

    private const RETRYABLE_STATUSES = [408, 409, 429, 500, 502, 503, 504];

    private string $endpoint;

    private string $apiKey;

    private string $model;

    public function __construct()
    {
        // Default dari config; forProvider() meng-override endpoint/model/api_key
        // dari DB provider. Hindari query DB di sini (settings() = 1 query + dekripsi
        // tiap request) karena path utama tak pernah memakai nilai ini.
        $this->endpoint = self::normalizeEndpoint((string) config('services.ai.endpoint', env('AI_ENDPOINT', 'https://api.openai.com/v1')));
        $this->apiKey = (string) config('services.ai.api_key', env('AI_API_KEY', ''));
        $this->model = (string) config('services.ai.model', env('AI_MODEL', 'gpt-4o-mini'));
    }

    /**
     * Baca pengaturan AI dari DB (admin bisa ubah via /admin/ai/settings).
     * Bila DB kosong, fallback ke config/services.ai lalu env.
     */
    public static function settings(): array
    {
        try {
            $db = AiSetting::allSettings();
        } catch (\Throwable) {
            $db = [];
        }

        $endpoint = $db['endpoint'] ?? '';
        if ($endpoint === '') {
            $endpoint = config('services.ai.endpoint', env('AI_ENDPOINT', 'https://api.openai.com/v1'));
        }

        $model = $db['model'] ?? '';
        if ($model === '') {
            $model = config('services.ai.model', env('AI_MODEL', 'gpt-4o-mini'));
        }

        $apiKey = '';
        if (! empty($db['api_key'])) {
            try {
                $apiKey = Crypt::decryptString($db['api_key']);
            } catch (\Throwable) {
                logger()->warning('AiClient: api_key di DB tidak bisa didekripsi (nilai lama/rusak). Atur ulang di /admin/ai/settings.');
            }
        } else {
            $apiKey = config('services.ai.api_key', env('AI_API_KEY', ''));
        }

        return [
            'endpoint' => rtrim((string) $endpoint, '/'),
            'api_key' => (string) $apiKey,
            'model' => (string) $model,
        ];
    }

    /**
     * Buat client untuk provider dari DB (multi-provider). Api key didekripsi.
     */
    public static function forProvider(AiProvider $provider, string $modelKey): self
    {
        $client = new self;
        $client->endpoint = self::normalizeEndpoint($provider->endpoint);
        $client->model = $modelKey;

        try {
            $client->apiKey = $provider->api_key ? Crypt::decryptString($provider->api_key) : '';
        } catch (\Throwable $e) {
            $client->apiKey = '';
        }

        return $client;
    }

    /**
     * Normalisasi base URL ke akar `/v1`. Bila user mengisi endpoint lengkap
     * (mis. `https://host/v1/chat/completions`), potong suffix `/chat/completions`
     * supaya tidak dobel saat path ditambahkan kembali di `chatWithUsage`.
     */
    public static function normalizeEndpoint(string $endpoint): string
    {
        $endpoint = trim($endpoint);
        $endpoint = preg_replace('#/chat/completions/*$#i', '', $endpoint);

        return rtrim($endpoint, '/');
    }

    public function hasApiKey(): bool
    {
        return ! empty($this->apiKey);
    }

    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Kirim chat completion, kembalikan isi pesan. Throw exception jika gagal.
     */
    public function chat(array $messages, float $temperature = 0.3, int $maxTokens = 2000): string
    {
        return $this->chatWithUsage($messages, $temperature, $maxTokens)['content'];
    }

    /**
     * Kirim chat completion dan kembalikan konten + tool_calls + usage dari respons provider.
     * Mencoba ulang dengan backoff eksponensial untuk error transien (timeout, 429, 5xx)
     * supaya lebih stabil seperti router gateway.
     *
     * $options meneruskan parameter OpenAI tambahan (tools, tool_choice, response_format,
     * top_p, frequency_penalty, presence_penalty, stop, seed) agar gateway bersifat
     * transparan & mendukung function calling (Trae/Claude Code).
     */
    public function chatWithUsage(array $messages, float $temperature = 0.3, int $maxTokens = 2000, array $options = []): array
    {
        $url = rtrim($this->endpoint, '/') . '/chat/completions';

        $payload = array_filter([
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'tools' => $options['tools'] ?? null,
            'tool_choice' => $options['tool_choice'] ?? null,
            'response_format' => $options['response_format'] ?? null,
            'top_p' => $options['top_p'] ?? null,
            'frequency_penalty' => $options['frequency_penalty'] ?? null,
            'presence_penalty' => $options['presence_penalty'] ?? null,
            'stop' => $options['stop'] ?? null,
            'seed' => $options['seed'] ?? null,
        ], fn($v) => $v !== null);

        $lastError = null;
        $lastResponse = null;

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                    ->timeout(60)
                    ->connectTimeout(10)
                    ->post($url, $payload);

                $lastResponse = $response;

                if ($response->successful()) {
                    $data = $this->decodeResponse($response);

                    if ($data !== null) {
                        $choice = $data['choices'][0] ?? [];
                        $message = $choice['message'] ?? [];

                        return [
                            'content' => $this->extractContent($message),
                            'tool_calls' => $message['tool_calls'] ?? null,
                            'finish_reason' => $choice['finish_reason'] ?? 'stop',
                            'usage' => $data['usage'] ?? [],
                        ];
                    }

                    // JSON tidak terbaca — anggap transien, coba ulang.
                    $lastError = new \RuntimeException('Respons AI tidak valid (JSON tidak terbaca).');
                } else {
                    // Status definitif gagal (mis. 401/404) → langsung lemparkan, jangan retry.
                    if (! in_array($response->status(), self::RETRYABLE_STATUSES, true)) {
                        Log::error('AI API error', ['status' => $response->status(), 'body' => $response->body()]);
                        throw $this->statusError($response);
                    }

                    $lastError = $this->statusError($response);
                    Log::warning("AiClient attempt {$attempt} gagal (status {$response->status()}).");
                }
            } catch (ConnectionException $e) {
                $lastError = $e;
                Log::warning("AiClient attempt {$attempt} koneksi gagal: " . $e->getMessage());
            }

            if ($attempt < self::MAX_ATTEMPTS) {
                usleep($this->backoffDelay($attempt, $lastResponse));
            }
        }

        Log::error('AiClient semua percobaan gagal', ['error' => $lastError?->getMessage()]);
        throw new \RuntimeException('AI API error: ' . ($lastError?->getMessage() ?? 'tidak diketahui'));
    }

    /**
     * Streaming chat completion dari provider (token-by-token). Meneruskan tiap chunk
     * OpenAI-compatible ke $onChunk, lalu mengembalikan hasil akhir terakumulasi
     * (content, tool_calls, usage) untuk keperluan deduksi kredit.
     */
    public function streamChat(array $messages, float $temperature, int $maxTokens, array $options, callable $onChunk): array
    {
        $url = rtrim($this->endpoint, '/') . '/chat/completions';

        $payload = array_filter([
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'tools' => $options['tools'] ?? null,
            'tool_choice' => $options['tool_choice'] ?? null,
            'response_format' => $options['response_format'] ?? null,
            'top_p' => $options['top_p'] ?? null,
            'frequency_penalty' => $options['frequency_penalty'] ?? null,
            'presence_penalty' => $options['presence_penalty'] ?? null,
            'stop' => $options['stop'] ?? null,
            'seed' => $options['seed'] ?? null,
        ], fn($v) => $v !== null);
        $payload['stream'] = true;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'text/event-stream',
        ])
            ->withOptions(['stream' => true, 'timeout' => 300])
            ->send('POST', $url, ['json' => $payload]);

        if (! $response->successful()) {
            Log::error('AI API stream error', ['status' => $response->status(), 'body' => $response->body()]);
            throw $this->statusError($response);
        }

        // Provider mengabaikan stream → balas JSON penuh sekali (fallback aman).
        if (str_contains(strtolower((string) $response->header('Content-Type')), 'application/json')) {
            $data = $this->decodeResponse($response);
            if (is_array($data)) {
                $onChunk($data);
                $choice = $data['choices'][0] ?? [];
                $message = $choice['message'] ?? [];

                return [
                    'content' => $data['choices'][0]['message']['content'] ?? ($data['choices'][0]['message']['reasoning_content'] ?? ''),
                    'tool_calls' => $message['tool_calls'] ?? null,
                    'finish_reason' => $choice['finish_reason'] ?? 'stop',
                    'usage' => $data['usage'] ?? [],
                ];
            }
        }

        // Baca stream SSE incrementally.
        $body = $response->toPsrResponse()->getBody();
        $buffer = '';
        $content = '';
        $reasoning = '';
        $toolCalls = [];
        $finishReason = 'stop';
        $usage = [];

        while (! $body->eof()) {
            $buffer .= $body->read(4096);

            while (($sep = strpos($buffer, "\n\n")) !== false) {
                $event = substr($buffer, 0, $sep);
                $buffer = substr($buffer, $sep + 2);

                $data = '';
                foreach (explode("\n", $event) as $line) {
                    $line = rtrim($line, "\r");
                    if (str_starts_with($line, 'data:')) {
                        $data .= trim(substr($line, 5));
                    }
                }

                if ($data === '' || $data === '[DONE]') {
                    continue;
                }

                $chunk = json_decode($data, true);
                if (! is_array($chunk)) {
                    continue;
                }

                $onChunk($chunk);

                $choice = $chunk['choices'][0] ?? [];
                $delta = $choice['delta'] ?? [];

                // Jangan campur reasoning_content ke content per-chunk (itu penyebab
                // klien agent menerima "thinking" sebagai jawaban lalu menghapusnya &
                // loop rethink). Pisahkan keduanya.
                if (($delta['content'] ?? '') !== '') {
                    $content .= $delta['content'];
                } elseif (($delta['reasoning_content'] ?? '') !== '') {
                    $reasoning .= $delta['reasoning_content'];
                }

                if (! empty($delta['tool_calls'])) {
                    $toolCalls = $this->mergeToolCallDeltas($toolCalls, $delta['tool_calls']);
                }
                if (! empty($choice['finish_reason'])) {
                    $finishReason = $choice['finish_reason'];
                }
                if (! empty($chunk['usage'])) {
                    $usage = $chunk['usage'];
                }
            }
        }

        // Model reasoning (mis. DeepSeek V4 Pro via aggregator) kadang hanya mengirim
        // reasoning_content tanpa content final. Klien OpenAI-compatible menuntut content
        // tidak kosong — bila kosong, ia hapus "jawaban" lalu loop rethink & berakhir error -1.
        // Pakai reasoning sebagai jawaban (sama seperti fallback non-streaming).
        $reasoningFallback = false;
        if ($content === '' && $reasoning !== '') {
            $content = $reasoning;
            $reasoningFallback = true;
        }

        return [
            'content' => $content,
            'reasoning_fallback' => $reasoningFallback,
            'tool_calls' => $toolCalls ?: null,
            'finish_reason' => $finishReason,
            'usage' => $usage,
        ];
    }

    /**
     * Ambil isi pesan assistant. Model reasoning (mis. DeepSeek V4 Pro / aggregator)
     * mengirim jawaban lewat `reasoning_content` — fallback ke sana bila `content`
     * kosong supaya klien (Trae/Claude Code) tak menerima pesan kosong.
     */
    private function extractContent(array $message): string
    {
        $content = $message['content'] ?? '';

        if ((string) $content === '' && ! empty($message['reasoning_content'])) {
            $content = $message['reasoning_content'];
        }

        return (string) $content;
    }

    /**
     * Gabungkan delta tool_calls inkremental (streaming) menjadi satu struktur final.
     */
    private function mergeToolCallDeltas(array $acc, array $deltas): array
    {
        foreach ($deltas as $delta) {
            $idx = (int) ($delta['index'] ?? 0);

            if (! isset($acc[$idx])) {
                $acc[$idx] = [
                    'id' => '',
                    'type' => 'function',
                    'function' => ['name' => '', 'arguments' => ''],
                ];
            }

            if (! empty($delta['id'])) {
                $acc[$idx]['id'] = $delta['id'];
            }
            if (! empty($delta['type'])) {
                $acc[$idx]['type'] = $delta['type'];
            }
            if (! empty($delta['function']['name'])) {
                $acc[$idx]['function']['name'] = $delta['function']['name'];
            }
            if (isset($delta['function']['arguments'])) {
                $acc[$idx]['function']['arguments'] .= $delta['function']['arguments'];
            }
        }

        return $acc;
    }

    /**
     * Error berisi status + cuplikan body gateway, agar penyebab 502/4xx terlihat
     * (mis. "IP not allowed", pesan Cloudflare, dsb) — bukan hanya angka status.
     */
    private function statusError($response): \RuntimeException
    {
        $body = trim((string) $response->body());
        $body = preg_replace('/\s+/', ' ', $body);
        $snippet = $body !== '' ? ' — ' . mb_substr($body, 0, 200) : '';

        return new \RuntimeException('AI API error: ' . $response->status() . $snippet);
    }

    /**
     * Hitung jeda backoff, menghormati header Retry-After (detik) saat throttling 429.
     */
    private function backoffDelay(int $attempt, $response = null): int
    {
        $retryAfter = $response ? $response->header('Retry-After') : null;

        if (is_numeric($retryAfter)) {
            $seconds = max(0, (int) $retryAfter);

            return min(5_000_000, $seconds * 1_000_000);
        }

        return (2 ** ($attempt - 1)) * 200_000; // 200ms, 400ms
    }

    /**
     * Decode gateway response body. Beberapa gateway menambahkan chunk SSE
     * (mis. "data: [DONE]") tepat setelah payload JSON, atau membungkus respons
     * dengan framing SSE — parse dengan benar alih-alih regex greedy.
     */
    private function decodeResponse($response): ?array
    {
        $body = (string) $response->body();

        // 1. JSON murni (respons non-streaming normal).
        $direct = json_decode($body, true);
        if (is_array($direct)) {
            return $direct;
        }

        // 2. Buang framing SSE di sekitar/belakang payload lalu decode lagi.
        $clean = $this->stripSseFraming($body);
        if ($clean !== $body) {
            $decoded = json_decode($clean, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // 3. Potong di kurung tutup terakhir: beberapa gateway (mis. ai.wsd.my.id)
        //    menempelkan "data: [DONE]" persis setelah `}` tanpa baris baru.
        $pos = strrpos($body, '}');
        if ($pos !== false) {
            $decoded = json_decode(substr($body, 0, $pos + 1), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // 4. Ambil objek JSON pertama yang mengandung "choices" dari tiap baris data:.
        foreach ($this->extractSseDataLines($body) as $data) {
            $decoded = json_decode($data, true);
            if (is_array($decoded) && array_key_exists('choices', $decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Hapus baris "data: ..." (termasuk "[DONE]") yang mungkin disisipkan gateway.
     */
    private function stripSseFraming(string $body): string
    {
        $lines = preg_split('/\r?\n/', $body);

        $lines = array_filter($lines, fn($l) => ! str_starts_with(trim($l), 'data:'));

        return trim(implode("\n", $lines));
    }

    /**
     * Ekstrak payload dari tiap baris "data:" dalam framing SSE.
     */
    private function extractSseDataLines(string $body): array
    {
        $out = [];

        foreach (preg_split('/\r?\n/', $body) as $line) {
            $line = trim($line);
            if ($line === '' || ! str_starts_with($line, 'data:')) {
                continue;
            }

            $payload = trim(substr($line, 5));
            if ($payload !== '' && $payload !== '[DONE]') {
                $out[] = $payload;
            }
        }

        return $out;
    }
}
