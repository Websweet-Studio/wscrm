<?php

namespace App\Services;

use App\Models\AiCredit;
use App\Models\AiModel;
use App\Models\AiTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrator multi-provider + deduksi kredit AI per customer.
 */
class AiGateway
{
    /**
     * Durasi (detik) provider di-"break" setelah gagal, supaya request berikutnya
     * langsung melompat ke provider sehat (circuit breaker sederhana).
     */
    private const CIRCUIT_BREAK_SECONDS = 30;
    /**
     * @return array{content: string, tool_calls: ?array, usage: array, credits_used: int, balance_after: int, model_key: string, provider_name: string}
     */
    public function chat(int $customerId, ?string $modelKey, array $messages, float $temperature = 0.3, int $maxTokens = 2000, array $options = []): array
    {
        $credit = AiCredit::firstOrCreate(['customer_id' => $customerId]);

        if ($credit->balance <= 0) {
            throw new \RuntimeException('Saldo AI tidak mencukupi. Silakan beli paket kredit.');
        }

        $models = $this->candidateModels($modelKey);

        // Estimasi biaya maksimum berdasarkan model & maxTokens, dan pastikan
        // saldo mencukupi SEBELUM memanggil provider (mencegah usage gratis saat
        // output melebihi saldo).
        $maxCredits = $this->estimateMaxCredits($models, $messages, $maxTokens);
        $this->ensureSufficientBalance($customerId, $maxCredits);

        $attempts = [];
        $lastError = null;
        $result = null;
        $usedModel = null;

        foreach ($models as $model) {
            if ($this->isCircuitBroken($model)) {
                $attempts[] = $model->model_key . ' (skip)';
                continue;
            }

            try {
                $client = AiClient::forProvider($model->provider, $model->model_key);
                $result = $client->chatWithUsage($messages, $temperature, $maxTokens, $options);
                $usedModel = $model;
                $this->markHealthy($model);
                break;
            } catch (\Throwable $e) {
                $lastError = $e;
                $attempts[] = $model->model_key;
                $this->markFailed($model);
                Log::warning("AiGateway fallback: model {$model->model_key} gagal - ".$e->getMessage());
            }
        }

        if ($result === null || $usedModel === null) {
            $detail = $lastError ? ' ('.implode(', ', $attempts).'): '.$lastError->getMessage() : ' ('.implode(', ', $attempts).')';
            throw new \RuntimeException('Semua provider AI gagal dihubungi. Coba lagi nanti.'.$detail);
        }

        $usage = $result['usage'] ?? [];
        $inputTokens = (int) ($usage['prompt_tokens'] ?? $this->estimateTokens(mb_strlen($this->inputPayload($messages))));
        $outputTokens = (int) ($usage['completion_tokens'] ?? $this->estimateTokens(mb_strlen((string) $result['content'])));

        $creditsUsed = $this->calculateCredits($usedModel, $inputTokens, $outputTokens);

        $balanceAfter = $this->deduct($customerId, $usedModel, $inputTokens, $outputTokens, $creditsUsed);

        return [
            'content' => $result['content'],
            'tool_calls' => $result['tool_calls'] ?? null,
            'finish_reason' => $result['finish_reason'] ?? 'stop',
            'usage' => ['prompt_tokens' => $inputTokens, 'completion_tokens' => $outputTokens],
            'credits_used' => $creditsUsed,
            'balance_after' => $balanceAfter,
            'model_key' => $usedModel->model_key,
            'provider_name' => $usedModel->provider->name,
        ];
    }

    /**
     * Model yang dicoba: yang diminta (bila aktif), lalu semua model aktif urut sort_order.
     */
    private function candidateModels(?string $modelKey): array
    {
        $query = AiModel::query()
            ->where('is_active', true)
            ->with('provider')
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($modelKey) {
            $specific = (clone $query)->where('model_key', $modelKey)->get();
            $specific = $specific->merge($query->where('model_key', '!=', $modelKey)->get());

            return $specific->all();
        }

        return $query->get()->all();
    }

    private function calculateCredits(AiModel $model, int $inputTokens, int $outputTokens): int
    {
        // Rate kini per 1M token: bagi token dengan 1.000.000.
        $credits = ($inputTokens / 1_000_000) * (float) $model->input_rate
            + ($outputTokens / 1_000_000) * (float) $model->output_rate;

        $credits = max(0, (int) round($credits));

        // Min 1 kredit bila ada pemakaian, supaya saldo tidak gratis total.
        if ($credits === 0 && ($inputTokens > 0 || $outputTokens > 0)) {
            $credits = 1;
        }

        return $credits;
    }

