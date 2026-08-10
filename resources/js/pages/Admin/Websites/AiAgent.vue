<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Bot, CheckCircle2, ExternalLink, Loader2, MessageSquare, PanelLeft, PanelLeftClose, Plus, Send, Trash2, XCircle, Zap } from 'lucide-vue-next';
import { nextTick, onMounted, ref, computed, watch } from 'vue';
import ConfirmModal from '@/components/ConfirmModal.vue';

interface ProgressItem {
    message: string;
    status: string;
    agent?: string;
}

interface PendingAction {
    action: string;
    params: any;
    description?: string;
}

interface Message {
    role: 'user' | 'agent';
    content: string;
    actions?: ActionResult[];
    progress?: ProgressItem[];
    pending?: boolean;
    pendingActions?: PendingAction[];
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
    { title: 'AI', href: '/admin/ai/models' },
    { title: 'AI Agent', href: '/admin/websites/ai' },
];

const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';

const conversations = ref<Conversation[]>(props.conversations || []);
const activeConversationId = ref<number | null>(null);
const messages = ref<Message[]>([]);
const inputMessage = ref('');
const isLoading = ref(false);
const chatContainer = ref<HTMLElement>();
const historyExpanded = ref(true);

const agentBadge = (agent: string) => {
    const map: Record<string, string> = {
        'SEO Writer': 'bg-blue-100 text-blue-700',
        'Media Agent': 'bg-purple-100 text-purple-700',
        'Content Auditor': 'bg-amber-100 text-amber-700',
        'Publisher': 'bg-green-100 text-green-700',
        'Orchestrator': 'bg-slate-200 text-slate-700',
        'Task Agent': 'bg-teal-100 text-teal-700',
        'Customer Agent': 'bg-indigo-100 text-indigo-700',
    };
    return map[agent] || 'bg-slate-200 text-slate-700';
};

const pendingActions = ref<PendingAction[]>([]);
const pendingConversationId = ref<number | null>(null);
const isConfirming = ref(false);

// Slash commands autocomplete
interface SlashCommand {
    cmd: string;
    desc: string;
}

const slashCommands: SlashCommand[] = [
    { cmd: '/help', desc: 'Tampilkan daftar perintah' },
    { cmd: '/updates', desc: 'Cek website yang perlu update WP core' },
    { cmd: '/article', desc: 'Buat artikel SEO ke website klien' },
    { cmd: '/audit', desc: 'Audit SEO halaman website' },
    { cmd: '/expiring', desc: 'Cek order yang berakhir bulan ini' },
    { cmd: '/renew', desc: 'Perpanjang order layanan' },
    { cmd: '/tasks', desc: 'Daftar tugas tim' },
    { cmd: '/create-task', desc: 'Buat tugas baru' },
    { cmd: '/customers', desc: 'Daftar customer' },
    { cmd: '/invoices', desc: 'Daftar invoice belum dibayar' },
    { cmd: '/summary', desc: 'Ringkasan kondisi bisnis' },
    { cmd: '/jurnal', desc: 'Catat jurnal maintenance harian' },
    { cmd: '/list-jurnal', desc: 'Lihat daftar jurnal maintenance' },
];

const textareaRef = ref<HTMLElement>();
const showSlashMenu = ref(false);
const activeSlashIndex = ref(0);

const filteredSlashCommands = computed(() => {
    const t = inputMessage.value.trim();
    if (!t.startsWith('/')) return [];
    // Match kata pertama saja, sehingga "/expiring xxx" tetap dikenali sebagai /expiring
    const q = t.split(/\s+/)[0].toLowerCase();
    const filtered = slashCommands.filter(c => c.cmd.toLowerCase().includes(q));
    if (filtered.length === 0 && q !== '/help') {
        filtered.push({ cmd: '/help', desc: 'Tampilkan daftar perintah' });
    }
    return filtered;
});

const selectSlashCommand = (cmd: string) => {
    inputMessage.value = cmd + ' ';
    showSlashMenu.value = false;
    nextTick(() => (textareaRef.value as any)?.$el?.focus());
};

watch(inputMessage, (val) => {
    if (val.startsWith('/') && !isLoading.value && !isConfirming.value) {
        showSlashMenu.value = true;
        activeSlashIndex.value = 0;
    } else {
        showSlashMenu.value = false;
    }
});

