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
    public function index(): Response
    {
        $conversations = AiConversation::where('user_id', auth()->id())
            ->latest('updated_at')
            ->get(['id', 'title', 'updated_at']);

        return Inertia::render('Admin/Websites/AiAgent', [
            'conversations' => $conversations,
        ]);
    }

    public function show(AiConversation $conversation): JsonResponse
    {
        abort_unless($conversation->user_id === auth()->id(), 403, 'Unauthorized access.');

        return response()->json([
            'conversation' => $conversation,
            'messages' => $conversation->messages()->get(['id', 'role', 'content', 'actions', 'created_at']),
        ]);
    }

    public function destroy(AiConversation $conversation): JsonResponse
    {
        abort_unless($conversation->user_id === auth()->id(), 403, 'Unauthorized access.');

        $conversation->delete();

        return response()->json(['success' => true]);
    }

    public function chat(Request $request, AiAgentService $agent): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'nullable|integer',
            'message' => 'required|string|max:2000',
            'page' => 'nullable|array',
            'page.url' => 'nullable|string|max:500',
            'page.label' => 'nullable|string|max:200',
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
            $result = $agent->process($validated['message'], null, [], $validated['page'] ?? []);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('AiAgent chat gagal: '.$e->getMessage());

            return response()->json([
                'error' => ['message' => 'Terjadi kesalahan saat memproses chat. Coba lagi nanti.', 'type' => 'server_error'],
            ], 500);
        }

        $pendingActions = $result['pending_actions'] ?? [];
        if ($pendingActions) {
            session([self::sessionKey($conversation->id) => $pendingActions]);
        }

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'agent',
            'content' => $result['ai_response'] ?? '',
            'actions' => array_merge(
                $result['actions'] ?? [],
                array_map(fn ($p) => ['action' => $p['action'], 'params' => $p['params'], 'result' => ['pending' => true, 'message' => 'Menunggu konfirmasi']], $pendingActions)
            ),
        ]);

        return response()->json(['conversation_id' => $conversation->id] + $result);
    }

    /**
     * Chat dengan streaming SSE: kirim progress tiap tahap workflow (generate judul,
     * konten, gambar, audit, publish) real-time ke chat — tidak menunggu selesai dulu.
     */
    public function streamChat(Request $request, AiAgentService $agent)
    {
        $validated = $request->validate([
            'conversation_id' => 'nullable|integer',
            'message' => 'required|string|max:2000',
            'page' => 'nullable|array',
            'page.url' => 'nullable|string|max:500',
            'page.label' => 'nullable|string|max:200',
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

        // Riwayat percakapan sebelumnya (tanpa pesan yang baru ditulis) — dikirim ke AI
        // supaya konteks multi-langkah terjaga (misal alur "/jurnal ..." → "hari ini").
        // Dibatasi 30 pesan terakhir & 1000 karakter/pesan untuk menghemat token.
        // Selain teks, sertakan "resolved context" (website_id/id/entry_date) dari aksi
        // AI sebelumnya agar AI TIDAK lupa entitas yang sudah disepakati (mis. domain).
        $history = $conversation->messages()
            ->orderByDesc('id')
            ->take(30)
            ->get(['role', 'content', 'actions'])
            ->reverse()
            ->map(function ($m) {
                $role = $m->role === 'agent' ? 'assistant' : 'user';
                $content = mb_strimwidth((string) $m->content, 0, 1000, '…');

                if ($m->role === 'agent' && !empty($m->actions)) {
                    $resolved = $this->extractResolvedContext($m->actions);
                    if ($resolved) {
                        $content .= "\n[Konteks entitas yang sudah disepakati AI sebelumnya: "
                            . json_encode($resolved, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ']';
                    }
                }

                return ['role' => $role, 'content' => $content];
            })
            ->values()
            ->all();

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        $conversationId = $conversation->id;
        $message = $validated['message'];
        $pageContext = $validated['page'] ?? [];

        return response()->stream(function () use ($agent, $message, $conversationId, $history, $pageContext) {
            // SSE: matikan buffering & kompresi PHP (zlib/output_buffering) supaya
            // tiap event progress langsung terkirim, tidak menumpuk sampai respons selesai.
            ini_set('output_buffering', 'off');
            ini_set('zlib.output_compression', 'off');

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
                }, $history, $pageContext);
            } catch (\Exception $e) {
                $result = [
                    'success' => false,
                    'ai_response' => 'Maaf, terjadi error: ' . $e->getMessage(),
                    'actions' => [],
                    'pending_actions' => [],
                ];
            }

            // Simpan aksi berisiko tinggi menunggu konfirmasi ke session (server-side, anti-manipulasi)
            $pendingActions = $result['pending_actions'] ?? [];
            if ($pendingActions) {
                session([self::sessionKey($conversationId) => $pendingActions]);
            }

            AiMessage::create([
                'conversation_id' => $conversationId,
                'role' => 'agent',
                'content' => $result['ai_response'] ?? '',
                'actions' => array_merge(
                    $result['actions'] ?? [],
                    array_map(fn ($p) => ['action' => $p['action'], 'params' => $p['params'], 'result' => ['pending' => true, 'message' => 'Menunggu konfirmasi']], $pendingActions)
                ),
            ]);

            $send([
                'type' => 'done',
                'conversation_id' => $conversationId,
                'ai_response' => $result['ai_response'] ?? '',
                'actions' => $result['actions'] ?? [],
                'pending_actions' => $pendingActions,
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

    /**
     * Konfirmasi & eksekusi aksi berisiko tinggi yang sebelumnya ditunda (pending).
     * Aksi dibaca dari session, bukan dari body request — client tidak bisa memanipulasi.
     */
    public function confirmActions(Request $request, AiAgentService $agent)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|integer',
        ]);

        $conversation = AiConversation::find($validated['conversation_id']);
        abort_unless($conversation && $conversation->user_id === auth()->id(), 403, 'Unauthorized access.');

        $conversationId = $conversation->id;
        $sessionKey = self::sessionKey($conversationId);
        $pendingActions = session($sessionKey, []);

        // Fallback bila session tidak tersimpan (mis. request streaming): baca dari
        // pesan AI terakhir yang berisi aksi berstatus pending. Data berasal dari DB,
        // bukan dari client — tetap aman.
        if (!$pendingActions) {
            $lastAgentMessage = $conversation->messages()
                ->where('role', 'agent')
                ->latest('id')
                ->first();

            $pendingActions = collect($lastAgentMessage->actions ?? [])
                ->filter(fn ($a) => !empty($a['result']['pending']))
                ->map(fn ($a) => ['action' => $a['action'], 'params' => $a['params']])
                ->values()
                ->all();
        }

        return response()->stream(function () use ($agent, $conversationId, $pendingActions, $sessionKey) {
            ini_set('output_buffering', 'off');
            ini_set('zlib.output_compression', 'off');

            $send = function (array $payload) {
                echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            };

            set_time_limit(300);

            $send(['type' => 'start', 'conversation_id' => $conversationId]);

            if (!$pendingActions) {
                $send(['type' => 'done', 'conversation_id' => $conversationId, 'ai_response' => 'Tidak ada aksi menunggu konfirmasi.', 'actions' => []]);
                echo "data: [DONE]\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();

                return;
            }

            try {
                $executed = $agent->executePendingActions($pendingActions, function (string $progressMessage, string $status = 'done', string $agentName = '') use ($send) {
                    $send(['type' => 'progress', 'message' => $progressMessage, 'status' => $status, 'agent' => $agentName]);
                });
            } catch (\Exception $e) {
                $executed = [['action' => 'unknown', 'params' => [], 'result' => ['error' => $e->getMessage()]]];
            }

            $lines = [];
            foreach ($executed as $r) {
                $res = $r['result'];
                if (isset($res['error'])) {
                    $lines[] = '[GAGAL] ' . $r['action'] . ': ' . $res['error'];
                } else {
                    $lines[] = '[OK] ' . ($res['message'] ?? 'Aksi ' . $r['action'] . ' selesai');
                }
            }
            $message = "Aksi dikonfirmasi dan dijalankan:\n\n" . implode("\n", $lines);

            AiMessage::create([
                'conversation_id' => $conversationId,
                'role' => 'agent',
                'content' => $message,
                'actions' => $executed,
            ]);

            session()->forget($sessionKey);

            $send([
                'type' => 'done',
                'conversation_id' => $conversationId,
                'ai_response' => $message,
                'actions' => $executed,
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

    /**
     * Batalkan aksi berisiko tinggi yang menunggu konfirmasi.
     */
    public function cancelActions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'required|integer',
        ]);

        $conversation = AiConversation::find($validated['conversation_id']);
        abort_unless($conversation && $conversation->user_id === auth()->id(), 403, 'Unauthorized access.');

        session()->forget(self::sessionKey($conversation->id));

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'agent',
            'content' => 'Aksi dibatalkan.',
            'actions' => [],
        ]);

        return response()->json(['success' => true]);
    }

    private static function sessionKey(int $conversationId): string
    {
        return 'ai_pending_actions_' . $conversationId;
    }

    /**
     * Ekstrak entitas yang sudah disepakati AI dari aksi-aksi sebelumnya, supaya
     * konteks multi-langkah tidak hilang (mis. domain sudah disebut → AI tidak
     * minta lagi saat user hanya menyebut judul).
     */
    private function extractResolvedContext(array $actions): array
    {
        $ctx = [];

        foreach ($actions as $a) {
            $params = $a['params'] ?? [];

            if (!empty($params['website_id'])) {
                $ctx['website_id'] = $params['website_id'];
            }
            if (!empty($params['website_client_id'])) {
                $ctx['website_client_id'] = $params['website_client_id'];
            }
            if (!empty($params['id'])) {
                $ctx['id'] = $params['id'];
            }
            if (!empty($params['entry_date'])) {
                $ctx['entry_date'] = $params['entry_date'];
            }
            if (!empty($params['title'])) {
                $ctx['title'] = $params['title'];
            }
        }

        return $ctx;
    }
}
