<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AiAgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AiAgentController extends Controller
{
    private function checkAdmin(): void
    {
        if (!auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function index(): Response
    {
        $this->checkAdmin();

        return Inertia::render('Admin/Websites/AiAgent');
    }

    public function chat(Request $request, AiAgentService $agent): JsonResponse
    {
        $this->checkAdmin();

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        try {
            $result = $agent->process($request->input('message'));
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'ai_response' => 'Maaf, terjadi error: ' . $e->getMessage(),
                'actions' => [],
            ], 500);
        }
    }
}