const showHelp = () => {
    messages.value.push({ role: 'user', content: '/help' });
    const helpText = 'Berikut perintah yang bisa kamu gunakan:\n\n' +
        slashCommands.map(c => `• **${c.cmd}** — ${c.desc}`).join('\n') +
        '\n\nKetik **/** di kolom chat untuk autocomplete perintah.';
    messages.value.push({ role: 'agent', content: helpText });
    inputMessage.value = '';
    showSlashMenu.value = false;
    scrollToBottom();
};

const loadConversation = async (id: number) => {
    activeConversationId.value = id;
    pendingActions.value = [];
    pendingConversationId.value = null;
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
    pendingActions.value = [];
    pendingConversationId.value = null;
};

const showConfirm = ref(false);
const confirmMessage = ref('');
const confirmCallback = ref<(() => void) | null>(null);
const openConfirm = (msg: string, cb: () => void) => { confirmMessage.value = msg; confirmCallback.value = cb; showConfirm.value = true; };
const handleConfirm = () => { showConfirm.value = false; if (confirmCallback.value) confirmCallback.value(); };

const deleteConversation = async (id: number) => {
    openConfirm('Hapus percakapan ini?', async () => {

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
    });
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

type StreamEventHandler = (payload: any) => void;

const streamRequest = async (url: string, body: any, handleEvent: StreamEventHandler): Promise<void> => {
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(body),
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
};

const confirmActions = async () => {
    if (!pendingConversationId.value || isConfirming.value) return;
    isConfirming.value = true;
    // Hapus kartu konfirmasi dari percakapan — diganti bubble status progress
    messages.value.forEach(m => (m.pendingActions = []));

    const agentMsg: Message = { role: 'agent', content: '', pending: true, progress: [] };
    messages.value.push(agentMsg);
    pendingActions.value = [];
    scrollToBottom();

    const handleEvent = (payload: any) => {
        if (payload.type === 'progress') {
            agentMsg.progress!.push({ message: payload.message, status: payload.status, agent: payload.agent });
            scrollToBottom();
        } else if (payload.type === 'done') {
            agentMsg.content = payload.ai_response || 'Tidak ada respons.';
            agentMsg.actions = payload.actions || [];
            agentMsg.pending = false;
            agentMsg.progress = [];
            scrollToBottom();
        } else if (payload.type === 'error') {
            agentMsg.content = payload.message || 'Terjadi error saat mengeksekusi aksi.';
            agentMsg.pending = false;
            agentMsg.progress = [];
            scrollToBottom();
        }
    };

    try {
        await streamRequest('/admin/websites/ai/chat/confirm', { conversation_id: pendingConversationId.value }, handleEvent);
    } catch {
        agentMsg.content = 'Maaf, terjadi error saat mengeksekusi aksi.';
        agentMsg.pending = false;
        agentMsg.progress = [];
    } finally {
        isConfirming.value = false;
        await nextTick();
        scrollToBottom();
    }
};

const cancelActions = async () => {
    if (!pendingConversationId.value) return;

    try {
        await fetch('/admin/websites/ai/chat/cancel', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ conversation_id: pendingConversationId.value }),
        });
    } catch {
        // abaikan — cukup bersihkan kartu
    }

    pendingActions.value = [];
    pendingConversationId.value = null;
    messages.value.forEach(m => (m.pendingActions = []));
    messages.value.push({ role: 'agent', content: 'Aksi dibatalkan.' });
    scrollToBottom();
};

