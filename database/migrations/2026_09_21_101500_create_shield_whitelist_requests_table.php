<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat pengajuan whitelist sementara oleh pelanggan dari halaman IP Block
 * (/customer/ip-block). Satu baris = satu pengajuan.
 *
 * Kisah pengajuan disimpan di sisi WSCRM (bukan hanya di WSD Shield) supaya portal
 * bisa: (a) menampilkan "kamu sudah ajukan 2 dari 3 kali hari ini", (b) menolak
 * pengajuan beruntun lewat pembatasan harian, dan (c) tetap punya jejak kalau
 * layanan Shield sedang tidak bisa dihubungi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shield_whitelist_requests', function (Blueprint $table) {
            $table->id();
            // Sejak 21 Sep 2026 tabel `customers` bertipe MyISAM (tanpa dukungan foreign key),
            // jadi relasinya dijaga di level aplikasi + index — bukan constraint FK.
            $table->unsignedBigInteger('customer_id');
            $table->string('ip', 64);
            $table->string('domain')->nullable();
            $table->string('reason')->nullable();
            $table->unsignedSmallInteger('hours')->default(24);
            // diterima = Shield membuka blokir; gagal = ditolak/tidak bisa dihubungi.
            $table->string('status', 16)->default('diterima');
            $table->string('note')->nullable();
            $table->string('server', 96)->nullable(); // agent yang memblokir (mis. host.sweet.web.id)
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
            $table->index('ip');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shield_whitelist_requests');
    }
};
