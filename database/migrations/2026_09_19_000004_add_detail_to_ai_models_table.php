<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Detail spesifikasi model AI (dari katalog provider) — ditampilkan ke pelanggan
 * sebagai tombol "Detail" (modal) di halaman /customer/ai tab Harga Token.
 * Semua kolom nullable: model lama tidak wajib punya detail.
 *
 * Catatan: cache_read_rate & agent_loop_cost = referensi upstream (USD per 1 juta
 * token), BUKAN tarif yang ditagih relay (relay hanya menagih token in/out).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {
            $table->string('description')->nullable()->after('label');
            $table->string('upstream_slug')->nullable()->after('description');
            $table->string('cli_command')->nullable()->after('upstream_slug');
            $table->decimal('intelligence_index', 5, 1)->nullable()->after('cli_command');
            $table->decimal('output_speed', 8, 1)->nullable()->after('intelligence_index');
            $table->string('context_window', 32)->nullable()->after('output_speed');
            $table->decimal('cache_read_rate', 10, 6)->nullable()->after('context_window');
            $table->decimal('agent_loop_cost', 10, 6)->nullable()->after('cache_read_rate');
            $table->date('released_at')->nullable()->after('agent_loop_cost');
        });
    }

    public function down(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'upstream_slug',
                'cli_command',
                'intelligence_index',
                'output_speed',
                'context_window',
                'cache_read_rate',
                'agent_loop_cost',
                'released_at',
            ]);
        });
    }
};
