<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Bot, CheckCircle2, Loader2, MessageSquare, Plus, Send, Sparkles, Trash2, XCircle, Zap } from 'lucide-vue-next';
import { nextTick, onMounted, ref } from 'vue';

interface Message {
    role: 'user' | 'agent';
    content: string;
    actions?: ActionResult[];
}

interface ActionResult {
    action: string;
    params: any;
    result: any;
}

interface Conversation {
    id: number;
    title: string;
    updated_at: string;
}

const props = defineProps<{
    conversations: Conversation[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Manage Website', href: '/admin/websites' },
    { title: 'AI Agent', href: '/admin/websites/ai' },
];

const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';

const conversations = ref<Conversation[]>(props.conversations || []);
const activeConversationId = ref<number | null>(null);
const messages = ref<Message[]>([]);
const inputMessage = ref('');
const isLoading = ref(false);
const chatContainer = ref<HTMLElement>();

const suggestions = [
    'Cek website mana yang perlu update WP core',
    'Audit SEO halaman utama semua website',
    'Buatkan artikel tentang tips digital marketing untuk web pertama',
    'Website mana saja yang plugin-nya perlu update?',
    'Tampilkan ringkasan kondisi semua website',
];

const loadConversation = async (id: number) => {
    activeConversationId.value = id;
    isLoading.value = true;

    try {
        const res = await fetch(`/admin/websites/ai/conversations/${id}`, {
            headers: { 'X-CSRF-TOKEN': csrfToken },
        });
        const data = await res.json();
        messages.value = (data.messages || []).map((m: any) => ({
            role: m.role,
            content: m.content,
            actions: m.actions || [],
        }));
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

    const res = await fetch(`/admin/websites/ai/conversations/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken },
    });

    if (res.ok) {
        conversations.value = conversations.value.filter(c => c.id !== id);
        if (activeConversationId.value === id) {
            activeConversationId.value = null;
            messages.value = [];
        }
    }
};

const upsertConversation = (id: number, firstMessage: string) => {
    const existing = conversations.value.find(c => c.id === id);
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

    try {
        const res = await fetch('/admin/websites/ai/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ message: text, conversation_id: activeConversationId.value }),
        });

        const data = await res.json();

        if (data.conversation_id) {
            activeConversationId.value = data.conversation_id;
            upsertConversation(data.conversation_id, text);
        }

        messages.value.push({
            role: 'agent',
            content: data.ai_response || 'Tidak ada respons.',
            actions: data.actions || [],
        });
    } catch (e) {
        messages.value.push({
            role: 'agent',
            content: 'Maaf, terjadi error saat menghubungi AI Agent.',
        });
    } finally {
        isLoading.value = false;
        await nextTick();
        scrollToBottom();
    }
};

const scrollToBottom = () => {
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const formatActionName = (action: string): string => {
    const names: Record<string, string> = {
        check_updates: 'Cek Update',
        update_wp: 'Update WordPress',
        update_plugins: 'Update Plugin',
        create_article: 'Buat Artikel',
        audit_seo: 'Audit SEO',
        check_expiring_orders: 'Order Berakhir',
        renew_order: 'Perpanjang Order',
    };
    return names[action] || action;
};

const formatMarkdown = (text: string): string => {
    return text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/`(.*?)`/g, '<code class="bg-muted px-1 rounded text-sm font-mono">$1</code>')
        .replace(/\n/g, '<br>');
};

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
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="AI Agent - Manage Website" />

        <div class="flex gap-6 h-[calc(100vh-120px)]">
            <!-- Sidebar: Riwayat Percakapan -->
            <aside class="w-64 flex-shrink-0 flex flex-col border rounded-lg bg-card overflow-hidden">
                <div class="p-3 border-b">
                    <Button class="w-full cursor-pointer justify-start" @click="newChat">
                        <Plus class="mr-2 h-4 w-4" /> Percakapan Baru
                    </Button>
                </div>
                <div class="flex-1 overflow-y-auto p-2 space-y-1">
                    <p v-if="conversations.length === 0" class="text-xs text-muted-foreground text-center py-8">
                        Belum ada percakapan
                    </p>
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
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                            <Bot class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <h1 class="text-xl font-semibold">AI Agent</h1>
                            <p class="text-sm text-muted-foreground">Asisten pintar untuk kelola website klien</p>
                        </div>
                    </div>
                </div>

                <!-- Chat Area -->
                <div class="flex-1 overflow-y-auto space-y-4 mb-4" ref="chatContainer">
                    <!-- Welcome -->
                    <div v-if="messages.length === 0" class="text-center py-12">
                        <Sparkles class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                        <h2 class="text-lg font-medium mb-2">Halo! Saya AI Agent untuk Manage Website</h2>
                        <p class="text-sm text-muted-foreground mb-6">
                            Saya bisa membantu mengecek update, membuat artikel, audit SEO, dan lainnya.
                        </p>
                        <div class="flex flex-wrap justify-center gap-2 max-w-lg mx-auto">
                            <button
                                v-for="s in suggestions"
                                :key="s"
                                class="text-xs text-left bg-muted/50 hover:bg-muted border rounded-md px-3 py-2 cursor-pointer transition-colors"
                                @click="inputMessage = s; sendMessage()"
                            >
                                {{ s }}
                            </button>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div v-for="(msg, idx) in messages" :key="idx" class="flex gap-3" :class="msg.role === 'user' ? 'justify-end' : ''">
                        <!-- User message -->
                        <div v-if="msg.role === 'user'" class="bg-primary text-primary-foreground rounded-2xl rounded-br-md px-4 py-2.5 max-w-[80%] text-sm">
                            {{ msg.content }}
                        </div>

                        <!-- Agent message -->
                        <div v-else class="flex gap-3 max-w-[85%]">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 flex-shrink-0 mt-1">
                                <Bot class="h-4 w-4 text-primary" />
                            </div>
                            <div class="space-y-2">
                                <div class="bg-muted/50 rounded-2xl rounded-bl-md px-4 py-2.5 text-sm prose-sm" v-html="formatMarkdown(msg.content)" />

                                <!-- Actions Result -->
                                <div v-if="msg.actions && msg.actions.length > 0" class="space-y-2">
                                    <div
                                        v-for="(action, aIdx) in msg.actions"
                                        :key="aIdx"
                                        class="border rounded-lg p-3 bg-background text-sm"
                                    >
                                        <div class="flex items-center gap-2 mb-2">
                                            <Zap class="h-3.5 w-3.5 text-primary" />
                                            <span class="font-medium">{{ formatActionName(action.action) }}</span>
                                            <Badge :variant="action.result?.success !== false ? 'default' : 'destructive'" class="text-xs">
                                                {{ action.result?.success !== false ? 'Sukses' : 'Gagal' }}
                                            </Badge>
                                        </div>

                                        <!-- check_updates result -->
                                        <div v-if="action.action === 'check_updates' && action.result?.websites_need_update">
                                            <div v-if="action.result.websites_need_update.length === 0" class="text-green-600 flex items-center gap-1">
                                                <CheckCircle2 class="h-3.5 w-3.5" /> Semua website up-to-date
                                            </div>
                                            <div v-else class="space-y-2">
                                                <div v-for="w in action.result.websites_need_update" :key="w.id" class="border-l-2 border-primary/50 pl-3">
                                                    <p class="font-medium">{{ w.name }}</p>
                                                    <p class="text-xs text-muted-foreground" v-for="issue in w.issues" :key="issue">• {{ issue }}</p>
                                                    <Badge v-if="!w.can_auto_update" variant="secondary" class="text-xs mt-1">Perlu kredensial WP</Badge>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- audit_seo result -->
                                        <div v-if="action.action === 'audit_seo' && action.result?.analysis">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="text-lg font-bold">{{ action.result.analysis.score }}/100</span>
                                                <Badge :variant="action.result.analysis.score >= 80 ? 'default' : action.result.analysis.score >= 60 ? 'secondary' : 'destructive'" class="text-xs">
                                                    {{ action.result.analysis.score >= 80 ? 'Optimal' : action.result.analysis.score >= 60 ? 'Perlu Perbaikan' : 'Prioritas' }}
                                                </Badge>
                                            </div>
                                            <div class="space-y-1 text-xs text-muted-foreground">
                                                <p>Title: {{ action.result.analysis.title || '- (kosong)' }}</p>
                                                <p>Meta Desc: {{ action.result.analysis.meta_description ? 'Ada' : 'Tidak ada' }}</p>
                                                <p>H1: {{ action.result.analysis.h1_count }}</p>
                                                <p>Gambar: {{ action.result.analysis.images_with_alt }}/{{ action.result.analysis.total_images }} dengan alt</p>
                                                <p>Ukuran: ~{{ action.result.analysis.page_size_kb }} KB</p>
                                            </div>
                                            <div v-if="action.result.analysis.issues?.length" class="mt-2">
                                                <p v-for="issue in action.result.analysis.issues" :key="issue.message" class="text-xs flex items-center gap-1">
                                                    <XCircle v-if="issue.type === 'error'" class="h-3 w-3 text-red-500" />
                                                    <span v-else-if="issue.type === 'warning'" class="text-yellow-500">⚠</span>
                                                    <span v-else class="text-blue-500">ℹ</span>
                                                    {{ issue.message }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- check_expiring_orders result -->
                                        <div v-if="action.action === 'check_expiring_orders' && action.result?.orders_expiring">
                                            <p v-if="action.result.orders_expiring.length === 0" class="text-green-600 flex items-center gap-1">
                                                <CheckCircle2 class="h-3.5 w-3.5" /> Tidak ada order yang berakhir bulan ini
                                            </p>
                                            <div v-else class="space-y-2">
                                                <div v-for="o in action.result.orders_expiring" :key="o.id" class="border-l-2 border-primary/50 pl-3">
                                                    <p class="font-medium">{{ o.customer }}</p>
                                                    <p class="text-xs text-muted-foreground">
                                                        <template v-if="o.service_type">{{ o.service_type }}<template v-if="o.domain_name"> - {{ o.domain_name }}</template> · </template>{{ o.expires_at }}
                                                    </p>
                                                    <div class="flex gap-1.5 mt-1">
                                                        <Badge v-if="o.auto_renew" variant="secondary" class="text-xs">Auto renew</Badge>
                                                        <Badge v-else variant="destructive" class="text-xs">Tidak auto renew</Badge>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Generic result -->
                                        <p v-if="action.result?.message" class="text-xs text-muted-foreground mt-1">{{ action.result.message }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading -->
                    <div v-if="isLoading" class="flex gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                            <Bot class="h-4 w-4 text-primary" />
                        </div>
                        <div class="bg-muted/50 rounded-2xl rounded-bl-md px-4 py-2.5">
                            <Loader2 class="h-4 w-4 animate-spin text-muted-foreground" />
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <div class="border-t pt-4">
                    <div class="flex gap-3">
                        <Textarea
                            v-model="inputMessage"
                            placeholder="Ketik perintah... contoh: cek website yang perlu update wp core"
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
                        Tips: Kamu bisa minta cek update, buat artikel, audit SEO, atau tanya kondisi website.
                        <span class="text-primary">Enter</span> untuk kirim, <span class="text-primary">Shift+Enter</span> untuk baris baru.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
