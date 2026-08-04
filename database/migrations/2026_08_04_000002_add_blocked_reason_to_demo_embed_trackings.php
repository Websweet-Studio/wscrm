<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demo_embed_trackings', function (Blueprint $table) {
            $table->string('blocked_reason')->nullable()->after('is_blocked');
        });
    }

    public function down(): void
    {
        Schema::table('demo_embed_trackings', function (Blueprint $table) {
            $table->dropColumn('blocked_reason');
        });
    }
};
