<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_credits', function (Blueprint $table) {
            // Hash sha256 dari api_key plaintext untuk lookup O(1) tanpa dekripsi massal.
            $table->string('api_key_hash', 64)->nullable()->after('api_key');
            $table->index('api_key_hash');
        });
    }

    public function down(): void
    {
        Schema::table('ai_credits', function (Blueprint $table) {
            $table->dropIndex(['api_key_hash']);
            $table->dropColumn('api_key_hash');
        });
    }
};
