<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_client_id')->constrained('website_clients')->cascadeOnDelete();
            // Tanpa FK ke users: users.id di DB lama (production) bertipe non-bigint → errno 150.
            $table->foreignId('user_id')->nullable();
            $table->date('entry_date');
            $table->json('activities');
            $table->text('summary')->nullable();
            $table->timestamps();

            $table->unique(['website_client_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
