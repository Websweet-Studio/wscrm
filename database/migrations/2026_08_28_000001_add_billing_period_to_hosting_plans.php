<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hosting_plans', function (Blueprint $table) {
            $table->string('billing_period', 20)->default('annually')->after('service_type')->comment('monthly, quarterly, semi_annually, annually');
        });
    }

    public function down(): void
    {
        Schema::table('hosting_plans', function (Blueprint $table) {
            $table->dropColumn('billing_period');
        });
    }
};
