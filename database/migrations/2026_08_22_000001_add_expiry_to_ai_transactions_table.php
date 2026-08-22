<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_transactions', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('credits');
            $table->integer('remaining')->nullable()->after('expires_at');
            $table->index(['expires_at', 'remaining']);
        });

        // Tambahkan nilai enum baru: refund (koreksi saldo) & expired (kredit hangus).
        DB::statement("ALTER TABLE ai_transactions MODIFY source ENUM('purchase','usage','manual_adjust','refund','expired') NOT NULL");
    }

    public function down(): void
    {
        // Kembalikan enum ke nilai semula; baris refund/expired tidak boleh ada dulu.
        DB::table('ai_transactions')->whereIn('source', ['refund', 'expired'])->delete();
        DB::statement("ALTER TABLE ai_transactions MODIFY source ENUM('purchase','usage','manual_adjust') NOT NULL");

        Schema::table('ai_transactions', function (Blueprint $table) {
            $table->dropIndex(['expires_at', 'remaining']);
            $table->dropColumn(['remaining', 'expires_at']);
        });
    }
};
