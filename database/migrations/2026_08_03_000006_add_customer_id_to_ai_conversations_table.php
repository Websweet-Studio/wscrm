<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_conversations', function (Blueprint $table) {
            // Tanpa FK ke customers: konsisten dengan kebijakan skema lama (errno 150).
            $table->unsignedBigInteger('customer_id')->nullable()->after('id');
            $table->index('customer_id');

            // user_id dibuat nullable agar percakapan customer (tanpa user_id) sah.
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
};
