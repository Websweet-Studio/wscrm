<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { router, usePage } from '@inertiajs/vue3';
import { Bot, Command, Loader2, RotateCcw, Send, X } from 'lucide-vue-next';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

interface Message {
    role: 'user' | 'agent';
    content: string;
    pending?: boolean;
}

const open = ref(false);
const messages = ref<Message[]>([]);
const input = ref('');
const loading = ref(false);
const container = ref<HTMLElement>();
const conversationId = ref<number | null>(null);

// Riwayat disimpan di sessionStorage per halaman, supaya tidak hilang saat user
// menutup panel / berpindah sub-halaman (Inertia partial reload) di resource yang sama.
const storageKey = () => 'floating_ai_' + (usePage().url as string || '/').split('?')[0];

const loadHistory = () => {
    try {
        const raw = sessionStorage.getItem(storageKey());
        if (!raw) return;
        const saved = JSON.parse(raw) as { messages?: Message[]; conversationId?: number | null };
        if (Array.isArray(saved.messages) && saved.messages.length) {
            messages.value = saved.messages;
        }
        if (saved.conversationId) {
            conversationId.value = saved.conversationId;
        }
    } catch {
        // abaikan data korup
    }
};

const persistHistory = () => {
    try {
        sessionStorage.setItem(storageKey(), JSON.stringify({
            messages: messages.value,
            conversationId: conversationId.value,
        }));
    } catch {
        // abaikan bila storage penuh/tak tersedia
    }
};

const resetConversation = () => {
    conversationId.value = null;
    messages.value = [];
    try {
        sessionStorage.removeItem(storageKey());
    } catch {
        // abaikan
    }
    messages.value.push({
        role: 'agent',
        content: 'Percakapan di-reset. Saya asisten AI — saat ini kamu di halaman **' + (currentPage().label || currentPage().url) + '**. Ada yang bisa saya bantu?',
    });
    scrollToBottom();
};

