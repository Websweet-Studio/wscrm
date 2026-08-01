<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
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

        $conversations = AiConversation::where('user_id', auth()->id())
            ->latest('updated_at')
            ->get(['id', 'title', 'updated_at']);

        return Inertia::render('Admin/Websites/AiAgent', [
            'conversations' => $conversations,
        ]);
    }

    public function show(AiConversation $conversation): JsonResponse
    {
        $this->checkAdmin();
        abort_unless($conversation->user_id === auth()->id(), 403, 'Unauthorized access.');

        return response()->json([
            'conversation' => $conversation,
            'messages' => $conversation->messages()->get(['id', 'role', 'content', 'actions', 'created_at']),
        ]);
    }

    public function destroy(AiConversation $conversation): JsonResponse
    {
        $this->checkAdmin();
        abort_unless($conversation->user_id === auth()->id(), 403, 'Unauthorized access.');

        $conversation->delete();

        return response()->json(['success' => true]);
    }

    public function chat(Request $request, AiAgentService $agent): JsonResponse
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'conversation_id' => 'nullable|integer',
            'message' => 'required|string|max:2000',
        ]);

        // Reuse existing conversation, or create a new one on first message
        $conversation = null;
        if (!empty($validated['conversation_id'])) {
            $conversation = AiConversation::find($validated['conversation_id']);
            abort_unless($conversation && $conversation->user_id === auth()->id(), 403, 'Unauthorized access.');
        }
        if (!$conversation) {
            $conversation = AiConversation::create([
                'user_id' => auth()->id(),
                'title' => mb_strimwidth($validated['message'], 0, 50),
            ]);
        }

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        try {
            $result = $agent->process($validated['message']);
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'ai_response' => 'Maaf, terjadi error: ' . $e->getMessage(),
                'actions' => [],
            ];
        }

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'agent',
            'content' => $result['ai_response'] ?? '',
            'actions' => $result['actions'] ?? [],
        ]);

        return response()->json(['conversation_id' => $conversation->id] + $result);
    }

    /**
     * Chat dengan streaming SSE: kirim progress tiap tahap workflow (generate judul,
     * konten, gambar, audit, publish) real-time ke chat — tidak menunggu selesai dulu.
     */
    public function streamChat(Request $request, AiAgentService $agent)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'conversation_id' => 'nullable|integer',
            'message' => 'required|string|max:2000',
        ]);

        $conversation = null;
        if (!empty($validated['conversation_id'])) {
            $conversation = AiConversation::find($validated['conversation_id']);
            abort_unless($conversation && $conversation->user_id === auth()->id(), 403, 'Unauthorized access.');
        }
        if (!$conversation) {
            $conversation = AiConversation::create([
                'user_id' => auth()->id(),
                'title' => mb_strimwidth($validated['message'], 0, 50),
            ]);
        }

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        $conversationId = $conversation->id;
        $message = $validated['message'];

        return response()->stream(function () use ($agent, $message, $conversationId) {
            $send = function (array $payload) {
                echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            };

            set_time_limit(300);

            $send(['type' => 'start', 'conversation_id' => $conversationId]);

            try {
                $result = $agent->process($message, function (string $progressMessage, string $status = 'done', string $agentName = '') use ($send) {
                    $send(['type' => 'progress', 'message' => $progressMessage, 'status' => $status, 'agent' => $agentName]);
                });
            } catch (\Exception $e) {
                $result = [
                    'success' => false,
                    'ai_response' => 'Maaf, terjadi error: ' . $e->getMessage(),
                    'actions' => [],
                ];
            }

            AiMessage::create([
                'conversation_id' => $conversationId,
                'role' => 'agent',
                'content' => $result['ai_response'] ?? '',
                'actions' => $result['actions'] ?? [],
            ]);

            $send([
                'type' => 'done',
                'conversation_id' => $conversationId,
                'ai_response' => $result['ai_response'] ?? '',
                'actions' => $result['actions'] ?? [],
            ]);

            echo "data: [DONE]\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }
}
