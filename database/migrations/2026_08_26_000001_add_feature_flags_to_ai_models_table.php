<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {
            $table->boolean('supports_vision')->default(false)->after('is_active');
            $table->boolean('supports_deep_thinking')->default(false)->after('supports_vision');
        });
    }

    public function down(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {
            $table->dropColumn(['supports_vision', 'supports_deep_thinking']);
        });
    }
};