const csrfToken = () => (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';

const currentPage = () => {
    const url = (usePage().url as string) || '/';
    const props = usePage().props as any;

    // 1) Coba dari breadcrumb global (bila tersedia).
    let label = '';
    const breadcrumbs = props.breadcrumbs;
    if (Array.isArray(breadcrumbs) && breadcrumbs.length) {
        label = breadcrumbs[breadcrumbs.length - 1]?.title || '';
    }

    // 2) Bila belum ada, turunkan dari nama entitas di props halaman (mis. website.name).
    if (!label) {
        const entityName =
            props.website?.name ||
            props.order?.domain_name ||
            props.customer?.name ||
            props.invoice?.invoice_number ||
            props.task?.title;
        if (entityName) {
            label = entityName;
        }
    }

    // 3) Fallback terakhir: dari segmen path, tapi buang segmen angka murni.
    if (!label) {
        const segments = url.split('?')[0].split('/').filter(Boolean);
        // Ambil segmen terakhir yang BUKAN angka (id), supaya "8" tidak jadi label.
        const last = [...segments].reverse().find((s) => !/^\d+$/.test(s));
        label = last
            ? last.replace(/-/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())
            : 'Dashboard';
    }

    return { url, label };
};

// ---------------------------------------------------------------------------
// Command palette "/" — daftar aksi yang tersedia sesuai halaman aktif.
// Setiap command punya "text" (yang dikirim ke AI) dan "hint" (deskripsi singkat).
// ---------------------------------------------------------------------------
interface AiCommand {
    command: string;
    text: string;
    hint: string;
}

const COMMAND_LIBRARY: AiCommand[] = [
    { command: '/reset', text: '/reset', hint: 'Mulai percakapan baru' },
    { command: '/journal', text: '/jurnal ', hint: 'Catat aktivitas/jurnal maintenance' },
    { command: '/cek-update', text: 'cek update semua website', hint: 'Cek update WordPress/plugin' },
    { command: '/cek-kadaluarsa', text: 'cek order yang harus diperpanjang bulan ini', hint: 'Cek order kadaluarsa terdekat' },
    { command: '/tugas', text: 'list tugas tim', hint: 'Lihat daftar tugas/PR' },
    { command: '/customer', text: 'list customer', hint: 'Lihat daftar customer' },
    { command: '/invoice', text: 'list invoice belum bayar', hint: 'Cek invoice menunggak' },
    { command: '/ringkasan', text: 'ringkasan kondisi bisnis', hint: 'Ringkasan penjualan & kondisi bisnis' },
    { command: '/harga-domain', text: 'list harga domain', hint: 'Lihat harga domain' },
    { command: '/harga-hosting', text: 'list harga hosting', hint: 'Lihat harga paket hosting' },
    { command: '/artikel', text: 'buat artikel untuk website ', hint: 'Generate artikel SEO' },
];

// Aksi spesifik tiap resource halaman (diprioritaskan lebih dulu).
const PAGE_COMMANDS: Record<string, AiCommand[]> = {
    journals: [
        { command: '/jurnal', text: '/jurnal ', hint: 'Catat aktivitas jurnal hari ini' },
        { command: '/jurnal-bulan', text: 'tampilkan jurnal bulan ini', hint: 'Lihat jurnal bulan ini' },
        { command: '/jurnal-website', text: 'list jurnal untuk website ', hint: 'Lihat jurnal website tertentu' },
    ],
    websites: [
        { command: '/cek-update', text: 'cek update website ini', hint: 'Cek update website ini' },
        { command: '/update-wp', text: 'update WordPress website ini', hint: 'Update core WordPress' },
        { command: '/audit-seo', text: 'audit SEO website ini', hint: 'Audit SEO website ini' },
        { command: '/artikel', text: 'buat artikel untuk website ini', hint: 'Generate artikel SEO' },
        { command: '/jurnal', text: '/jurnal website ini ', hint: 'Catat jurnal website ini' },
    ],
    orders: [
        { command: '/cek-kadaluarsa', text: 'cek order yang harus diperpanjang bulan ini', hint: 'Cek kadaluarsa terdekat' },
        { command: '/urutkan-kadaluarsa', text: 'urutkan berdasarkan kadaluarsa terdekat', hint: 'Urutkan by kadaluarsa' },
    ],
    services: [
        { command: '/cek-kadaluarsa', text: 'cek order yang harus diperpanjang bulan ini', hint: 'Cek kadaluarsa terdekat' },
        { command: '/urutkan-kadaluarsa', text: 'urutkan berdasarkan kadaluarsa terdekat', hint: 'Urutkan by kadaluarsa' },
    ],
    customers: [
        { command: '/customer', text: 'list customer', hint: 'Lihat daftar customer' },
        { command: '/customer-baru', text: 'buat customer baru', hint: 'Tambah customer' },
    ],
    invoices: [
        { command: '/invoice', text: 'list invoice belum bayar', hint: 'Cek invoice menunggak' },
        { command: '/invoice-bulan', text: 'list invoice bulan ini', hint: 'Invoice bulan ini' },
    ],
    tasks: [
        { command: '/tugas', text: 'list tugas tim', hint: 'Lihat daftar tugas' },
        { command: '/tugas-baru', text: 'buat tugas baru', hint: 'Tambah tugas' },
    ],
};

const resourceFromUrl = (url: string): string => {
    const seg = url.split('?')[0].split('/').filter(Boolean);
    // URL admin: /admin/{resource} atau /admin/{resource}/{id}
    if (seg[0] === 'admin' && seg[1]) {
        return seg[1];
    }
    return seg[seg.length - 1] || '';
};

// Daftar command yang relevan dengan halaman aktif (spesifik dulu, lalu umum + /reset).
const availableCommands = computed<AiCommand[]>(() => {
    const resource = resourceFromUrl(usePage().url as string || '/');
    const specific = PAGE_COMMANDS[resource] || [];
    const generic = COMMAND_LIBRARY.filter((c) => !specific.some((s) => s.command === c.command));
    return [...specific, ...generic];
});

// Input saat ini dimulai dengan "/" tapi bukan command lengkap yang terpilih.
const slashMenuOpen = ref(false);
const filteredCommands = computed<AiCommand[]>(() => {
    const v = input.value;
    if (!v.startsWith('/')) return [];
    const q = v.slice(1).toLowerCase();
    return availableCommands.value.filter((c) => c.command.toLowerCase().startsWith(q));
});

const selectCommand = (cmd: AiCommand) => {
    input.value = cmd.text;
    slashMenuOpen.value = false;
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        // Jangan submit saat menu slash terbuka; biarkan user memilih dulu.
        if (slashMenuOpen.value && filteredCommands.value.length > 0) {
            e.preventDefault();
            return;
        }
        e.preventDefault();
        send();
    }
};

