<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_clients', function (Blueprint $table) {
            $table->boolean('auto_update_enabled')->default(false)->after('is_active');
            $table->timestamp('last_auto_update_at')->nullable()->after('auto_update_enabled');
            $table->string('last_auto_update_status')->nullable()->after('last_auto_update_at');
        });
    }

    public function down(): void
    {
        Schema::table('website_clients', function (Blueprint $table) {
            $table->dropColumn(['auto_update_enabled', 'last_auto_update_at', 'last_auto_update_status']);
        });
    }
};
