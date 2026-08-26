<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_combos', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nama combo, mis. Coding, General, Vision');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ai_combo_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_combo_id')->constrained('ai_combos')->cascadeOnDelete();
            $table->foreignId('ai_model_id')->constrained('ai_models')->cascadeOnDelete();
            $table->integer('priority')->default(0)->comment('Urutan prioritas: 0 = utama, makin tinggi = fallback');
            $table->timestamps();

            $table->unique(['ai_combo_id', 'ai_model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_combo_models');
        Schema::dropIfExists('ai_combos');
    }
};
