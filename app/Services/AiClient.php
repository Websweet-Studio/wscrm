<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HTTP client untuk AI gateway (OpenAI-compatible chat completions).
 */
class AiClient
{
    private string $endpoint;
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->endpoint = config('services.ai.endpoint', env('AI_ENDPOINT', 'https://api.openai.com/v1'));
        $this->apiKey = config('services.ai.api_key', env('AI_API_KEY', ''));
        $this->model = config('services.ai.model', env('AI_MODEL', 'gpt-4o-mini'));
    }

    /**
     * Buat client untuk provider dari DB (multi-provider). Api key didekripsi.
     */
    public static function forProvider(\App\Models\AiProvider $provider, string $modelKey): self
    {
        $client = new self;
        $client->endpoint = rtrim($provider->endpoint, '/');
        $client->model = $modelKey;

        try {
            $client->apiKey = $provider->api_key ? Crypt::decryptString($provider->api_key) : '';
        } catch (\Throwable $e) {
            $client->apiKey = '';
        }

        return $client;
    }

    public function hasApiKey(): bool
    {
        return !empty($this->apiKey);
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
     * Kirim chat completion dan kembalikan konten + usage (token) dari respons provider.
     */
    public function chatWithUsage(array $messages, float $temperature = 0.3, int $maxTokens = 2000): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout(60)
            ->post(rtrim($this->endpoint, '/') . '/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

        if (!$response->successful()) {
            Log::error('AI API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('AI API error: ' . $response->status());
        }

        $data = $this->decodeResponse($response);

        return [
            'content' => $data['choices'][0]['message']['content'] ?? '',
            'usage' => $data['usage'] ?? [],
        ];
    }

    /**
     * Decode gateway response body. Beberapa gateway menambahkan chunk SSE
     * (mis. "data: [DONE]") tepat setelah payload JSON, yang membuat json_decode gagal —
     * ambil objek JSON-nya saja.
     */
    private function decodeResponse($response): ?array
    {
        $body = $response->body();

        return preg_match('/\{.*\}/s', $body, $m)
            ? json_decode($m[0], true)
            : null;
    }
}
