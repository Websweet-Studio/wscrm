<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_transactions', function (Blueprint $table) {
            $table->index(['source', 'created_at']);
            $table->index('ai_model_id');
            $table->index('invoice_id');
            $table->index('ai_package_id');
        });
    }

    public function down(): void
    {
        Schema::table('ai_transactions', function (Blueprint $table) {
            $table->dropIndex(['source', 'created_at']);
            $table->dropIndex(['ai_model_id']);
            $table->dropIndex(['invoice_id']);
            $table->dropIndex(['ai_package_id']);
        });
    }
};
