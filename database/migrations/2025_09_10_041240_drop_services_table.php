<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First drop foreign key constraint from invoices table
        Schema::table('invoices', function ($table) {
            $table->dropForeign('invoices_service_id_foreign');
        });

        // Then drop the services table
        Schema::dropIfExists('services');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
