<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DemoCategory;
use App\Models\DemoPackage;
use App\Models\DemoWebsite;

return new class extends Migration
{
    public function up(): void
    {
        // First, create demo_categories and demo_packages from existing data
        $categories = DemoWebsite::select('category')->distinct()->whereNotNull('category')->pluck('category');
        $packages = DemoWebsite::select('package')->distinct()->whereNotNull('package')->where('package', '!=', '')->pluck('package');

        $categoryMap = [];
        foreach ($categories as $category) {
            $cat = DemoCategory::create([
                'name' => ucfirst($category),
                'slug' => \Illuminate\Support\Str::slug($category),
                'description' => null,
                'is_active' => true,
                'sort_order' => 0,
            ]);
            $categoryMap[$category] = $cat->id;
        }

        $packageMap = [];
        foreach ($packages as $package) {
            $pkg = DemoPackage::create([
                'name' => ucfirst($package),
                'slug' => \Illuminate\Support\Str::slug($package),
                'description' => null,
                'is_active' => true,
                'sort_order' => 0,
            ]);
            $packageMap[$package] = $pkg->id;
        }

        // Add new foreign key columns
        Schema::table('demo_websites', function (Blueprint $table) {
            $table->unsignedBigInteger('demo_category_id')->nullable()->after('url');
            $table->unsignedBigInteger('demo_package_id')->nullable()->after('demo_category_id');
        });

        // Migrate existing data
        foreach (DemoWebsite::all() as $demo) {
            $updates = [];
            if ($demo->category && isset($categoryMap[$demo->category])) {
                $updates['demo_category_id'] = $categoryMap[$demo->category];
            }
            if ($demo->package && isset($packageMap[$demo->package])) {
                $updates['demo_package_id'] = $packageMap[$demo->package];
            }
            if (!empty($updates)) {
                $demo->update($updates);
            }
        }

        // Add foreign key constraints
        Schema::table('demo_websites', function (Blueprint $table) {
            $table->foreign('demo_category_id')->references('id')->on('demo_categories')->nullOnDelete();
            $table->foreign('demo_package_id')->references('id')->on('demo_packages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('demo_websites', function (Blueprint $table) {
            $table->dropForeign(['demo_category_id']);
            $table->dropForeign(['demo_package_id']);
            $table->dropColumn(['demo_category_id', 'demo_package_id']);
        });
    }
};