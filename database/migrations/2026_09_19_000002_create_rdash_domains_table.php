<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cermin domain dari API RDASH + status pencocokan dengan order WSCRM.
 *
 * Idempoten: hanya membuat tabel jika belum ada. Aman untuk instalasi yang
 * sudah menjalankan versi ini (tabel dilewati, tidak error).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rdash_domains')) {
            return;
        }

        Schema::create('rdash_domains', function (Blueprint $table) {
            $table->id();

            // Data asli dari RDash
            // rdash_id nullable: baris "missing" (domain ada di order WSCRM tapi tidak di RDash)
            // tidak punya ID dari registrar.
            $table->unsignedBigInteger('rdash_id')->nullable()->unique();
            $table->string('name', 191)->unique();
            $table->unsignedTinyInteger('status')->nullable();
            $table->string('status_label', 50)->nullable();
            $table->string('status_reason', 191)->nullable();
            $table->unsignedTinyInteger('verification_status')->nullable();
            $table->string('verification_status_label', 50)->nullable();
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_registrar_locked')->default(false);
            $table->string('nameserver_1', 191)->nullable();
            $table->string('nameserver_2', 191)->nullable();
            $table->string('nameserver_3', 191)->nullable();
            $table->string('nameserver_4', 191)->nullable();
            $table->string('nameserver_5', 191)->nullable();
            $table->text('notes')->nullable();
            $table->date('expired_at')->nullable();
            $table->dateTime('rdash_created_at')->nullable();
            $table->unsignedBigInteger('rdash_customer_id')->nullable();

            // Pencocokan ke order WSCRM
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->date('order_expires_at')->nullable();
            $table->string('match_status', 20)->default('unmatched');
            $table->integer('drift_days')->nullable();
            $table->unsignedInteger('order_matches')->default(0);

            // Meta sinkronisasi
            $table->timestamp('last_synced_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index('expired_at');
            $table->index('match_status');
            $table->index('order_id');
            $table->index('rdash_customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rdash_domains');
    }
};