    /**
     * Estimasi biaya maksimum (kredit) utk request ini: input token diestimasi dari
     * panjang messages, output token memakai maxTokens. Diambil nilai terbesar dari
     * semua kandidat model (sebab fallback bisa mendarat di model mana pun).
     */
    private function estimateMaxCredits(array $models, array $messages, int $maxTokens): int
    {
        if (count($models) === 0) {
            return 1;
        }

        $inputTokens = max(1, $this->estimateTokens(mb_strlen($this->inputPayload($messages))));

        $max = 0;
        foreach ($models as $model) {
            $max = max($max, $this->calculateCredits($model, $inputTokens, $maxTokens));
        }

        return max(1, $max);
    }

    private function ensureSufficientBalance(int $customerId, int $requiredCredits): void
    {
        $credit = AiCredit::where('customer_id', $customerId)->lockForUpdate()->first();

        if (! $credit || $credit->balance < $requiredCredits) {
            throw new \RuntimeException('Saldo AI tidak mencukupi. Silakan beli paket kredit.');
        }
    }

    private function deduct(int $customerId, AiModel $model, int $inputTokens, int $outputTokens, int $creditsUsed): int
    {
        return DB::transaction(function () use ($customerId, $model, $inputTokens, $outputTokens, $creditsUsed) {
            $credit = AiCredit::where('customer_id', $customerId)->lockForUpdate()->first();

            if (! $credit || $credit->balance < $creditsUsed) {
                throw new \RuntimeException('Saldo AI tidak mencukupi. Silakan beli paket kredit.');
            }

            AiTransaction::create([
                'customer_id' => $customerId,
                'type' => 'out',
                'source' => 'usage',
                'credits' => -$creditsUsed,
                'ai_model_id' => $model->id,
                'tokens_input' => $inputTokens,
                'tokens_output' => $outputTokens,
                'description' => "Chat AI pakai model {$model->model_key}",
            ]);

            $credit->decrement('balance', $creditsUsed);

            return (int) $credit->fresh()->balance;
        });
    }

    private function estimateTokens(int $charLength): int
    {
        return (int) max(1, ceil($charLength / 4));
    }

    /**
     * Circuit breaker: provider yang baru saja gagal di-skip sementara agar request
     * berikutnya tidak menunggu timeout provider yang sama berulang kali.
     */
    private function circuitKey(AiModel $model): string
    {
        return 'ai_circuit_' . $model->provider_id;
    }

    private function isCircuitBroken(AiModel $model): bool
    {
        return Cache::has($this->circuitKey($model));
    }

    private function markFailed(AiModel $model): void
    {
        try {
            $providerId = $model->provider_id;
            Cache::put($this->circuitKey($model), true, self::CIRCUIT_BREAK_SECONDS);
            Cache::increment('ai_failures_' . $providerId);
            Cache::put('ai_last_failed_' . $providerId, now()->toIso8601String(), 3600);
        } catch (\Throwable $e) {
            // Cache tidak tersedia bukan blocker.
        }
    }

    private function markHealthy(AiModel $model): void
    {
        try {
            Cache::forget($this->circuitKey($model));
            Cache::forget('ai_failures_' . $model->provider_id);
            Cache::forget('ai_last_failed_' . $model->provider_id);
        } catch (\Throwable $e) {
            // noop
        }
    }

    /**
     * Status kesehatan provider untuk ditampilkan di Admin > AI > Providers.
     */
    public static function health(int $providerId): array
    {
        try {
            return [
                'broken' => Cache::has('ai_circuit_' . $providerId),
                'failures' => (int) (Cache::get('ai_failures_' . $providerId) ?? 0),
                'last_failed_at' => Cache::get('ai_last_failed_' . $providerId),
            ];
        } catch (\Throwable $e) {
            return ['broken' => false, 'failures' => 0, 'last_failed_at' => null];
        }
    }

    private function inputPayload(array $messages): string
    {
        try {
            return json_encode($messages, JSON_UNESCAPED_UNICODE) ?: '';
        } catch (\Throwable $e) {
            return '';
        }
    }
}