watch(input, (v) => {
    slashMenuOpen.value = v.startsWith('/') && filteredCommands.value.length > 0;
});

const scrollToBottom = () => {
    nextTick(() => {
        if (container.value) {
            container.value.scrollTop = container.value.scrollHeight;
        }
    });
};

const toggle = () => {
    open.value = !open.value;
    if (open.value) {
        scrollToBottom();
    }
};

const formatMarkdown = (text: string): string => {
    return text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/`(.*?)`/g, '<code class="bg-muted px-1 rounded text-sm font-mono">$1</code>')
        .replace(/\n/g, '<br>');
};

const streamRequest = async (url: string, body: any, onEvent: (p: any) => void): Promise<void> => {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
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
                    onEvent(JSON.parse(data));
                } catch {
                    // abaikan event malformed
                }
            }
        }
    }
};

const send = async () => {
    const text = input.value.trim();
    if (!text || loading.value) return;

    // Perintah /reset: mulai percakapan baru tanpa memanggil backend.
    if (text === '/reset') {
        input.value = '';
        resetConversation();
        return;
    }

    messages.value.push({ role: 'user', content: text });
    input.value = '';
    loading.value = true;
    persistHistory();
    scrollToBottom();

    const agentMsg: Message = { role: 'agent', content: '', pending: true };
    messages.value.push(agentMsg);

    const onEvent = (p: any) => {
        if (p.type === 'progress') {
            // Tampilkan progress ringkas sebagai teks sementara (bila belum ada konten final).
            if (!agentMsg.content) {
                agentMsg.content = p.message;
            }
            scrollToBottom();
        } else if (p.type === 'start') {
            if (p.conversation_id) {
                conversationId.value = p.conversation_id;
            }
        } else if (p.type === 'done') {
            agentMsg.content = p.ai_response || 'Tidak ada respons.';
            agentMsg.pending = false;
            scrollToBottom();
            persistHistory();
            // Aksi apply_filter → navigasi ke URL filter yang diminta AI.
            navigateIfNeeded(p.actions || []);
            // Refresh halaman aktif bila AI menjalankan aksi yang mengubah data
            // (misal create_journal), supaya tabel/dashboard langsung ter-update.
            reloadIfNeeded(p.actions || []);
        } else if (p.type === 'error') {
            agentMsg.content = p.message || 'Terjadi error.';
            agentMsg.pending = false;
            persistHistory();
            scrollToBottom();
        }
    };

    try {
        await streamRequest('/admin/websites/ai/chat/stream', {
            message: text,
            page: currentPage(),
            conversation_id: conversationId.value,
        }, onEvent);
    } catch {
        agentMsg.content = 'Maaf, terjadi error saat menghubungi AI Agent.';
        agentMsg.pending = false;
    } finally {
        agentMsg.pending = false;
        loading.value = false;
        scrollToBottom();
    }
};


// Aksi yang mengubah data (mutasi). Setelah sukses, reload halaman aktif agar
// tampilan (tabel/list) menampilkan data terbaru tanpa perlu refresh manual.
const MUTATING_ACTIONS = new Set([
    'create_journal',
    'update_journal',
    'delete_journal',
    'create_task',
    'update_task_status',
    'create_customer',
    'update_customer_status',
    'mark_invoice_paid',
    'update_domain_price',
    'update_hosting_price',
    'update_wp',
    'update_plugins',
    'create_article',
]);

const reloadIfNeeded = (actions: any[]) => {
    const hasMutation = actions.some((a) => MUTATING_ACTIONS.has(a.action) && !a.result?.error);
    if (hasMutation) {
        router.reload({ only: ['journals', 'stats', 'recentActivities', 'expiringServices', 'chartData', 'myPendingTasks', 'customers', 'invoices', 'orders', 'domainPrices', 'hostingPlans'] });
    }
};

// Aksi apply_filter membawa instruksi navigasi (path+query). Eksekusi via router.visit
// dengan preserveState agar chat (state komponen) TIDAK hilang saat filter berubah.
const navigateIfNeeded = (actions: any[]) => {
    const filterAction = actions.find((a) => a.action === 'apply_filter' && !a.result?.error && a.result?.navigate);
    if (filterAction) {
        router.visit(filterAction.result.navigate, { preserveState: true, preserveScroll: true });
    }
};

