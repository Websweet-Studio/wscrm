<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            DB::statement("ALTER TABLE order_items MODIFY COLUMN item_type ENUM('hosting','domain','service','app','web','maintenance')");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            DB::statement("ALTER TABLE order_items MODIFY COLUMN item_type ENUM('hosting','domain','app','web','maintenance')");
        });
    }
};
