<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_clients', function (Blueprint $table) {
            $table->string('wp_username')->nullable()->after('url');
            $table->text('wp_app_password')->nullable()->after('wp_username');
        });
    }

    public function down(): void
    {
        Schema::table('website_clients', function (Blueprint $table) {
            $table->dropColumn(['wp_username', 'wp_app_password']);
        });
    }
};
