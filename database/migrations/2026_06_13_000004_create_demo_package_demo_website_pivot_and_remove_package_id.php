<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create many-to-many pivot table
        Schema::create('demo_package_demo_website', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demo_package_id')->constrained('demo_packages')->cascadeOnDelete();
            $table->foreignId('demo_website_id')->constrained('demo_websites')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['demo_package_id', 'demo_website_id']);
        });

        // Migrate existing demo_package_id data to pivot table
        $existingMappings = \DB::table('demo_websites')
            ->whereNotNull('demo_package_id')
            ->select('id', 'demo_package_id')
            ->get();

        foreach ($existingMappings as $mapping) {
            \DB::table('demo_package_demo_website')->insert([
                'demo_package_id' => $mapping->demo_package_id,
                'demo_website_id' => $mapping->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Drop the old foreign key and column
        Schema::table('demo_websites', function (Blueprint $table) {
            $table->dropForeign(['demo_package_id']);
            $table->dropColumn('demo_package_id');
        });
    }

    public function down(): void
    {
        Schema::table('demo_websites', function (Blueprint $table) {
            $table->unsignedBigInteger('demo_package_id')->nullable()->after('demo_category_id');
            $table->foreign('demo_package_id')->references('id')->on('demo_packages')->nullOnDelete();
        });

        Schema::dropIfExists('demo_package_demo_website');
    }
};