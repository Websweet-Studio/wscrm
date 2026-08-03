<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_credits', function (Blueprint $table) {
            // API key per customer untuk endpoint OpenAI-compatible (dienkripsi via Crypt → TEXT).
            $table->text('api_key')->nullable()->after('balance');
        });
    }

    public function down(): void
    {
        Schema::table('ai_credits', function (Blueprint $table) {
            $table->dropColumn('api_key');
        });
    }
};
