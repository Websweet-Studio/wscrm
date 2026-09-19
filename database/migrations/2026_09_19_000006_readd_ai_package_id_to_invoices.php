<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambal kolom `invoices.ai_package_id` yang HILANG di DB produksi.
 *
 * Gejala: `Customer\AiController::purchasePackage()` menulis dan mencari kolom ini →
 * SQLSTATE[42S22] Unknown column 'ai_package_id' → halaman "Beli paket kredit AI" error 500,
 * dan tidak ada satu pun invoice `topup` yang pernah terbit (0 baris di produksi).
 *
 * Migrasi lama `2026_08_03_000007_add_ai_package_id_to_invoices_table` tercatat sudah
 * dijalankan (batch 23) tetapi kolomnya tidak ada di `appws_wscrm` = schema drift
 * (kolom `service_id` yang jadi acuan `->after()` juga sudah dihapus migrasi lain).
 * Migrasi ini idempoten: hanya menambah bila belum ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('invoices', 'ai_package_id')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('ai_package_id')->nullable()->after('payment_account_id');
            $table->index('ai_package_id');
        });

        try {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreign('ai_package_id')->references('id')->on('ai_packages')->nullOnDelete();
            });
        } catch (\Throwable $e) {
            // FK gagal (mis. tipe/engine berbeda) tidak boleh menggagalkan migrasi —
            // kolom + index sudah cukup agar alur beli paket AI jalan.
            report($e);
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('invoices', 'ai_package_id')) {
            return;
        }

        try {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign(['ai_package_id']);
            });
        } catch (\Throwable $e) {
            report($e);
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['ai_package_id']);
            $table->dropColumn('ai_package_id');
        });
    }
};
