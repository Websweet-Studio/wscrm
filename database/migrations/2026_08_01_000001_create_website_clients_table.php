<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('website_clients', function (Blueprint $table) {
      $table->id();
      $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
      $table->string('name');
      $table->string('url');
      $table->string('wp_version')->nullable();
      $table->string('theme_name')->nullable();
      $table->string('theme_version')->nullable();
      $table->json('plugins')->nullable();
      $table->text('notes')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('website_clients');
  }
};