const sendMessage = async () => {
    const text = inputMessage.value.trim();
    if (!text || isLoading.value) return;

    if (text === '/help') {
        showHelp();
        return;
    }

    // Intercept slash command jurnal — arahkan langsung ke halaman, tanpa AI
    if (text === '/list-jurnal') {
        messages.value.push({ role: 'user', content: text });
        inputMessage.value = '';
        messages.value.push({
            role: 'agent',
            content: 'Buka halaman [Daftar Jurnal](/admin/journals) untuk melihat semua jurnal maintenance.',
        });
        return;
    }

    messages.value.push({ role: 'user', content: text });
    inputMessage.value = '';
    showSlashMenu.value = false;
    isLoading.value = true;

    await nextTick();
    scrollToBottom();

    const agentMsg: Message = { role: 'agent', content: '', pending: true, progress: [] };
    messages.value.push(agentMsg);

    const handleEvent = (payload: any) => {
        if (payload.type === 'start') {
            activeConversationId.value = payload.conversation_id;
            upsertConversation(payload.conversation_id, text);
        } else if (payload.type === 'progress') {
            agentMsg.progress!.push({ message: payload.message, status: payload.status, agent: payload.agent });
            scrollToBottom();
        } else if (payload.type === 'done') {
            agentMsg.content = payload.ai_response || 'Tidak ada respons.';
            agentMsg.actions = payload.actions || [];
            agentMsg.pending = false;
            agentMsg.progress = [];
            if (payload.pending_actions?.length) {
                pendingActions.value = payload.pending_actions;
                pendingConversationId.value = payload.conversation_id;
                agentMsg.pendingActions = payload.pending_actions;
            }
            scrollToBottom();
        } else if (payload.type === 'error') {
            agentMsg.content = payload.message || 'Terjadi error saat memproses permintaan.';
            agentMsg.pending = false;
            agentMsg.progress = [];
            scrollToBottom();
        }
    };

    try {
        await streamRequest('/admin/websites/ai/chat/stream', { message: text, conversation_id: activeConversationId.value }, handleEvent);
    } catch (e) {
        agentMsg.content = 'Maaf, terjadi error saat menghubungi AI Agent.';
        agentMsg.pending = false;
        agentMsg.progress = [];
    } finally {
        if (agentMsg.pending) {
            agentMsg.pending = false;
            agentMsg.progress = [];
        }
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

const statusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    const map: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
        todo: 'secondary',
        in_progress: 'default',
        done: 'outline',
        cancelled: 'destructive',
    };
    return map[status] || 'secondary';
};

const customerStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    const map: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
        active: 'default',
        inactive: 'secondary',
        suspended: 'destructive',
    };
    return map[status] || 'secondary';
};

