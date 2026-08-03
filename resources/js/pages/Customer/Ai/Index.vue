<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Bot, Coins, Loader2, MessageSquare, Plus, Send, Sparkles, Trash2 } from 'lucide-vue-next';
import { nextTick, onMounted, ref } from 'vue';

interface Message {
    role: 'user' | 'agent';
    content: string;
    meta?: { credits_used: number; balance_after: number; model_key: string } | null;
    pending?: boolean;
}

interface Conversation {
    id: number;
    title: string;
    updated_at: string;
}

const props = defineProps<{
    balance: number;
    conversations: Conversation[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/customer/dashboard' },
    { title: 'AI Assistant', href: '/customer/ai' },
];

const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';

const balance = ref(props.balance);
const conversations = ref<Conversation[]>(props.conversations || []);
const activeConversationId = ref<number | null>(null);
const messages = ref<Message[]>([]);
const inputMessage = ref('');
const isLoading = ref(false);
const chatContainer = ref<HTMLElement>();

const loadConversation = async (id: number) => {
    activeConversationId.value = id;
    isLoading.value = true;
    try {
        const res = await fetch(`/customer/ai/conversations/${id}`, { headers: { 'X-CSRF-TOKEN': csrfToken } });
        const data = await res.json();
        messages.value = (data.messages || []).map((m: any) => ({ role: m.role, content: m.content }));
    } catch {
        messages.value = [];
    } finally {
        isLoading.value = false;
        await nextTick();
        scrollToBottom();
    }
};

const newChat = () => {
    activeConversationId.value = null;
    messages.value = [];
};

const deleteConversation = async (id: number) => {
    if (!confirm('Hapus percakapan ini?')) return;
    const res = await fetch(`/customer/ai/conversations/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken },
    });
    if (res.ok) {
        conversations.value = conversations.value.filter((c) => c.id !== id);
        if (activeConversationId.value === id) {
            activeConversationId.value = null;
            messages.value = [];
        }
    }
};

const upsertConversation = (id: number, firstMessage: string) => {
    const existing = conversations.value.find((c) => c.id === id);
    if (existing) {
        conversations.value.splice(conversations.value.indexOf(existing), 1);
        conversations.value.unshift(existing);
    } else {
        conversations.value.unshift({
            id,
            title: firstMessage.length > 40 ? firstMessage.slice(0, 40) + '…' : firstMessage,
            updated_at: new Date().toISOString(),
        });
    }
};

const sendMessage = async () => {
    const text = inputMessage.value.trim();
    if (!text || isLoading.value) return;

    messages.value.push({ role: 'user', content: text });
    inputMessage.value = '';
    isLoading.value = true;

    await nextTick();
    scrollToBottom();

    const agentMsg: Message = { role: 'agent', content: '', pending: true, meta: null };
    messages.value.push(agentMsg);

    const handleEvent = (payload: any) => {
        if (payload.type === 'start') {
            activeConversationId.value = payload.conversation_id;
            upsertConversation(payload.conversation_id, text);
        } else if (payload.type === 'done') {
            agentMsg.content = payload.ai_response || 'Tidak ada respons.';
            agentMsg.meta = payload.meta || null;
            if (payload.meta?.balance_after !== undefined) balance.value = payload.meta.balance_after;
            agentMsg.pending = false;
            scrollToBottom();
        } else if (payload.type === 'error') {
            agentMsg.content = payload.message || 'Terjadi error saat memproses permintaan.';
            agentMsg.pending = false;
            scrollToBottom();
        }
    };

    try {
        const res = await fetch('/customer/ai/chat/stream', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ message: text, conversation_id: activeConversationId.value }),
        });

        if (!res.ok) throw new Error('HTTP ' + res.status);
        if (!res.body) throw new Error('Stream tidak tersedia');

        const reader = res.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            buffer += decoder.decode(value, { stream: true });

            let sep: number;
            while ((sep = buffer.indexOf('\n\n')) !== -1) {
                const raw = buffer.slice(0, sep);
                buffer = buffer.slice(sep + 2);

                for (const line of raw.split('\n')) {
                    if (!line.startsWith('data: ')) continue;
                    const data = line.slice(6);
                    if (data.trim() === '[DONE]') continue;
                    try {
                        handleEvent(JSON.parse(data));
                    } catch {
                        // event malformed — abaikan
                    }
                }
            }
        }
    } catch (e) {
        agentMsg.content = 'Maaf, terjadi error saat menghubungi AI Assistant.';
        agentMsg.pending = false;
    } finally {
        agentMsg.pending = false;
        isLoading.value = false;
        await nextTick();
        scrollToBottom();
    }
};

const scrollToBottom = () => {
    if (chatContainer.value) chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
};

const formatMarkdown = (text: string): string =>
    text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/`(.*?)`/g, '<code class="bg-muted px-1 rounded text-sm font-mono">$1</code>')
        .replace(/\n/g, '<br>');

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
};

onMounted(() => {
    if (conversations.value.length > 0) {
        loadConversation(conversations.value[0].id);
    }
});
</script>

<template>
    <CustomerLayout :breadcrumbs="breadcrumbs">
        <Head title="AI Assistant" />

        <div class="flex gap-6 h-[calc(100vh-140px)]">
            <!-- Sidebar: Riwayat -->
            <aside class="w-64 flex-shrink-0 flex flex-col border rounded-lg bg-card overflow-hidden">
                <div class="p-3 border-b space-y-2">
                    <div class="flex items-center justify-between rounded-md bg-primary/5 px-3 py-2">
                        <span class="flex items-center gap-1.5 text-sm font-semibold">
                            <Coins class="h-4 w-4 text-amber-500" /> {{ balance.toLocaleString('id-ID') }}
                        </span>
                        <Link href="/customer/ai/packages" class="text-xs font-medium text-primary hover:underline">Beli</Link>
                    </div>
                    <Button class="w-full cursor-pointer justify-start" @click="newChat">
                        <Plus class="mr-2 h-4 w-4" /> Percakapan Baru
                    </Button>
                </div>
                <div class="flex-1 overflow-y-auto p-2 space-y-1">
                    <p v-if="conversations.length === 0" class="text-xs text-muted-foreground text-center py-8">Belum ada percakapan</p>
                    <div
                        v-for="c in conversations"
                        :key="c.id"
                        class="group flex items-center gap-2 rounded-md px-3 py-2 cursor-pointer text-sm transition-colors"
                        :class="activeConversationId === c.id ? 'bg-primary/10 text-primary' : 'hover:bg-muted'"
                        @click="loadConversation(c.id)"
                    >
                        <MessageSquare class="h-4 w-4 flex-shrink-0" />
                        <span class="flex-1 truncate">{{ c.title }}</span>
                        <button
                            class="opacity-0 group-hover:opacity-100 text-muted-foreground hover:text-destructive cursor-pointer flex-shrink-0"
                            title="Hapus"
                            @click.stop="deleteConversation(c.id)"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
            </aside>

            <!-- Main Chat -->
            <div class="flex-1 flex flex-col min-w-0 max-w-3xl">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                            <Bot class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <h1 class="text-xl font-semibold">AI Assistant</h1>
                            <p class="text-sm text-muted-foreground">Chat AI — biaya dipotong dari saldo kredit Anda</p>
                        </div>
                    </div>
                    <Link href="/customer/ai/packages">
                        <Button variant="outline" class="cursor-pointer"><Coins class="mr-2 h-4 w-4" /> Beli Kredit</Button>
                    </Link>
                </div>

                <div class="flex-1 overflow-y-auto space-y-4 mb-4" ref="chatContainer">
                    <div v-if="messages.length === 0" class="text-center py-12">
                        <Sparkles class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                        <h2 class="text-lg font-medium mb-2">Halo! Saya AI Assistant Anda</h2>
                        <p class="text-sm text-muted-foreground mb-6">
                            Tanyakan apa saja — bantuan teknis, tips website, dan lainnya.
                        </p>
                    </div>

                    <div v-for="(msg, idx) in messages" :key="idx" class="flex gap-3" :class="msg.role === 'user' ? 'justify-end' : ''">
                        <div v-if="msg.role === 'user'" class="bg-primary text-primary-foreground rounded-2xl rounded-br-md px-4 py-2.5 max-w-[80%] text-sm">
                            {{ msg.content }}
                        </div>
                        <div v-else class="flex gap-3 max-w-[85%]">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 flex-shrink-0 mt-1">
                                <Bot class="h-4 w-4 text-primary" />
                            </div>
                            <div class="space-y-2">
                                <div v-if="msg.pending" class="bg-muted/50 rounded-2xl rounded-bl-md px-4 py-2.5 text-sm">
                                    <Loader2 class="h-4 w-4 animate-spin text-muted-foreground" />
                                </div>
                                <template v-else>
                                    <div class="bg-muted/50 rounded-2xl rounded-bl-md px-4 py-2.5 text-sm prose-sm" v-html="formatMarkdown(msg.content)" />
                                    <p v-if="msg.meta" class="text-[11px] text-muted-foreground">
                                        Model {{ msg.meta.model_key }} · pakai {{ msg.meta.credits_used }} kredit · sisa {{ msg.meta.balance_after }}
                                    </p>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <div class="flex gap-3">
                        <Textarea
                            v-model="inputMessage"
                            placeholder="Ketik pertanyaan Anda..."
                            :disabled="isLoading"
                            class="min-h-[52px] max-h-32 resize-none"
                            rows="2"
                            @keydown="handleKeydown"
                        />
                        <Button
                            @click="sendMessage"
                            :disabled="isLoading || !inputMessage.trim()"
                            class="cursor-pointer flex-shrink-0 h-[52px] w-[52px]"
                        >
                            <Send class="h-4 w-4" />
                        </Button>
                    </div>
                    <p class="text-xs text-muted-foreground mt-2">
                        <span class="text-primary">Enter</span> kirim · <span class="text-primary">Shift+Enter</span> baris baru.
                        Saldo dipotong per pemakaian sesuai model yang dipakai.
                    </p>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
