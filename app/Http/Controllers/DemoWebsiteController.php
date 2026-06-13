<?php

namespace App\Http\Controllers;

use App\Models\DemoWebsite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DemoWebsiteController extends Controller
{
    public function publicIndex(Request $request): JsonResponse
    {
        $demos = DemoWebsite::active()
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($request->package, function ($query, $package) {
                $query->where('package', $package);
            })
            ->ordered()
            ->get()
            ->map(fn ($demo) => [
                'id' => $demo->id,
                'title' => $demo->title,
                'url' => $demo->url,
                'category' => $demo->category,
                'package' => $demo->package,
                'featured_image' => $demo->featured_image_url,
                'description' => $demo->description,
            ]);

        $categories = DemoWebsite::active()
            ->select('category')
            ->distinct()
            ->pluck('category');

        $packages = DemoWebsite::active()
            ->whereNotNull('package')
            ->select('package')
            ->distinct()
            ->pluck('package');

        return response()->json([
            'demos' => $demos,
            'categories' => $categories,
            'packages' => $packages,
        ]);
    }

    public function publicShow(DemoWebsite $demoWebsite): JsonResponse
    {
        if (! $demoWebsite->is_active) {
            abort(404);
        }

        return response()->json([
            'id' => $demoWebsite->id,
            'title' => $demoWebsite->title,
            'url' => $demoWebsite->url,
            'category' => $demoWebsite->category,
            'package' => $demoWebsite->package,
            'featured_image' => $demoWebsite->featured_image_url,
            'description' => $demoWebsite->description,
        ]);
    }
}