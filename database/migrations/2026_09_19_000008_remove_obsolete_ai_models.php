<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hapus model AI usang: `mimo-v2.5` dan `oc-free`.
 *
 * Aman dihapus (dicek 19 Sep 2026):
 * - 0 baris `ai_combo_models` memakai keduanya (FK cascade, jadi tidak ada anak yatim),
 * - 0 baris `ai_transactions` memakai `ai_model_id` 3 / 4 (tanpa riwayat pemakaian),
 * - tidak disebut di `ai_settings`, blog, atau kode aplikasi.
 *
 * `down()` mengembalikan kedua baris persis seperti kondisi sebelum dihapus
 * (nilai diambil dari snapshot produksi `appws_wscrm`).
 */
return new class extends Migration
{
    private const ROWS = [
        [
            'id' => 3, 'provider_id' => 2, 'model_key' => 'mimo-v2.5', 'display_name' => 'mimo-v2.5',
            'input_rate' => 2000.0000, 'output_rate' => 3000.0000, 'is_active' => 1,
            'supports_vision' => 0, 'supports_deep_thinking' => 0, 'sort_order' => 0,
            'created_at' => '2026-08-20 08:16:42', 'updated_at' => '2026-08-20 14:55:52',
        ],
        [
            'id' => 4, 'provider_id' => 3, 'model_key' => 'oc-free', 'display_name' => 'oc-free',
            'input_rate' => 0.0000, 'output_rate' => 0.0000, 'is_active' => 1,
            'supports_vision' => 0, 'supports_deep_thinking' => 0, 'sort_order' => 0,
            'created_at' => '2026-08-20 08:24:45', 'updated_at' => '2026-08-20 08:25:13',
        ],
    ];

    public function up(): void
    {
        DB::table('ai_models')->whereIn('model_key', ['mimo-v2.5', 'oc-free'])->delete();
    }

    public function down(): void
    {
        foreach (self::ROWS as $row) {
            if (DB::table('ai_models')->where('model_key', $row['model_key'])->exists()) {
                continue;
            }
            DB::table('ai_models')->insert($row);
        }
    }
};
