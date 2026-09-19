<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom harga promo registrasi domain dari API RDash.
     *
     * Harga promo RDash hanya berlaku untuk registrasi siklus 1 tahun
     * (tidak berlaku perpanjangan/transfer), sehingga harga normal tetap
     * disimpan di kolom lama (base_cost / selling_price).
     */
    public function up(): void
    {
        Schema::table('domain_prices', function (Blueprint $table) {
            if (! Schema::hasColumn('domain_prices', 'promo_price')) {
                $table->decimal('promo_price', 10, 2)->nullable()->after('renewal_price_with_tax')
                    ->comment('Harga promo registrasi dari RDash (eksklusif PPN)');
            }

            if (! Schema::hasColumn('domain_prices', 'promo_base_cost')) {
                $table->decimal('promo_base_cost', 10, 2)->nullable()->after('promo_price')
                    ->comment('Modal saat promo = harga promo + PPN');
            }

            if (! Schema::hasColumn('domain_prices', 'promo_selling_price')) {
                $table->decimal('promo_selling_price', 10, 2)->nullable()->after('promo_base_cost')
                    ->comment('Harga jual saat promo aktif (modal promo + margin, dibulatkan)');
            }

            if (! Schema::hasColumn('domain_prices', 'promo_starts_at')) {
                $table->timestamp('promo_starts_at')->nullable()->after('promo_selling_price');
            }

            if (! Schema::hasColumn('domain_prices', 'promo_ends_at')) {
                $table->timestamp('promo_ends_at')->nullable()->after('promo_starts_at');
            }

            if (! Schema::hasColumn('domain_prices', 'promo_note')) {
                $table->text('promo_note')->nullable()->after('promo_ends_at')
                    ->comment('Syarat promo dari RDash (HTML dibersihkan)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('domain_prices', function (Blueprint $table) {
            foreach (['promo_note', 'promo_ends_at', 'promo_starts_at', 'promo_selling_price', 'promo_base_cost', 'promo_price'] as $column) {
                if (Schema::hasColumn('domain_prices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
