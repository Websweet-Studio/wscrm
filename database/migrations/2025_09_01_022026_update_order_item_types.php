<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if we're using SQLite
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support MODIFY COLUMN, so we need to drop indexes first
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropIndex(['order_id', 'item_type']);
                $table->dropIndex(['item_type', 'item_id']);
            });
            
            // Add new column
            Schema::table('order_items', function (Blueprint $table) {
                $table->string('item_type_new')->after('item_type');
            });
            
            // Copy data to new column
            DB::statement("UPDATE order_items SET item_type_new = item_type");
            
            // Drop old column and rename new one
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('item_type');
            });
            
            Schema::table('order_items', function (Blueprint $table) {
                $table->renameColumn('item_type_new', 'item_type');
            });
            
            // Recreate indexes
            Schema::table('order_items', function (Blueprint $table) {
                $table->index(['order_id', 'item_type']);
                $table->index(['item_type', 'item_id']);
            });
        } else {
            // For MySQL, we can use MODIFY COLUMN
            DB::statement("ALTER TABLE order_items MODIFY COLUMN item_type ENUM('hosting', 'domain', 'app', 'web', 'maintenance')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if we're using SQLite
        if (DB::getDriverName() === 'sqlite') {
            // Drop indexes first
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropIndex(['order_id', 'item_type']);
                $table->dropIndex(['item_type', 'item_id']);
            });
            
            // For SQLite, we'll just change it back to string
            Schema::table('order_items', function (Blueprint $table) {
                $table->string('item_type_old')->after('item_type');
            });
            
            // Copy data to new column
            DB::statement("UPDATE order_items SET item_type_old = item_type");
            
            // Drop old column and rename new one
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('item_type');
            });
            
            Schema::table('order_items', function (Blueprint $table) {
                $table->renameColumn('item_type_old', 'item_type');
            });
            
            // Recreate indexes
            Schema::table('order_items', function (Blueprint $table) {
                $table->index(['order_id', 'item_type']);
                $table->index(['item_type', 'item_id']);
            });
        } else {
            // For MySQL
            DB::statement("ALTER TABLE order_items MODIFY COLUMN item_type ENUM('hosting', 'domain')");
        }
    }
};
