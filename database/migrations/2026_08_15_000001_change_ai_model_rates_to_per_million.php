<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  public function up(): void
  {
    // Konversi rate dari "kredit per 1K token" → "kredit per 1M token" (× 1000).
    DB::table('ai_models')->update([
      'input_rate' => DB::raw('input_rate * 1000'),
      'output_rate' => DB::raw('output_rate * 1000'),
    ]);
  }

  public function down(): void
  {
    DB::table('ai_models')->update([
      'input_rate' => DB::raw('input_rate / 1000'),
      'output_rate' => DB::raw('output_rate / 1000'),
    ]);
  }
};
