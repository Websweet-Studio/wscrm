<?php

namespace App\Http\Controllers;

use App\Models\DemoCategory;
use App\Models\DemoPackage;
use App\Models\DemoWebsite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DemoWebsiteController extends Controller
{
    public function publicPage(Request $request): Response
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
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->ordered()
            ->paginate(12)
            ->through(fn($demo) => [
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

        return Inertia::render('Public/Demos/Index', [
            'demos' => $demos,
            'categories' => $categories,
            'packages' => $packages,
            'filters' => [
                'search' => $request->search,
                'category' => $request->category,
                'package' => $request->package,
            ],
        ]);
    }

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

    /**
     * Render a clean embed view for a demo website (iframe-friendly).
     */
    public function embed(Request $request, $id)
    {
        $demo = DemoWebsite::active()
            ->with(['demoCategory', 'demoPackages'])
            ->findOrFail($id);

        $width = $request->input('width', 800);
        $height = $request->input('height', 600);

        return view('demos.embed', [
            'demo' => $demo,
            'width' => min((int) $width, 1200),
            'height' => min((int) $height, 900),
        ]);
    }

    /**
     * oEmbed JSON endpoint for third-party embed discovery.
     */
    public function oembed(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|string',
        ]);

        $url = $request->input('url');
        $maxwidth = $request->input('maxwidth', 800);
        $maxheight = $request->input('maxheight', 600);

        // Parse demo ID from URL like /demo-web/embed/{id}
        if (!preg_match('#/demo-web/embed/(\d+)#', $url, $matches)) {
            return response()->json(['error' => 'Invalid URL'], 404);
        }

        $demo = DemoWebsite::active()->with(['demoCategory', 'demoPackages'])->find($matches[1]);

        if (!$demo) {
            return response()->json(['error' => 'Demo not found'], 404);
        }

        $embedUrl = config('app.url') . "/demo-web/embed/{$demo->id}";

        return response()->json([
            'version' => '1.0',
            'type' => 'rich',
            'title' => $demo->title,
            'author_name' => config('app.name'),
            'provider_name' => config('app.name'),
            'provider_url' => config('app.url'),
            'width' => min((int) $maxwidth, 1200),
            'height' => min((int) $maxheight, 900),
            'html' => '<iframe src="' . $embedUrl . '" width="' . min((int) $maxwidth, 1200) . '" height="' . min((int) $maxheight, 900) . '" frameborder="0" allowfullscreen style="max-width:100%;border:1px solid #e8e6dc;border-radius:12px;overflow:hidden;"></iframe>',
            'thumbnail_url' => $demo->featured_image_url,
            'thumbnail_width' => 800,
            'thumbnail_height' => 450,
        ]);
    }
}
