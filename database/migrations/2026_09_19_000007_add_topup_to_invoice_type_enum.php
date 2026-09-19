<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah nilai enum `invoices.invoice_type` = `topup`.
 *
 * Gejala: `Customer\AiController::purchasePackage()` membuat invoice dengan
 * `invoice_type = 'topup'`, tetapi enum produksi hanya ('setup','renewal','upgrade','downgrade')
 * → `SQLSTATE[01000] Warning: 1265 Data truncated for column 'invoice_type'` → beli paket kredit
 * AI gagal (bersama kolom `ai_package_id` yang hilang, lihat migrasi `2026_09_19_000006`).
 *
 * `down()` mengembalikan enum 4 nilai — hanya aman bila tidak ada baris `topup`
 * (invoice topup ada = kredit AI pelanggan sudah tercatat; jangan dihapus).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('invoice_type', ['setup', 'renewal', 'upgrade', 'downgrade', 'topup'])
                ->default('setup')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('invoice_type', ['setup', 'renewal', 'upgrade', 'downgrade'])
                ->default('setup')
                ->change();
        });
    }
};
