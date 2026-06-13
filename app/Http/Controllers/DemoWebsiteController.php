<?php

namespace App\Http\Controllers;

use App\Models\DemoCategory;
use App\Models\DemoPackage;
use App\Models\DemoWebsite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DemoWebsiteController extends Controller
{
    public function publicIndex(Request $request): JsonResponse
    {
        $demos = DemoWebsite::active()
            ->with(['demoCategory', 'demoPackages'])
            ->when($request->category, function ($query, $category) {
                $query->whereHas('demoCategory', function ($q) use ($category) {
                    $q->where('slug', $category);
                });
            })
            ->when($request->package, function ($query, $package) {
                $query->whereHas('demoPackages', function ($q) use ($package) {
                    $q->where('slug', $package);
                });
            })
            ->ordered()
            ->get()
            ->map(fn($demo) => [
                'id' => $demo->id,
                'title' => $demo->title,
                'url' => $demo->url,
                'category' => $demo->demoCategory?->name,
                'category_slug' => $demo->demoCategory?->slug,
                'packages' => $demo->demoPackages->map(fn($pkg) => [
                    'id' => $pkg->id,
                    'name' => $pkg->name,
                    'slug' => $pkg->slug,
                ]),
                'featured_image' => $demo->featured_image_url,
                'description' => $demo->description,
            ]);

        $categories = DemoCategory::active()
            ->ordered()
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
            ]);

        $packages = DemoPackage::active()
            ->ordered()
            ->get()
            ->map(fn($pkg) => [
                'id' => $pkg->id,
                'name' => $pkg->name,
                'slug' => $pkg->slug,
            ]);

        return response()->json([
            'demos' => $demos,
            'categories' => $categories,
            'packages' => $packages,
        ]);
    }

    public function publicShow(DemoWebsite $demoWebsite): JsonResponse
    {
        if (!$demoWebsite->is_active) {
            abort(404);
        }

        $demoWebsite->load(['demoCategory', 'demoPackages']);

        return response()->json([
            'id' => $demoWebsite->id,
            'title' => $demoWebsite->title,
            'url' => $demoWebsite->url,
            'category' => $demoWebsite->demoCategory?->name,
            'category_slug' => $demoWebsite->demoCategory?->slug,
            'packages' => $demoWebsite->demoPackages->map(fn($pkg) => [
                'id' => $pkg->id,
                'name' => $pkg->name,
                'slug' => $pkg->slug,
            ]),
            'featured_image' => $demoWebsite->featured_image_url,
            'description' => $demoWebsite->description,
        ]);
    }
}