onMounted(() => {
    loadHistory();
    if (messages.value.length === 0) {
        messages.value.push({
            role: 'agent',
            content: 'Halo! Saya asisten AI. Saat ini kamu berada di halaman **' + (currentPage().label || currentPage().url) + '**. Ada yang bisa saya bantu?',
        });
    }
});

// Jika Inertia navigasi ke sub-halaman dalam resource yang sama, muat ulang riwayat
// agar chat tidak hilang (komponen floating umumnya tidak di-unmount).
watch(() => usePage().url, () => {
    loadHistory();
});
</script>

<template>
    <div class="fixed bottom-5 right-5 z-50 flex flex-col items-end gap-3">
        <!-- Panel chat -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-2 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-2 scale-95"
        >
            <div v-if="open" class="w-[380px] max-w-[calc(100vw-2rem)] rounded-2xl border bg-card shadow-2xl overflow-hidden flex flex-col">
                <!-- Header -->
                <div class="flex items-center gap-2 border-b bg-muted/40 px-4 py-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                        <Bot class="h-4 w-4 text-primary" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold leading-tight">AI Assistant</p>
                        <p class="text-xs text-muted-foreground truncate">{{ currentPage().label || currentPage().url }}</p>
                    </div>
                    <Button variant="ghost" size="icon" class="h-8 w-8 cursor-pointer" title="Reset percakapan" @click="resetConversation">
                        <RotateCcw class="h-4 w-4" />
                    </Button>
                    <Button variant="ghost" size="icon" class="h-8 w-8 cursor-pointer" @click="open = false">
                        <X class="h-4 w-4" />
                    </Button>
                </div>

                <!-- Messages -->
                <div ref="container" class="flex-1 overflow-y-auto p-4 space-y-3" style="max-height: 60vh; min-height: 200px;">
                    <div v-for="(m, i) in messages" :key="i" class="flex gap-2" :class="m.role === 'user' ? 'justify-end' : ''">
                        <div
                            v-if="m.role === 'user'"
                            class="bg-primary text-primary-foreground rounded-2xl rounded-br-md px-3.5 py-2 text-sm max-w-[80%] whitespace-pre-wrap"
                        >
                            {{ m.content }}
                        </div>
                        <div v-else class="flex gap-2 max-w-[85%]">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 flex-shrink-0 mt-0.5">
                                <Bot class="h-3.5 w-3.5 text-primary" />
                            </div>
                            <div
                                class="bg-muted/50 rounded-2xl rounded-bl-md px-3.5 py-2 text-sm prose-sm"
                                v-html="m.pending ? (m.content || '<span class=&quot;text-muted-foreground&quot;>Memproses...</span>') : formatMarkdown(m.content)"
                            ></div>
                        </div>
                    </div>

                    <div v-if="loading" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <Loader2 class="h-3 w-3 animate-spin" />
                        AI sedang berpikir...
                    </div>
                </div>

                <!-- Input -->
                <div class="border-t p-3">
                    <!-- Slash command menu -->
                    <div
                        v-if="slashMenuOpen && filteredCommands.length"
                        class="mb-2 max-h-48 overflow-y-auto rounded-lg border bg-popover p-1 shadow-md"
                    >
                        <button
                            v-for="cmd in filteredCommands"
                            :key="cmd.command"
                            type="button"
                            class="flex w-full cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm hover:bg-muted"
                            @click="selectCommand(cmd)"
                        >
                            <Command class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                            <span class="font-medium">{{ cmd.command }}</span>
                            <span class="ml-auto truncate text-xs text-muted-foreground">{{ cmd.hint }}</span>
                        </button>
                    </div>

                    <div class="flex gap-2">
                        <Textarea
                            v-model="input"
                            placeholder="Tanya atau perintahkan AI... (ketik / untuk aksi)"
                            :disabled="loading"
                            class="min-h-[44px] max-h-28 resize-none text-sm"
                            rows="1"
                            @keydown="handleKeydown"
                        />
                        <Button @click="send" :disabled="loading || !input.trim()" class="cursor-pointer flex-shrink-0 h-[44px] w-[44px]">
                            <Send class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Tombol floating -->
        <Button
            @click="toggle"
            class="h-14 w-14 rounded-full shadow-lg cursor-pointer"
            :aria-label="open ? 'Tutup AI Assistant' : 'Buka AI Assistant'"
        >
            <Bot v-if="!open" class="h-6 w-6" />
            <X v-else class="h-6 w-6" />
        </Button>
    </div>
</template>
