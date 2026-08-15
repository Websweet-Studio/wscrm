<?php

namespace Database\Seeders;

use App\Models\AiModel;
use App\Models\AiProvider;
use Illuminate\Database\Seeder;

/**
 * Katalog model dari paket aggregator $10/bulan (quota per bulan).
 * Harga jual per 1M token = estimasi harga per request (asumsi 1 request ≈ 1K token),
 * margin ±2.5x dari cost: $10 × Rp18.000 / quota bulanan, dibulatkan.
 * Model key memakai slug — sesuaikan dgn nama model aktual provider via Admin > AI > Models
 * jika perlu. Endpoint + api key provider wajib diisi via Admin > AI > Providers.
 */
class AiCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $provider = AiProvider::updateOrCreate(
            ['name' => 'AI Aggregator'],
            ['endpoint' => '', 'is_active' => true, 'sort_order' => 2]
        );

        // [model_key, display_name, kredit per 1M token, sort]
        $models = [
            ['grok-4.5', 'Grok 4.5', 150000, 1],
            ['gpt-5.6-luna', 'GPT 5.6 Luna', 10000, 2],
            ['glm-5.2', 'GLM-5.2', 20000, 3],
            ['glm-5.1', 'GLM-5.1', 20000, 4],
            ['kimi-k3', 'Kimi K3', 200000, 5],
            ['kimi-k2.7-code', 'Kimi K2.7 Code', 15000, 6],
            ['kimi-k2.6', 'Kimi K2.6', 15000, 7],
            ['mimo-v2.5', 'MiMo-V2.5', 1000, 8],
            ['mimo-v2.5-pro', 'MiMo-V2.5-Pro', 6000, 9],
            ['minimax-m3', 'MiniMax M3', 6000, 10],
            ['minimax-m2.7', 'MiniMax M2.7', 5000, 11],
            ['qwen3.7-max', 'Qwen3.7 Max', 20000, 12],
            ['qwen3.7-plus', 'Qwen3.7 Plus', 5000, 13],
            ['qwen3.6-plus', 'Qwen3.6 Plus', 6000, 14],
            ['deepseek-v4-pro', 'DeepSeek V4 Pro', 5000, 15],
            ['deepseek-v4-flash', 'DeepSeek V4 Flash', 1000, 16],
            ['hy3', 'Hy3', 5000, 17],
        ];

        foreach ($models as [$key, $name, $rate, $sort]) {
            AiModel::updateOrCreate(
                ['provider_id' => $provider->id, 'model_key' => $key],
                ['display_name' => $name, 'input_rate' => $rate, 'output_rate' => $rate, 'is_active' => true, 'sort_order' => $sort]
            );
        }
    }
}