const formatRupiah = (value: any): string => {
    const num = Number(value) || 0;
    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(num);
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
        list_tasks: 'Daftar Tugas',
        create_task: 'Buat Tugas',
        update_task_status: 'Ubah Status Tugas',
        list_customers: 'Daftar Customer',
        create_customer: 'Buat Customer',
        update_customer_status: 'Ubah Status Customer',
        list_unpaid_invoices: 'Invoice Belum Bayar',
        mark_invoice_paid: 'Tandai Invoice Lunas',
        list_journals: 'Daftar Jurnal',
        create_journal: 'Catat Jurnal',
        update_journal: 'Update Jurnal',
        delete_journal: 'Hapus Jurnal',
        business_summary: 'Ringkasan Bisnis',
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
    if (showSlashMenu.value && filteredSlashCommands.value.length > 0) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeSlashIndex.value = (activeSlashIndex.value + 1) % filteredSlashCommands.value.length;
            return;
        }
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeSlashIndex.value = (activeSlashIndex.value - 1 + filteredSlashCommands.value.length) % filteredSlashCommands.value.length;
            return;
        }
        if (e.key === 'Tab') {
            e.preventDefault();
            selectSlashCommand(filteredSlashCommands.value[activeSlashIndex.value].cmd);
            return;
        }
        if (e.key === 'Enter') {
            e.preventDefault();
            // Enter hanya autocomplete kalau input command murni (satu kata).
            // Kalau ada teks tambahan (mis. "/expiring cek order"), kirim apa adanya, jangan timpa input.
            const bareCommand = inputMessage.value.trim().split(/\s+/).length === 1;
            if (bareCommand) {
                selectSlashCommand(filteredSlashCommands.value[activeSlashIndex.value].cmd);
            } else {
                showSlashMenu.value = false;
                sendMessage();
            }
            return;
        }
        if (e.key === 'Escape') {
            e.preventDefault();
            showSlashMenu.value = false;
            return;
        }
    }

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
        <Head title="AI Agent" />

        <div class="flex gap-6 h-[calc(100vh-120px)]">
            <!-- Sidebar: Riwayat Percakapan -->
            <aside
                class="flex-shrink-0 flex flex-col border rounded-lg bg-card overflow-hidden min-w-0 transition-all duration-300"
                :class="historyExpanded ? 'w-64' : 'w-12'"
            >
                <div class="p-2.5 border-b">
                    <Button
                        class="w-full cursor-pointer h-9"
                        :class="historyExpanded ? 'justify-start' : 'justify-center'"
                        @click="newChat"
                        :title="historyExpanded ? 'Percakapan Baru' : 'Buat chat baru'"
                    >
                        <Plus class="h-4 w-4" :class="historyExpanded ? 'mr-2' : ''" />
                        <span v-if="historyExpanded">Percakapan Baru</span>
                    </Button>
                </div>
                <div class="flex-1 overflow-y-auto p-2 space-y-1">
                    <p v-if="historyExpanded && conversations.length === 0" class="text-xs text-muted-foreground text-center py-8">
                        Belum ada percakapan
                    </p>
                    <div
                        v-for="c in conversations"
                        :key="c.id"
                        class="group flex items-center gap-2 rounded-md px-3 py-2 cursor-pointer text-sm transition-colors"
                        :class="[activeConversationId === c.id ? 'bg-primary/10 text-primary' : 'hover:bg-muted', historyExpanded ? '' : 'justify-center px-0']"
                        :title="c.title"
                        @click="loadConversation(c.id)"
                    >
                        <MessageSquare class="h-4 w-4 flex-shrink-0" />
                        <span v-if="historyExpanded" class="flex-1 truncate">{{ c.title }}</span>
                        <button
                            v-if="historyExpanded"
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
                        <Button variant="ghost" size="icon" class="cursor-pointer" :title="historyExpanded ? 'Sembunyikan riwayat chat' : 'Tampilkan riwayat chat'" @click="historyExpanded = !historyExpanded">
                            <PanelLeftClose v-if="historyExpanded" class="h-4 w-4" />
                            <PanelLeft v-else class="h-4 w-4" />
                        </Button>
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
                                <!-- Typing indicator (while processing) -->
                                <div v-if="msg.pending" class="bg-muted/50 rounded-2xl rounded-bl-md px-4 py-3 text-sm inline-flex items-center gap-2">
                                    <span class="typing-dots" aria-label="AI sedang mengetik">
                                        <span></span><span></span><span></span>
                                    </span>
                                    <span v-if="!msg.progress?.length" class="text-xs text-muted-foreground">Sedang memproses...</span>
                                </div>

                                <!-- Progress real-time (streaming) -->
                                <div v-if="msg.pending && msg.progress?.length" class="bg-muted/50 rounded-2xl rounded-bl-md px-4 py-2.5 text-sm space-y-1.5">
                                    <p v-for="(log, i) in msg.progress" :key="i" class="text-xs flex items-center gap-1.5" :class="log.status === 'loading' ? 'text-muted-foreground' : ''">
                                        <Loader2 v-if="log.status === 'loading'" class="h-3 w-3 animate-spin text-primary" />
                                        <CheckCircle2 v-else class="h-3 w-3 text-green-600" />
                                        <span v-if="log.agent" class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-medium leading-none" :class="agentBadge(log.agent)">
                                            {{ log.agent }}
                                        </span>
                                        {{ log.message }}
                                    </p>
                                </div>

                                <!-- Final content -->
                                <div v-if="!msg.pending" class="bg-muted/50 rounded-2xl rounded-bl-md px-4 py-2.5 text-sm prose-sm" v-html="formatMarkdown(msg.content)" />
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
                                            <Badge :variant="action.result?.error ? 'destructive' : 'default'" class="text-xs">
                                                {{ action.result?.error ? 'Gagal' : 'Sukses' }}
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

                                        <!-- create_article result -->
                                        <div v-if="action.action === 'create_article' && action.result?.score !== undefined">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="text-lg font-bold">{{ action.result.score }}/100</span>
                                                <Badge :variant="action.result.passed ? 'default' : 'destructive'" class="text-xs">
                                                    {{ action.result.passed ? 'Lolos & ' + (action.result.status === 'publish' ? 'Dipublish' : 'Draft') : 'Belum Lolos' }}
                                                </Badge>
                                                <span v-if="action.result.images_embedded" class="text-xs text-muted-foreground">· {{ action.result.images_embedded }} gambar</span>
                                                <span v-if="action.result.word_count" class="text-xs text-muted-foreground">· {{ action.result.word_count }} kata</span>
                                            </div>
                                            <div v-if="action.result.post_url" class="text-xs mb-2">
                                                <a :href="action.result.post_url" target="_blank" class="text-primary hover:underline flex items-center gap-1">
                                                    Lihat artikel <ExternalLink class="h-3 w-3" />
                                                </a>
                                            </div>
                                            <!-- Timeline log workflow artikel -->
                                            <div v-if="action.result.logs?.length" class="my-2 rounded-lg border bg-muted/40 p-2 space-y-1">
                                                <p v-for="(log, i) in action.result.logs" :key="i" class="text-xs flex items-center gap-1.5" :class="log.status === 'loading' ? 'text-muted-foreground' : ''">
                                                    <Loader2 v-if="log.status === 'loading'" class="h-3 w-3 animate-spin text-primary" />
                                                    <CheckCircle2 v-else class="h-3 w-3 text-green-600" />
                                                    <span v-if="log.agent" class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-medium leading-none" :class="agentBadge(log.agent)">
                                                        {{ log.agent }}
                                                    </span>
                                                    {{ log.message }}
                                                </p>
                                            </div>
                                            <div v-if="action.result.issues?.length" class="space-y-1">
                                                <p v-for="issue in action.result.issues" :key="issue.message" class="text-xs flex items-center gap-1">
                                                    <XCircle v-if="issue.type === 'error'" class="h-3 w-3 text-red-500" />
                                                    <span v-else class="text-yellow-500">⚠</span>
                                                    {{ issue.message }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- list_tasks result -->
                                        <div v-if="action.action === 'list_tasks' && action.result?.tasks !== undefined">
                                            <p v-if="action.result.tasks.length === 0" class="text-green-600 flex items-center gap-1">
                                                <CheckCircle2 class="h-3.5 w-3.5" /> Tidak ada tugas ditemukan
                                            </p>
                                            <div v-else class="space-y-2">
                                                <div v-for="t in action.result.tasks" :key="t.id" class="border-l-2 border-primary/50 pl-3">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <p class="font-medium">{{ t.title }}</p>
                                                        <Badge :variant="statusVariant(t.status)" class="text-xs">{{ t.status_label }}</Badge>
                                                        <Badge v-if="t.priority === 'high'" variant="destructive" class="text-xs">High</Badge>
                                                        <Badge v-else-if="t.priority === 'medium'" variant="secondary" class="text-xs">Medium</Badge>
                                                    </div>
                                                    <p class="text-xs text-muted-foreground">
                                                        <template v-if="t.category">{{ t.category }} · </template>
                                                        <template v-if="t.assignee">Assignee: {{ t.assignee }} · </template>
                                                        <template v-if="t.due_date">Tenggat: {{ t.due_date }}</template>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- create_task / update_task_status result -->
                                        <div v-if="['create_task', 'update_task_status'].includes(action.action) && action.result?.success">
                                            <p class="text-green-600 flex items-center gap-1">
                                                <CheckCircle2 class="h-3.5 w-3.5" /> {{ action.result.message }}
                                            </p>
                                        </div>

                                        <!-- list_customers result -->
                                        <div v-if="action.action === 'list_customers' && action.result?.customers !== undefined">
                                            <p v-if="action.result.customers.length === 0" class="text-green-600 flex items-center gap-1">
                                                <CheckCircle2 class="h-3.5 w-3.5" /> Tidak ada customer ditemukan
                                            </p>
                                            <div v-else class="space-y-2">
                                                <div v-for="c in action.result.customers" :key="c.id" class="border-l-2 border-primary/50 pl-3">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <p class="font-medium">{{ c.name }}</p>
                                                        <Badge :variant="customerStatusVariant(c.status)" class="text-xs">{{ c.status_label }}</Badge>
                                                    </div>
                                                    <p class="text-xs text-muted-foreground">
                                                        {{ c.email }}<template v-if="c.phone"> · {{ c.phone }}</template><template v-if="c.city"> · {{ c.city }}</template>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- create_customer / update_customer_status result -->
                                        <div v-if="['create_customer', 'update_customer_status'].includes(action.action) && action.result?.success">
                                            <p class="text-green-600 flex items-center gap-1">
                                                <CheckCircle2 class="h-3.5 w-3.5" /> {{ action.result.message }}
                                            </p>
                                        </div>

                                        <!-- list_unpaid_invoices result -->
                                        <div v-if="action.action === 'list_unpaid_invoices' && action.result?.invoices !== undefined">
                                            <p v-if="action.result.invoices.length === 0" class="text-green-600 flex items-center gap-1">
                                                <CheckCircle2 class="h-3.5 w-3.5" /> Tidak ada invoice belum dibayar
                                            </p>
                                            <div v-else class="space-y-2">
                                                <div class="flex gap-2 flex-wrap mb-2">
                                                    <Badge variant="secondary" class="text-xs">{{ action.result.total }} invoice</Badge>
                                                    <Badge variant="secondary" class="text-xs">Total: Rp {{ formatRupiah(action.result.total_amount) }}</Badge>
                                                    <Badge v-if="action.result.overdue_count" variant="destructive" class="text-xs">{{ action.result.overdue_count }} terlambat</Badge>
                                                </div>
                                                <div v-for="inv in action.result.invoices" :key="inv.id" class="border-l-2 border-primary/50 pl-3">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <p class="font-medium">{{ inv.invoice_number }}</p>
                                                        <Badge :variant="inv.status === 'overdue' ? 'destructive' : 'secondary'" class="text-xs">{{ inv.status_label }}</Badge>
                                                    </div>
                                                    <p class="text-xs text-muted-foreground">
                                                        {{ inv.customer }} · Rp {{ formatRupiah(inv.amount) }}<template v-if="inv.due_date"> · Jatuh tempo: {{ inv.due_date }}</template>
                                                        <template v-if="inv.days_late"> · Telat {{ inv.days_late }} hari</template>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- mark_invoice_paid result -->
                                        <div v-if="action.action === 'mark_invoice_paid' && action.result?.success">
                                            <p class="text-green-600 flex items-center gap-1">
                                                <CheckCircle2 class="h-3.5 w-3.5" /> {{ action.result.message }}
                                            </p>
                                        </div>

                                        <!-- business_summary result -->
                                        <div v-if="action.action === 'business_summary' && action.result?.customers" class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                            <div class="rounded-lg border bg-muted/40 p-2.5">
                                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Customer</p>
                                                <p class="font-semibold">{{ action.result.customers.total }} <span class="text-xs text-muted-foreground">({{ action.result.customers.active }} aktif)</span></p>
                                            </div>
                                            <div class="rounded-lg border bg-muted/40 p-2.5">
                                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Tugas</p>
                                                <p class="font-semibold">{{ action.result.tasks.total }} <span class="text-xs text-muted-foreground">({{ action.result.tasks.in_progress }} berjalan)</span></p>
                                            </div>
                                            <div class="rounded-lg border bg-muted/40 p-2.5">
                                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Invoice Belum Bayar</p>
                                                <p class="font-semibold">{{ action.result.invoices.unpaid_total }} <span class="text-xs text-muted-foreground">· Rp {{ formatRupiah(action.result.invoices.unpaid_sum) }}</span></p>
                                            </div>
                                            <div v-if="action.result.invoices.overdue_total > 0" class="rounded-lg border bg-muted/40 p-2.5">
                                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Overdue</p>
                                                <p class="font-semibold text-destructive">{{ action.result.invoices.overdue_total }}</p>
                                            </div>
                                            <div class="rounded-lg border bg-muted/40 p-2.5">
                                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Karyawan</p>
                                                <p class="font-semibold">{{ action.result.employees.total }}</p>
                                            </div>
                                            <div class="rounded-lg border bg-muted/40 p-2.5">
                                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Revenue Order Aktif</p>
                                                <p class="font-semibold">Rp {{ formatRupiah(action.result.active_order_revenue) }}</p>
                                            </div>
                                        </div>

                                        <!-- list_journals result -->
                                        <div v-if="action.action === 'list_journals' && action.result?.journals !== undefined">
                                            <p v-if="action.result.journals.length === 0" class="text-green-600 flex items-center gap-1">
                                                <CheckCircle2 class="h-3.5 w-3.5" /> Tidak ada jurnal ditemukan
                                            </p>
                                            <div v-else class="space-y-2">
                                                <div v-for="j in action.result.journals" :key="j.id" class="border-l-2 border-primary/50 pl-3">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <p class="font-medium">{{ j.website }}</p>
                                                        <Badge variant="secondary" class="text-xs">{{ j.activity_count }} aktivitas</Badge>
                                                    </div>
                                                    <p class="text-xs text-muted-foreground">
                                                        <template v-if="j.customer">{{ j.customer }} · </template>{{ j.entry_date }}
                                                        <template v-if="j.summary"> — {{ j.summary }}</template>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- create_journal / update_journal / delete_journal result -->
                                        <div v-if="['create_journal', 'update_journal', 'delete_journal'].includes(action.action) && action.result?.success">
                                            <p class="text-green-600 flex items-center gap-1">
                                                <CheckCircle2 class="h-3.5 w-3.5" /> {{ action.result.message }}
                                            </p>
                                        </div>

                                        <!-- Generic result -->
                                        <p v-if="action.result?.error" class="text-xs text-red-600 mt-1">{{ action.result.error }}</p>
                                        <p v-if="action.result?.message" class="text-xs text-muted-foreground mt-1">{{ action.result.message }}</p>
                                    </div>
                                </div>

                                <!-- Konfirmasi aksi (percakapan) -->
                                <div v-if="msg.pendingActions && msg.pendingActions.length > 0 && !isConfirming" class="border-2 border-amber-400 rounded-xl bg-amber-50 p-4 space-y-3">
                                    <div class="flex items-center gap-2 text-amber-800">
                                        <Zap class="h-4 w-4" />
                                        <p class="text-sm font-semibold">AI ingin mengeksekusi aksi berikut:</p>
                                    </div>
                                    <ul class="space-y-1.5">
                                        <li v-for="(pa, i) in msg.pendingActions" :key="i" class="text-sm text-amber-900 flex items-start gap-2">
                                            <span class="mt-1.5 h-1.5 w-1.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                                            <span>
                                                <span class="font-medium">{{ formatActionName(pa.action) }}</span>
                                                <span v-if="pa.description" class="text-amber-700"> — {{ pa.description }}</span>
                                            </span>
                                        </li>
                                    </ul>
                                    <div class="flex gap-2">
                                        <Button size="sm" class="cursor-pointer" :disabled="isLoading" @click="confirmActions">
                                            Jalankan
                                        </Button>
                                        <Button size="sm" variant="outline" class="cursor-pointer" :disabled="isLoading" @click="cancelActions">
                                            Batal
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading -->
                    <div v-if="isLoading && !messages.some(m => m.pending)" class="flex gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                            <Bot class="h-4 w-4 text-primary" />
                        </div>
                        <div class="bg-muted/50 rounded-2xl rounded-bl-md px-4 py-2.5">
                            <Loader2 class="h-4 w-4 animate-spin text-muted-foreground" />
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <div class="border-t pt-4 relative">
                    <!-- Slash command autocomplete -->
                    <div
                        v-if="showSlashMenu && filteredSlashCommands.length > 0"
                        class="absolute bottom-full left-0 right-0 mb-2 z-20 rounded-lg border bg-card shadow-lg overflow-hidden"
                    >
                        <ul class="max-h-56 overflow-y-auto py-1">
                            <li
                                v-for="(c, i) in filteredSlashCommands"
                                :key="c.cmd"
                                class="flex items-center gap-3 px-3 py-2 cursor-pointer text-sm transition-colors"
                                :class="i === activeSlashIndex ? 'bg-primary/10' : 'hover:bg-muted'"
                                @mouseenter="activeSlashIndex = i"
                                @click="selectSlashCommand(c.cmd)"
                            >
                                <span class="font-mono text-primary flex-shrink-0">{{ c.cmd }}</span>
                                <span class="text-muted-foreground truncate">{{ c.desc }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="flex gap-3">
                        <Textarea
                            ref="textareaRef"
                            v-model="inputMessage"
                            placeholder="Ada yang bisa dibantu?"
                            :disabled="isLoading || isConfirming"
                            class="min-h-[52px] max-h-32 resize-none"
                            rows="2"
                            @keydown="handleKeydown"
                        />
                        <Button
                            @click="sendMessage"
                            :disabled="isLoading || isConfirming || !inputMessage.trim()"
                            class="cursor-pointer flex-shrink-0 h-[52px] w-[52px]"
                        >
                            <Send class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
        <ConfirmModal :show="showConfirm" :message="confirmMessage" confirmText="Hapus" variant="destructive" @confirm="handleConfirm" @cancel="showConfirm = false" />
    </AppLayout>
</template>

<style scoped>
.typing-dots {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.typing-dots span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    opacity: 0.3;
    animation: typing-bounce 1.2s infinite ease-in-out;
}

.typing-dots span:nth-child(2) {
    animation-delay: 0.15s;
}

.typing-dots span:nth-child(3) {
    animation-delay: 0.3s;
}

@keyframes typing-bounce {
    0%, 60%, 100% {
        transform: translateY(0);
        opacity: 0.3;
    }
    30% {
        transform: translateY(-4px);
        opacity: 1;
    }
}
</style>
