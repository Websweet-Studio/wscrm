<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiCredit;
use App\Models\AiMessage;
use App\Models\AiPackage;
use App\Models\Invoice;
use App\Services\AiGateway;
use App\Services\InvoiceGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AiController extends Controller
{
    private const SYSTEM_PROMPT = 'Kamu adalah asisten AI yang membantu pelanggan hosting/website. Jawab dengan ramah dan jelas dalam Bahasa Indonesia.';

    private function customer(): \App\Models\Customer
    {
        return Auth::guard('customer')->user();
    }

    public function index(): Response
    {
        $customer = $this->customer();

        $conversations = AiConversation::where('customer_id', $customer->id)
            ->latest('updated_at')
            ->get(['id', 'title', 'updated_at']);

        return Inertia::render('Customer/Ai/Index', [
            'balance' => AiCredit::currentBalance($customer->id),
            'conversations' => $conversations,
        ]);
    }

    public function packages(): Response
    {
        $customer = $this->customer();

        $packages = AiPackage::active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return Inertia::render('Customer/Ai/Packages', [
            'balance' => AiCredit::currentBalance($customer->id),
            'packages' => $packages,
        ]);
    }

    /**
     * Beli paket kredit → buat invoice topup → arahkan ke halaman pembayaran existing.
     */
    public function buy(AiPackage $package, InvoiceGeneratorService $generator)
    {
        abort_unless($package->is_active, 404, 'Paket tidak tersedia.');

        $invoice = Invoice::create([
            'customer_id' => $this->customer()->id,
            'invoice_number' => $generator->generateInvoiceNumber(),
            'invoice_type' => 'topup',
            'amount' => $package->price,
            'discount' => $package->discount_amount ?? 0,
            'ai_package_id' => $package->id,
            'status' => 'pending',
            'issue_date' => now(),
            'due_date' => now()->addDays(7),
        ]);

        return redirect()->route('customer.invoices.payment', $invoice)
            ->with('success', 'Invoice pembelian kredit AI dibuat. Silakan selesaikan pembayaran.');
    }

    public function show(AiConversation $conversation): JsonResponse
    {
        abort_unless($conversation->customer_id === $this->customer()->id, 403, 'Unauthorized access.');

        return response()->json([
            'conversation' => $conversation,
            'messages' => $conversation->messages()->get(['id', 'role', 'content', 'created_at']),
        ]);
    }

    public function destroy(AiConversation $conversation): JsonResponse
    {
        abort_unless($conversation->customer_id === $this->customer()->id, 403, 'Unauthorized access.');

        $conversation->delete();

        return response()->json(['success' => true]);
    }

    public function chat(Request $request, AiGateway $gateway): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'nullable|integer',
            'message' => 'required|string|max:2000',
        ]);

        $conversation = $this->resolveConversation($validated['conversation_id'] ?? null, $validated['message']);

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        try {
            $result = $gateway->chat($this->customer()->id, null, $this->buildMessages($conversation, $validated['message']));
            $response = $result['content'];
            $meta = [
                'credits_used' => $result['credits_used'],
                'balance_after' => $result['balance_after'],
                'model_key' => $result['model_key'],
            ];
        } catch (\Exception $e) {
            $response = 'Maaf, terjadi error: '.$e->getMessage();
            $meta = [];
        }

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'agent',
            'content' => $response,
        ]);

        return response()->json(['conversation_id' => $conversation->id, 'ai_response' => $response] + $meta);
    }

    /**
     * Chat streaming SSE — pola sama dengan admin AiAgent, diproses via AiGateway (multi-provider + kredit).
     */
    public function streamChat(Request $request, AiGateway $gateway)
    {
        $validated = $request->validate([
            'conversation_id' => 'nullable|integer',
            'message' => 'required|string|max:2000',
        ]);

        $conversation = $this->resolveConversation($validated['conversation_id'] ?? null, $validated['message']);

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        $conversationId = $conversation->id;
        $message = $validated['message'];

        return response()->stream(function () use ($gateway, $message, $conversationId) {
            $send = function (array $payload) {
                echo 'data: '.json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            };

            set_time_limit(300);

            $send(['type' => 'start', 'conversation_id' => $conversationId]);

            $conversation = AiConversation::find($conversationId);
            $response = '';
            $meta = [];

            try {
                $result = $gateway->chat($this->customer()->id, null, $this->buildMessages($conversation, $message));
                $response = $result['content'];
                $meta = [
                    'credits_used' => $result['credits_used'],
                    'balance_after' => $result['balance_after'],
                    'model_key' => $result['model_key'],
                ];
            } catch (\Exception $e) {
                $response = 'Maaf, terjadi error: '.$e->getMessage().' Silakan beli paket kredit di halaman paket.';
            }

            AiMessage::create([
                'conversation_id' => $conversationId,
                'role' => 'agent',
                'content' => $response,
            ]);

            $send([
                'type' => 'done',
                'conversation_id' => $conversationId,
                'ai_response' => $response,
                'meta' => $meta,
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

    private function resolveConversation(?int $conversationId, string $message): AiConversation
    {
        if ($conversationId) {
            $conversation = AiConversation::find($conversationId);
            abort_unless($conversation && $conversation->customer_id === $this->customer()->id, 403, 'Unauthorized access.');

            return $conversation;
        }

        return AiConversation::create([
            'customer_id' => $this->customer()->id,
            'title' => mb_strimwidth($message, 0, 50),
        ]);
    }

    private function buildMessages(AiConversation $conversation, string $newMessage): array
    {
        $messages = [['role' => 'system', 'content' => self::SYSTEM_PROMPT]];

        foreach ($conversation->messages()->orderBy('id')->get() as $m) {
            $messages[] = [
                'role' => $m->role === 'agent' ? 'assistant' : 'user',
                'content' => $m->content,
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $newMessage];

        return $messages;
    }
}
