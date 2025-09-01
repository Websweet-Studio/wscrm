<?php

namespace App\Http\Controllers;

use App\Models\DomainPrice;
use App\Services\DomainAvailabilityService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DomainPriceController extends Controller
{
    public function index(): Response
    {
        $domainPrices = DomainPrice::active()
            ->orderBy('selling_price')
            ->get();

        return Inertia::render('Domains/Index', [
            'domainPrices' => $domainPrices,
        ]);
    }

    public function search(Request $request): Response
    {
        $domain = $request->input('domain');
        $extension = $request->input('extension', '.com');

        $domainPrice = DomainPrice::byExtension($extension)->active()->first();

        return Inertia::render('Domains/Search', [
            'domain' => $domain,
            'extension' => $extension,
            'domainPrice' => $domainPrice,
            'available' => true, // TODO: Implement domain availability check
        ]);
    }

    public function publicIndex(): Response
    {
        $domainPrices = DomainPrice::where('is_active', true)
            ->when(request('search'), function ($query, $search) {
                $query->where('extension', 'like', "%{$search}%");
            })
            ->orderBy('selling_price')
            ->get();

        return Inertia::render('Public/Domains/Index', [
            'domainPrices' => $domainPrices,
            'filters' => request()->only(['search']),
        ]);
    }

    public function publicSearch(Request $request, DomainAvailabilityService $domainService)
    {
        $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        $inputDomain = $request->domain;
        $extension = '';
        $baseDomain = $inputDomain;
        
        // Parse domain and extension
        if (str_contains($inputDomain, '.')) {
            $parts = explode('.', $inputDomain);
            $extension = end($parts);
            $baseDomain = implode('.', array_slice($parts, 0, -1));
        }

        // Get available extensions from database
        $availableExtensions = DomainPrice::where('is_active', true)
            ->orderBy('selling_price')
            ->get();

        // Check domain availability using RNA API
        $availabilityResults = [];
        
        try {
            if ($extension) {
                // Check specific domain if extension provided
                $availabilityResults[$inputDomain] = $domainService->checkAvailability($inputDomain);
            } else {
                // Check popular extensions for the base domain
                $popularExtensions = ['com', 'net', 'org', 'id', 'co.id'];
                $availabilityResults = $domainService->checkWithSuggestions($baseDomain, $popularExtensions);
            }
        } catch (\Exception $e) {
            // Log error but continue without availability data
            \Log::error('Domain availability check failed', [
                'domain' => $inputDomain,
                'error' => $e->getMessage()
            ]);
        }

        return Inertia::render('Public/Domains/Search', [
            'domain' => $baseDomain,
            'requestedDomain' => $inputDomain,
            'requestedExtension' => $extension,
            'domainPrices' => $availableExtensions,
            'availabilityResults' => $availabilityResults,
        ]);
    }

    /**
     * API endpoint for checking domain availability
     */
    public function checkAvailability(Request $request, DomainAvailabilityService $domainService)
    {
        $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        $result = $domainService->checkAvailability($request->domain);

        return response()->json($result);
    }
}
