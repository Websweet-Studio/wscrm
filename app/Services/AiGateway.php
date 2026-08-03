<?php

namespace App\Services;

use App\Models\AiCredit;
use App\Models\AiModel;
use App\Models\AiTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrator multi-provider + deduksi kredit AI per customer.
 */
class AiGateway
{
    /**
     * @return array{content: string, usage: array, credits_used: int, balance_after: int, model_key: string, provider_name: string}
     */
    public function chat(int $customerId, ?string $modelKey, array $messages, float $temperature = 0.3, int $maxTokens = 2000): array
    {
        $credit = AiCredit::firstOrCreate(['customer_id' => $customerId]);

        if ($credit->balance <= 0) {
            throw new \RuntimeException('Saldo AI tidak mencukupi. Silakan beli paket kredit.');
        }

        $models = $this->candidateModels($modelKey);

        $attempts = [];
        $lastError = null;
        $result = null;
        $usedModel = null;

        foreach ($models as $model) {
            try {
                $client = AiClient::forProvider($model->provider, $model->model_key);
                $result = $client->chatWithUsage($messages, $temperature, $maxTokens);
                $usedModel = $model;
                break;
            } catch (\Throwable $e) {
                $lastError = $e;
                $attempts[] = $model->model_key;
                Log::warning("AiGateway fallback: model {$model->model_key} gagal - ".$e->getMessage());
            }
        }

        if ($result === null || $usedModel === null) {
            throw new \RuntimeException('Semua provider AI gagal dihubungi. Coba lagi nanti. ('.implode(', ', $attempts).')');
        }

        $usage = $result['usage'] ?? [];
        $inputTokens = (int) ($usage['prompt_tokens'] ?? $this->estimateTokens(mb_strlen($this->inputPayload($messages))));
        $outputTokens = (int) ($usage['completion_tokens'] ?? $this->estimateTokens(mb_strlen((string) $result['content'])));

        $creditsUsed = $this->calculateCredits($usedModel, $inputTokens, $outputTokens);

        $balanceAfter = $this->deduct($customerId, $usedModel, $inputTokens, $outputTokens, $creditsUsed);

        return [
            'content' => $result['content'],
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
        $credits = (int) ceil($inputTokens / 1000) * (float) $model->input_rate
            + (int) ceil($outputTokens / 1000) * (float) $model->output_rate;

        $credits = max(0, (int) round($credits));

        // Min 1 kredit bila ada pemakaian, supaya saldo tidak gratis total.
        if ($credits === 0 && ($inputTokens > 0 || $outputTokens > 0)) {
            $credits = 1;
        }

        return $credits;
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

    private function inputPayload(array $messages): string
    {
        try {
            return json_encode($messages, JSON_UNESCAPED_UNICODE) ?: '';
        } catch (\Throwable $e) {
            return '';
        }
    }
}
