<?php

namespace App\Http\Controllers;

use App\Models\DemoCategory;
use App\Models\DemoPackage;
use App\Models\DemoWebsite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DemoWebsiteController extends Controller
{
    public function publicPage(Request $request): InertiaResponse
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
     * Render a clean embed view for a single demo website (iframe-friendly).
     */
    public function embed(Request $request, $id)
    {
        $demo = DemoWebsite::active()
            ->with(['demoCategory', 'demoPackages'])
            ->findOrFail($id);

        return view('demos.embed-single', [
            'demo' => $demo,
        ]);
    }

    /**
     * Render a full embeddable listing page with filters, pagination, and preview (iframe-friendly).
     */
    public function embedListing(Request $request)
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
            ->paginate(9)
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

        return view('demos.embed-listing', [
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

    /**
     * oEmbed JSON endpoint for third-party embed discovery.
     */
    public function oembed(Request $request): JsonResponse
    {
        $maxwidth = $request->input('maxwidth', 800);
        $maxheight = $request->input('maxheight', 900);

        $appName = config('app.name');
        $appUrl = config('app.url');
        $embedUrl = $appUrl . '/demo-web/embed';

        return response()->json([
            'version' => '1.0',
            'type' => 'rich',
            'title' => 'Demo Website',
            'author_name' => $appName,
            'provider_name' => $appName,
            'provider_url' => $appUrl,
            'width' => min((int) $maxwidth, 1200),
            'height' => min((int) $maxheight, 900),
            'html' => '<iframe src="' . $embedUrl . '" width="' . min((int) $maxwidth, 1200) . '" height="' . min((int) $maxheight, 900) . '" frameborder="0" allowfullscreen style="max-width:100%;border:1px solid #e8e6dc;border-radius:12px;overflow:hidden;"></iframe>',
        ]);
    }

    /**
     * Serve the embed JS widget that client/reseller can paste on their website.
     * Data is rendered server-side into the JS output (no CORS/XHR needed).
     */
    public function embedJs(Request $request)
    {
        $demosData = DemoWebsite::active()
            ->with(['demoCategory', 'demoPackages'])
            ->ordered()
            ->limit(50)
            ->get()
            ->map(fn($demo) => [
                'title' => $demo->title,
                'url' => $demo->url,
                'category' => $demo->demoCategory?->name,
                'description' => $demo->description,
                'featured_image' => $demo->featured_image_url,
                'packages' => $demo->demoPackages->map(fn($pkg) => ['name' => $pkg->name]),
            ]);

        $demosJson = json_encode($demosData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $js = <<<'JS'
(function() {
    'use strict';

    var WSS_DEMOS = DEMOS_JSON_PLACEHOLDER;

    var CONTAINER = document.getElementById('wss-demo-widget');
    if (!CONTAINER) return;

    var LIMIT = parseInt(CONTAINER.getAttribute('data-limit')) || 6;
    var PRIMARY = CONTAINER.getAttribute('data-primary') || '#c96442';
    var BG = CONTAINER.getAttribute('data-bg') || '#faf9f5';
    var CARD_BG = CONTAINER.getAttribute('data-card-bg') || '#ffffff';
    var TEXT = CONTAINER.getAttribute('data-text') || '#141413';
    var TEXT_SEC = CONTAINER.getAttribute('data-text-secondary') || '#5e5d59';
    var BORDER = CONTAINER.getAttribute('data-border') || '#f0eee6';

    var style = document.createElement('style');
    style.textContent =
        '#wss-demo-widget *{margin:0;padding:0;box-sizing:border-box}'+
        '#wss-demo-widget{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}'+
        '.wss-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px}'+
        '.wss-card{background:'+CARD_BG+';border:1px solid '+BORDER+';border-radius:14px;overflow:hidden;transition:box-shadow .2s}'+
        '.wss-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.08)}'+
        '.wss-card-img{position:relative;aspect-ratio:16/9;background:#e8e6dc;overflow:hidden}'+
        '.wss-card-img img{width:100%;height:100%;object-fit:cover}'+
        '.wss-card-img .wss-ph{display:flex;align-items:center;justify-content:center;width:100%;height:100%;color:#b0aea5;font-size:32px}'+
        '.wss-badge{position:absolute;top:8px;left:8px;background:'+PRIMARY+';color:#fff;padding:2px 10px;border-radius:6px;font-size:11px;font-weight:600}'+
        '.wss-card-body{padding:14px}'+
        '.wss-card-body h3{font-size:15px;font-weight:600;font-family:Georgia,serif;color:'+TEXT+';margin-bottom:6px}'+
        '.wss-card-body p{font-size:12px;color:'+TEXT_SEC+';margin-bottom:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}'+
        '.wss-pkg{display:inline-block;border:1px solid '+BORDER+';color:'+TEXT_SEC+';padding:1px 7px;border-radius:5px;font-size:10px;margin:2px}'+
        '.wss-action{display:flex;gap:8px;margin-top:10px}'+
        '.wss-btn{display:inline-flex;align-items:center;justify-content:center;gap:4px;padding:7px 14px;border-radius:10px;font-size:13px;font-weight:500;text-decoration:none;transition:all .2s;border:none;cursor:pointer}'+
        '.wss-btn-p{background:'+PRIMARY+';color:#fff;flex:1}'+
        '.wss-btn-p:hover{filter:brightness(.9)}'+
        '.wss-header{margin-bottom:16px;text-align:center}'+
        '.wss-header h2{font-size:24px;font-weight:600;font-family:Georgia,serif;color:'+TEXT+';margin-bottom:8px}'+
        '.wss-header p{font-size:14px;color:'+TEXT_SEC+'}'+
        '.wss-empty{text-align:center;padding:48px 20px;color:'+TEXT_SEC+'}'+
        '.wss-empty-icon{font-size:40px;color:#b0aea5;margin-bottom:12px}';

    document.head.appendChild(style);

    // Title
    var title = CONTAINER.getAttribute('data-title');
    if (title) {
        var hdr = document.createElement('div');
        hdr.className = 'wss-header';
        hdr.innerHTML = '<h2>' + title + '</h2>' + (CONTAINER.getAttribute('data-subtitle') ? '<p>' + CONTAINER.getAttribute('data-subtitle') + '</p>' : '');
        CONTAINER.appendChild(hdr);
    }

    var demos = WSS_DEMOS.slice(0, LIMIT);
    if (demos.length === 0) {
        var empty = document.createElement('div');
        empty.className = 'wss-empty';
        empty.innerHTML = '<div class="wss-empty-icon">&#128187;</div><p>Belum ada demo tersedia.</p>';
        CONTAINER.appendChild(empty);
        return;
    }

    var grid = document.createElement('div');
    grid.className = 'wss-grid';

    var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#b0aea5" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>';

    demos.forEach(function(d) {
        var card = document.createElement('div');
        card.className = 'wss-card';

        var img = d.featured_image
            ? '<img src="' + d.featured_image + '" alt="' + d.title + '" loading="lazy">'
            : '<div class="wss-ph">' + svg + '</div>';

        var badge = d.category ? '<span class="wss-badge">' + d.category + '</span>' : '';
        var pkgs = '';
        if (d.packages) {
            d.packages.forEach(function(p) { pkgs += '<span class="wss-pkg">' + p.name + '</span>'; });
        }
        var desc = d.description ? '<p>' + d.description + '</p>' : '';

        card.innerHTML =
            '<div class="wss-card-img">' + img + badge + '</div>' +
            '<div class="wss-card-body">' +
                '<h3>' + d.title + '</h3>' +
                desc +
                '<div>' + pkgs + '</div>' +
                '<div class="wss-action">' +
                    '<a href="' + d.url + '" target="_blank" class="wss-btn wss-btn-p">Lihat Demo</a>' +
                '</div>' +
            '</div>';

        grid.appendChild(card);
    });

    CONTAINER.appendChild(grid);
})();
JS;

        $js = str_replace('DEMOS_JSON_PLACEHOLDER', $demosJson, $js);

        return response($js)
            ->header('Content-Type', 'application/javascript')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
