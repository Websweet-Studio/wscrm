<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Paket kredit AI 100K: hapus diskon promo supaya harga kredit 1:1 (Rp 1/kredit).
 *
 * Alasan: `credit_price` dihitung dari `final_price / credits` (paket termurah per kredit),
 * jadi diskon 50% membuat harga jual token AI yang tampil ke pelanggan hanya separuh dari
 * rate kredit — rate 18.000/72.000 kredit hanya tampil Rp 9.000/Rp 36.000 per 1 juta token.
 * Setelah diskon 0: 18.000 kredit = Rp 18.000 (= $0,225 @ Rp 80.000/USD) dan
 * 72.000 kredit = Rp 72.000 (= $0,90) → tepat 1,5x harga upstream ($0,15 / $0,60).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('ai_packages')
            ->where('credits', 100000)
            ->where('price', 100000)
            ->update(['discount_amount' => 0.00, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Pulihkan promo lama (diskon 50%) — hanya untuk paket 100K.
        DB::table('ai_packages')
            ->where('credits', 100000)
            ->where('price', 100000)
            ->update(['discount_amount' => 50000.00, 'updated_at' => now()]);
    }
};
