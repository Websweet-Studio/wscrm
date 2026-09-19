<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bersihkan tabel sisa fitur AI Agent admin.
 *
 * Fitur AI Agent admin (AiConversation/AiMessage + 7 agen) sudah dihapus dari
 * aplikasi, sehingga tabel ini tidak lagi dibaca/ditulis oleh kode mana pun.
 * Tabel dibiarkan ada hanya sebagai sampah di instalasi yang sudah berjalan,
 * dan tidak akan pernah dipakai lagi oleh instalasi baru.
 *
 * Aman dijalankan berulang (dropIfExists) — termasuk untuk instalasi yang
 * tabelnya sudah dihapus manual oleh admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Anak dulu (FK ke ai_conversations), baru induk.
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }

    /**
     * Reversible: kalau ada instalasi yang perlu rollback ke versi lama
     * (yang masih memakai AI Agent admin), tabel dikembalikan lagi.
     */
    public function down(): void
    {
        if (! Schema::hasTable('ai_conversations')) {
            Schema::create('ai_conversations', function (Blueprint $table) {
                $table->id();
                // Tanpa FK ke users/customers: konsisten dengan kebijakan skema lama (errno 150).
                // Catatan: jangan pakai ->after() di dalam Schema::create()
                // (MySQL menolak "AFTER" pada CREATE TABLE → error 1064).
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->foreignId('user_id')->nullable();
                $table->string('title')->default('Percakapan baru');
                $table->timestamps();

                $table->index('customer_id');
            });
        }

        if (! Schema::hasTable('ai_messages')) {
            Schema::create('ai_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
                $table->string('role');
                $table->text('content');
                $table->json('actions')->nullable();
                $table->timestamps();

                $table->index('conversation_id');
            });
        }
    }
};
