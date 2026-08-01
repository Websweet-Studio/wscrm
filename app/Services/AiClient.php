<?php

namespace App\Services;

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

        return $data['choices'][0]['message']['content'] ?? '';
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
