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
     * Supports pagination via data-per-page attribute.
     */
    public function embedJs(Request $request)
    {
        $demosData = DemoWebsite::active()
            ->with(['demoCategory', 'demoPackages'])
            ->ordered()
            ->limit(100)
            ->get()
            ->map(fn($demo) => [
                'title' => $demo->title,
                'url' => $demo->url,
                'category' => $demo->demoCategory?->name,
                'category_slug' => $demo->demoCategory?->slug,
                'description' => $demo->description,
                'featured_image' => $demo->featured_image_url,
                'packages' => $demo->demoPackages->map(fn($pkg) => ['name' => $pkg->name]),
            ]);

        $demosJson = json_encode($demosData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $js = <<<'JS'
(function() {
    'use strict';

    var ALL_DEMOS = DEMOS_JSON_PLACEHOLDER;

    var C = document.getElementById('wss-demo-widget');
    if (!C) return;

    var LIMIT = parseInt(C.getAttribute('data-limit')) || 0;
    var CATEGORY = C.getAttribute('data-category') || '';
    var PER_PAGE = parseInt(C.getAttribute('data-per-page')) || 6;
    var PRIMARY = C.getAttribute('data-primary') || '#c96442';
    var BG = C.getAttribute('data-bg') || '#faf9f5';
    var CARD_BG = C.getAttribute('data-card-bg') || '#ffffff';
    var TEXT = C.getAttribute('data-text') || '#141413';
    var TEXT2 = C.getAttribute('data-text-secondary') || '#5e5d59';
    var BDR = C.getAttribute('data-border') || '#f0eee6';
    var WHATSAPP = C.getAttribute('data-whatsapp') || '';

    var demos = ALL_DEMOS;
    if (LIMIT > 0) demos = demos.slice(0, LIMIT);
    if (CATEGORY) demos = demos.filter(function(d) { return d.category_slug === CATEGORY; });
    var filteredDemos = demos;
    var totalPages = Math.ceil(filteredDemos.length / PER_PAGE);
    var currentPage = 1;

    // Inject styles
    var s = document.createElement('style');
    s.textContent =
    /* Reset & Base */
    '#wss-demo-widget{box-sizing:border-box}' +
    '#wss-demo-widget{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",sans-serif;line-height:1.6;color:'+TEXT+'}' +

    /* Header */
    '.wss-hdr{text-align:center;margin-bottom:28px;padding:0 16px}' +
    '.wss-hdr h2{font-size:28px;font-weight:700;font-family:Georgia,"Times New Roman",serif;color:'+TEXT+';margin-bottom:10px;letter-spacing:-.02em}' +
    '.wss-hdr p{font-size:15px;color:'+TEXT2+';max-width:540px;margin:0 auto;line-height:1.7}' +

    /* Grid */
    '.wss-grd{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:22px;padding:0 4px}' +

    /* Card */
    '.wss-crd{background:'+CARD_BG+';border:1px solid '+BDR+';border-radius:16px;overflow:hidden;transition:transform .25s ease,box-shadow .25s ease;display:flex;flex-direction:column}' +
    '.wss-crd:hover{transform:translateY(-3px);box-shadow:0 12px 36px rgba(0,0,0,.08)}' +

    /* Card image */
    '.wss-cimg{position:relative;aspect-ratio:16/10;background:#eae8df;overflow:hidden}' +
    '.wss-cimg img{width:100%;height:100%;object-fit:cover;transition:transform .35s ease}' +
    '.wss-crd:hover .wss-cimg img{transform:scale(1.05)}' +
    '.wss-cimg .wss-ph{display:flex;align-items:center;justify-content:center;width:100%;height:100%;color:#c8c5bb}' +

    /* Badge */
    '.wss-bdg{position:absolute;top:12px;left:12px;background:'+PRIMARY+';color:#fff;padding:4px 12px;border-radius:8px;font-size:11px;font-weight:600;letter-spacing:.03em;box-shadow:0 2px 8px rgba(0,0,0,.12)}' +

    /* Card body */
    '.wss-cbdy{padding:18px 20px 20px;display:flex;flex-direction:column;flex:1;gap:0}' +
    '.wss-cbdy h3{font-size:17px;font-weight:700;font-family:Georgia,serif;color:'+TEXT+';margin-bottom:8px;line-height:1.4;letter-spacing:-.01em;padding:0}' +
    '.wss-cbdy>p{font-size:13px;color:'+TEXT2+';margin-bottom:12px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;flex:0;line-height:1.65}' +

    /* Packages */
    '.wss-pkgs{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px}' +
    '.wss-pkg{display:inline-flex;align-items:center;border:1px solid '+BDR+';color:'+TEXT2+';padding:3px 10px;border-radius:6px;font-size:11px;font-weight:500;letter-spacing:.01em}' +

    /* Button area */
    '.wss-act{margin-top:auto;display:flex;gap:8px;padding-top:4px}' +

    /* Button base */
    '.wss-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:12px 20px;border-radius:12px;font-size:14px;font-weight:600;text-decoration:none;transition:all .2s ease;border:none;cursor:pointer;flex:1;line-height:1.4}' +

    /* Primary button */
    '.wss-bp{background:'+PRIMARY+';color:#fff;padding:12px 20px;}' +
    '.wss-bp:hover{filter:brightness(1.08);box-shadow:0 6px 20px rgba(0,0,0,.15)}' +
    '.wss-bp:active{transform:scale(0.98);filter:brightness(0.95)}' +
    '.wss-bp svg{width:14px;height:14px;flex-shrink:0}' +

    /* Info text */
    '.wss-info{text-align:center;margin-top:14px;font-size:13px;color:'+TEXT2+';opacity:.75}' +

    /* Pagination */
    '.wss-pag{text-align:center;margin-top:28px;display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap}' +
    '.wss-pag a,.wss-pag span{display:inline-flex;align-items:center;justify-content:center;min-width:40px;height:40px;padding:0 12px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid '+BDR+';color:'+TEXT+';background:'+CARD_BG+';transition:all .2s ease;cursor:pointer;-webkit-user-select:none;user-select:none;gap:4px;line-height:1}' +
    '.wss-pag a:hover{background:'+BDR+';border-color:#d5d3ca}' +
    '.wss-pag .wss-act-pg{background:'+PRIMARY+';color:#fff;border-color:'+PRIMARY+';cursor:default;box-shadow:0 2px 10px rgba(0,0,0,.1)}' +
    '.wss-pag .wss-dsb{opacity:.35;cursor:default;pointer-events:none}' +

    /* Empty */
    '.wss-empty{text-align:center;padding:64px 24px;color:'+TEXT2+'}' +
    '.wss-empty svg{width:48px;height:48px;color:#c8c5bb;margin-bottom:14px}' +
    '.wss-empty h3{font-size:18px;font-weight:600;font-family:Georgia,serif;color:'+TEXT+';margin-bottom:8px}' +
    '.wss-empty p{font-size:14px;line-height:1.6}' +

    /* Fullscreen overlay */
    '.wss-overlay{position:fixed;top:0;left:0;width:100%;height:100%;z-index:999999;background:rgba(0,0,0,.92);display:flex;flex-direction:column;align-items:stretch}' +
    '.wss-overlay-bar{display:flex;align-items:center;justify-content:space-between;padding:10px 16px;background:#111;flex-shrink:0;gap:10px}' +
    '.wss-overlay-title{color:#fff;font-size:14px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}' +
    '.wss-overlay-actions{display:flex;gap:8px;flex-shrink:0}' +
    '.wss-ov-close{background:rgba(255,255,255,.15);color:#fff;border:none;width:36px;height:36px;border-radius:8px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:20px;transition:background .2s;line-height:1}' +
    '.wss-ov-close:hover{background:rgba(255,255,255,.3)}' +
    '.wss-ov-wa{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;background:#25D366;color:#fff;font-size:13px;font-weight:600;text-decoration:none;border:none;cursor:pointer;transition:filter .2s;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}' +
    '.wss-ov-wa:hover{filter:brightness(1.1)}' +
    '.wss-ov-wa svg{width:16px;height:16px;flex-shrink:0}' +
    '.wss-overlay iframe{flex:1;border:none;width:100%;height:100%;background:#fff}' +

    /* Category filter */
    '.wss-flt{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin-bottom:24px;padding:0 4px}' +
    '.wss-flt-btn{display:inline-flex;align-items:center;padding:8px 18px;border-radius:10px;font-size:13px;font-weight:500;text-decoration:none;border:1px solid '+BDR+';color:'+TEXT2+';background:'+CARD_BG+';cursor:pointer;transition:all .2s ease;-webkit-user-select:none;user-select:none;line-height:1.4}' +
    '.wss-flt-btn:hover{background:'+BDR+';border-color:#d5d3ca}' +
    '.wss-flt-btn.wss-flt-act{background:'+PRIMARY+';color:#fff;border-color:'+PRIMARY+';box-shadow:0 2px 10px rgba(0,0,0,.1)}' +

    /* Responsive */
'@media(max-width:640px){' +
      '.wss-grd{grid-template-columns:1fr;gap:16px}' +
      '.wss-hdr h2{font-size:22px}' +
      '.wss-hdr p{font-size:13px}' +
      '.wss-cbdy{padding:14px 16px 16px}' +
      '.wss-cbdy h3{font-size:15px}' +
      '.wss-btn{padding:10px 16px;font-size:13px}' +
      '.wss-pag{gap:4px}' +
      '.wss-pag a,.wss-pag span{min-width:36px;height:36px;font-size:12px}' +
      '.wss-flt{gap:6px}' +
      '.wss-flt-btn{padding:6px 14px;font-size:12px}' +
    '}';

    document.head.appendChild(s);

    // Category filter (only when showing all categories)
    var filterBar;
    if (!CATEGORY) {
        var categories = [];
        demos.forEach(function(d) {
            if (d.category_slug && categories.every(function(c) { return c.slug !== d.category_slug; })) {
                categories.push({ slug: d.category_slug, name: d.category });
            }
        });

        if (categories.length > 1) {
            filterBar = document.createElement('div');
            filterBar.className = 'wss-flt';
            C.appendChild(filterBar);

            var activeSlug = '';

            function filterByCategory(slug) {
                activeSlug = slug;
                filteredDemos = slug ? demos.filter(function(d) { return d.category_slug === slug; }) : demos;
                currentPage = 1;
                totalPages = Math.ceil(filteredDemos.length / PER_PAGE);
                renderFilters();
                render();
            }

            function renderFilters() {
                filterBar.innerHTML = '';
                var allBtn = document.createElement('button');
                allBtn.className = 'wss-flt-btn' + (activeSlug === '' ? ' wss-flt-act' : '');
                allBtn.textContent = 'Semua';
                allBtn.onclick = function() { filterByCategory(''); };
                filterBar.appendChild(allBtn);

                categories.forEach(function(cat) {
                    var btn = document.createElement('button');
                    btn.className = 'wss-flt-btn' + (activeSlug === cat.slug ? ' wss-flt-act' : '');
                    btn.textContent = cat.name;
                    btn.onclick = function() { filterByCategory(cat.slug); };
                    filterBar.appendChild(btn);
                });
            }

            renderFilters();
        }
    }

    if (demos.length === 0) {
        C.innerHTML += '<div class="wss-empty"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg><h3>Belum Ada Demo</h3><p>Belum ada demo website yang tersedia saat ini.</p></div>';
        return;
    }

    var grid = document.createElement('div');
    grid.className = 'wss-grd';
    C.appendChild(grid);

    grid.addEventListener('click', function(e) {
        var btn = e.target.closest('.wss-open-demo');
        if (!btn) return;
        e.preventDefault();
        openDemo(btn.getAttribute('data-url'), btn.getAttribute('data-title'));
    });

    var info = document.createElement('div');
    info.className = 'wss-info';
    C.appendChild(info);

    var pag = document.createElement('div');
    pag.className = 'wss-pag';
    C.appendChild(pag);

    var extSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>';
    var phSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>';
    var leftSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>';
    var rightSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>';

    function esc(str) {
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function truncate(str, limit) {
        if (!str) return '';
        if (str.length <= limit) return str;
        return str.substring(0, limit) + '...';
    }

    var waSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';

    function openDemo(url, title) {
        var overlay = document.createElement('div');
        overlay.className = 'wss-overlay';

        var waNumber = WHATSAPP.replace(/^0/, '62').replace(/[^0-9]/g, '');
        var waMsg = encodeURIComponent('Halo, saya tertarik dengan desain website "' + title + '". Bisa info lebih lanjut?');
        var waUrl = 'https://wa.me/' + waNumber + '?text=' + waMsg;

        overlay.innerHTML =
            '<div class="wss-overlay-bar">' +
                '<span class="wss-overlay-title">' + esc(title) + '</span>' +
                '<div class="wss-overlay-actions">' +
                    (WHATSAPP ? '<a href="' + waUrl + '" target="_blank" rel="noopener" class="wss-ov-wa">' + waSvg + ' Order Desain Ini</a>' : '') +
                    '<button class="wss-ov-close" title="Tutup">&times;</button>' +
                '</div>' +
            '</div>' +
            '<iframe src="' + esc(url) + '" allowfullscreen></iframe>';

        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';

        function closeOverlay() {
            overlay.remove();
            document.body.style.overflow = '';
            document.removeEventListener('keydown', onKey);
        }

        function onKey(e) { if (e.key === 'Escape') closeOverlay(); }
        document.addEventListener('keydown', onKey);

        overlay.querySelector('.wss-ov-close').onclick = closeOverlay;
    }

    function render() {
        var start = (currentPage - 1) * PER_PAGE;
        var end = start + PER_PAGE;
        var pageDemos = filteredDemos.slice(start, end);

        // Info
        info.textContent = filteredDemos.length > 0
            ? 'Menampilkan ' + (start + 1) + '-' + Math.min(end, filteredDemos.length) + ' dari ' + filteredDemos.length + ' demo'
            : '';

        // Grid
        grid.innerHTML = '';
        if (filteredDemos.length === 0) {
            grid.innerHTML = '<div class="wss-empty"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg><h3>Tidak Ada Demo</h3><p>Tidak ada demo dalam kategori ini.</p></div>';
            return;
        }
        pageDemos.forEach(function(d) {
            var img = d.featured_image
                ? '<img src="' + esc(d.featured_image) + '" alt="' + esc(d.title) + '" loading="lazy">'
                : '<div class="wss-ph">' + phSvg + '</div>';

            var badge = d.category ? '<span class="wss-bdg">' + esc(d.category) + '</span>' : '';

            var pkgs = '';
            if (d.packages && d.packages.length) {
                d.packages.forEach(function(p) { pkgs += '<span class="wss-pkg">' + esc(p.name) + '</span>'; });
            }

            var desc = d.description ? '<p>' + esc(d.description) + '</p>' : '';

            grid.innerHTML +=
                '<div class="wss-crd">' +
                    '<div class="wss-cimg">' + img + badge + '</div>' +
                    '<div class="wss-cbdy">' +
                        '<h3>' + esc(truncate(d.title, 30)) + '</h3>' +
                        (pkgs ? '<div class="wss-pkgs">' + pkgs + '</div>' : '') +
                        '<div class="wss-act">' +
                            '<a href="javascript:void(0)" class="wss-btn wss-bp wss-open-demo" data-url="' + esc(d.url) + '" data-title="' + esc(d.title) + '">' + extSvg + ' Lihat Demo</a>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        });

        // Pagination
        pag.innerHTML = '';
        if (totalPages <= 1) return;

        // Prev
        var prev = document.createElement(currentPage === 1 ? 'span' : 'a');
        prev.innerHTML = leftSvg + ' Prev';
        if (currentPage === 1) { prev.className = 'wss-dsb'; }
        else {
            prev.href = 'javascript:void(0)';
            prev.onclick = function() { goTo(currentPage - 1); };
        }
        prev.style.display = 'inline-flex';
        prev.style.gap = '4px';
        prev.style.fontSize = '12px';
        pag.appendChild(prev);

        // Page numbers
        for (var i = 1; i <= totalPages; i++) {
            if (totalPages <= 7 || i === 1 || i === totalPages || Math.abs(i - currentPage) <= 1) {
                var el = document.createElement(i === currentPage ? 'span' : 'a');
                el.textContent = i;
                if (i === currentPage) { el.className = 'wss-act-pg'; }
                else {
                    el.href = 'javascript:void(0)';
                    el.onclick = (function(page) { return function() { goTo(page); }; })(i);
                }
                pag.appendChild(el);
            } else if (totalPages > 7 && (i === currentPage - 2 || i === currentPage + 2)) {
                var dots = document.createElement('span');
                dots.textContent = '...';
                dots.style.border = 'none';
                dots.style.cursor = 'default';
                dots.style.color = TEXT2;
                pag.appendChild(dots);
            }
        }

        // Next
        var next = document.createElement(currentPage === totalPages ? 'span' : 'a');
        next.innerHTML = 'Next ' + rightSvg;
        if (currentPage === totalPages) { next.className = 'wss-dsb'; }
        else {
            next.href = 'javascript:void(0)';
            next.onclick = function() { goTo(currentPage + 1); };
        }
        next.style.display = 'inline-flex';
        next.style.gap = '4px';
        next.style.fontSize = '12px';
        pag.appendChild(next);
    }

    function goTo(page) {
        currentPage = page;
        render();
        C.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    render();
})();
JS;

        $js = str_replace('DEMOS_JSON_PLACEHOLDER', $demosJson, $js);

        return response($js)
            ->header('Content-Type', 'application/javascript')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
