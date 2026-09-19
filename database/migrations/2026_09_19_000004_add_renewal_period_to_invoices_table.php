<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Periode tagihan invoice.
 *
 * - `period_end` : tanggal jatuh tempo LAYANAN yang ditagih invoice ini. Dipakai
 *                  sebagai penjaga anti-dobel (satu periode = satu invoice) dan
 *                  untuk mengenali "invoice lunas tapi layanan belum diperpanjang"
 *                  (Invoice::serviceRenewalPending()).
 *
 * Idempoten (aman dijalankan ulang) dan reversible lewat down().
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('invoices', 'period_end')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->date('period_end')->nullable()->comment('Jatuh tempo layanan yang ditagih');
            });
        }

        // Backfill period_end dari catatan invoice lama ("... expiring on 01 Oct 2026").
        // Format bulan Inggris wajib eksplisit karena %b ikut lc_time_names.
        DB::statement("SET lc_time_names = 'en_US'");
        DB::statement(
            "UPDATE invoices
                SET period_end = STR_TO_DATE(SUBSTRING(SUBSTRING_INDEX(notes, 'expiring on ', -1), 1, 11), '%d %b %Y')
              WHERE invoice_type = 'renewal'
                AND period_end IS NULL
                AND notes LIKE '%expiring on %'"
        );

        // Index untuk penjaga anti-dobel (order + tipe + periode).
        $indexExists = collect(DB::select("SHOW INDEX FROM invoices WHERE Key_name = 'invoices_order_period_index'"))->isNotEmpty();

        if (! $indexExists) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->index(['order_id', 'invoice_type', 'period_end'], 'invoices_order_period_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'period_end')) {
            $indexExists = collect(DB::select("SHOW INDEX FROM invoices WHERE Key_name = 'invoices_order_period_index'"))->isNotEmpty();

            if ($indexExists) {
                Schema::table('invoices', function (Blueprint $table) {
                    $table->dropIndex('invoices_order_period_index');
                });
            }

            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('period_end');
            });
        }
    }
};
