<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('ai_package_id')->nullable()->after('service_id');
            $table->foreign('ai_package_id')->references('id')->on('ai_packages')->nullOnDelete();
        });

        // Tambah nilai enum invoice_type 'topup' (default 'setup' dipertahankan).
        DB::statement("ALTER TABLE invoices MODIFY COLUMN invoice_type ENUM('setup','renewal','upgrade','downgrade','topup') NOT NULL DEFAULT 'setup'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY COLUMN invoice_type ENUM('setup','renewal','upgrade','downgrade') NOT NULL DEFAULT 'setup'");

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['ai_package_id']);
            $table->dropColumn('ai_package_id');
        });
    }
};
