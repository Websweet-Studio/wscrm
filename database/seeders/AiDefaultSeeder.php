<?php

namespace Database\Seeders;

use App\Models\AiModel;
use App\Models\AiPackage;
use App\Models\AiProvider;
use Illuminate\Database\Seeder;

/**
 * Default provider + model + paket agar fitur bisa langsung dicoba.
 * Api key provider dikosongkan — admin wajib mengisinya lewat menu Admin > AI > Providers.
 */
class AiDefaultSeeder extends Seeder
{
    public function run(): void
    {
        $provider = AiProvider::updateOrCreate(
            ['name' => 'OpenRouter'],
            ['endpoint' => 'https://openrouter.ai/api/v1', 'is_active' => true, 'sort_order' => 1]
        );

        // Rate kredit per 1K token ≈ margin ±2.5x harga pasar /1M (kurs Rp18.000) ÷ Rp5.000.
        $models = [
            ['model_key' => 'openai/gpt-4o-mini', 'display_name' => 'GPT-4o Mini', 'input_rate' => 1.5, 'output_rate' => 5.5, 'sort_order' => 1],
            ['model_key' => 'openai/gpt-4o', 'display_name' => 'GPT-4o', 'input_rate' => 25, 'output_rate' => 90, 'sort_order' => 2],
            ['model_key' => 'anthropic/claude-3.5-sonnet', 'display_name' => 'Claude 3.5 Sonnet', 'input_rate' => 27, 'output_rate' => 135, 'sort_order' => 3],
        ];

        foreach ($models as $model) {
            AiModel::updateOrCreate(
                ['provider_id' => $provider->id, 'model_key' => $model['model_key']],
                $model + ['is_active' => true]
            );
        }

        $packages = [
            ['name' => 'Starter 10K', 'credits' => 10000, 'price' => 50000, 'sort_order' => 1],
            ['name' => 'Regular 50K', 'credits' => 50000, 'price' => 200000, 'discount_amount' => 10000, 'sort_order' => 2],
            ['name' => 'Bisnis 250K', 'credits' => 250000, 'price' => 900000, 'discount_amount' => 50000, 'sort_order' => 3],
        ];

        foreach ($packages as $package) {
            AiPackage::updateOrCreate(
                ['name' => $package['name']],
                $package + ['is_active' => true]
            );
        }
    }
}
