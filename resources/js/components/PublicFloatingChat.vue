<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { usePage } from '@inertiajs/vue3';
import { Loader2, MessageCircle, Send, X } from 'lucide-vue-next';
import { computed, nextTick, onMounted, ref } from 'vue';

interface ChatMsg {
    role: 'user' | 'assistant';
    content: string;
}

const open = ref(false);
const messages = ref<ChatMsg[]>([]);
const input = ref('');
const loading = ref(false);
const container = ref<HTMLElement>();
const fallbackWa = ref<string | null>(null);

const page = usePage();
const companyWhatsapp = computed(() => {
    const raw = ((page.props as any)?.brandingSettings?.company_whatsapp as string | undefined) || '';
    return raw ? String(raw).replace(/\D/g, '') : '';
});

const waLink = computed(() => {
    const number = fallbackWa.value || companyWhatsapp.value;
    if (!number) return '';
    return `https://wa.me/${number}?text=${encodeURIComponent('Halo, saya ingin bertanya tentang layanan Anda.')}`;
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
    if (open.value) scrollToBottom();
};

const escapeHtml = (s: string): string =>
    s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

const inlineMarkdown = (s: string): string => {
    return s
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>')
        .replace(/`(.+?)`/g, '<code class="rounded bg-muted px-1 py-0.5 font-mono text-xs">$1</code>');
};

// Render markdown ringan (bold/italic/code/bullet list) menjadi HTML yang aman.
const formatMarkdown = (text: string): string => {
    const lines = text.split('\n');
    let html = '';
    let listOpen = false;
    let para: string[] = [];

    const closeList = () => {
        if (listOpen) {
            html += '</ul>';
            listOpen = false;
        }
    };
    const flushPara = () => {
        if (para.length) {
            html += '<p>' + para.map(inlineMarkdown).join('<br>') + '</p>';
            para = [];
        }
    };

    for (const raw of lines) {
        const line = escapeHtml(raw);

        if (/^\s*[-*]\s+/.test(raw)) {
            flushPara();
            if (!listOpen) {
                html += '<ul class="my-1 list-disc space-y-1 pl-4">';
                listOpen = true;
            }
            const item = raw.replace(/^\s*[-*]\s+/, '');
            html += '<li>' + inlineMarkdown(item) + '</li>';
            continue;
        }

        closeList();
        if (line.trim() === '') {
            flushPara();
            continue;
        }

        para.push(line);
    }

    flushPara();
    closeList();

    return html;
};

const send = async () => {
    const text = input.value.trim();
    if (!text || loading.value) return;

    messages.value.push({ role: 'user', content: text });
    input.value = '';
    loading.value = true;
    scrollToBottom();

    const history = messages.value
        .filter((m) => m !== messages.value[messages.value.length - 1])
        .slice(-20)
        .map((m) => ({ role: m.role, content: m.content }));

    try {
        const res = await fetch('/api/public/ai-chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({ message: text, history }),
        });

        const data = await res.json();
        const reply = data.reply || 'Maaf, terjadi kesalahan. Silakan hubungi kami via WhatsApp.';
        messages.value.push({ role: 'assistant', content: reply });
        fallbackWa.value = data.fallback_whatsapp || null;
    } catch {
        messages.value.push({
            role: 'assistant',
            content: 'Maaf, saya sedang tidak bisa terhubung. Silakan hubungi kami via WhatsApp.',
        });
        fallbackWa.value = companyWhatsapp.value || null;
    } finally {
        loading.value = false;
        scrollToBottom();
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        send();
    }
};

onMounted(() => {
    messages.value.push({
        role: 'assistant',
        content: 'Halo! Saya asisten virtual. Ada yang bisa saya bantu seputar layanan hosting, domain, atau pembuatan website?',
    });
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
            <div v-if="open" class="flex w-[400px] max-w-[calc(100vw-2rem)] flex-col overflow-hidden rounded-2xl border bg-card shadow-2xl">
                <!-- Header -->
                <div class="flex items-center gap-2 border-b bg-muted/40 px-4 py-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                        <MessageCircle class="h-4 w-4 text-primary" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold leading-tight">Customer Service</p>
                        <p class="text-xs text-muted-foreground">Asisten virtual</p>
                    </div>
                    <Button variant="ghost" size="icon" class="h-8 w-8 cursor-pointer" @click="open = false">
                        <X class="h-4 w-4" />
                    </Button>
                </div>

                <!-- Messages -->
                <div ref="container" class="flex-1 space-y-3 overflow-y-auto p-4" style="max-height: 60vh; min-height: 200px;">
                    <div v-for="(m, i) in messages" :key="i" class="flex gap-2" :class="m.role === 'user' ? 'justify-end' : ''">
                        <div v-if="m.role === 'user'" class="max-w-[80%] whitespace-pre-wrap rounded-2xl rounded-br-md bg-primary px-3.5 py-2 text-sm text-primary-foreground">
                            {{ m.content }}
                        </div>
                        <div v-else class="flex max-w-[85%] gap-2">
                            <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10">
                                <MessageCircle class="h-3.5 w-3.5 text-primary" />
                            </div>
                            <div class="max-w-[85%] whitespace-pre-wrap rounded-2xl rounded-bl-md bg-muted/50 px-3.5 py-2.5 text-sm leading-relaxed" v-html="formatMarkdown(m.content)"></div>
                        </div>
                    </div>

                    <div v-if="loading" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <Loader2 class="h-3 w-3 animate-spin" />
                        Mengetik...
                    </div>
                </div>

                <!-- Tombol WhatsApp (muncul saat AI mentok) -->
                <div v-if="waLink" class="px-4 pb-2">
                    <Button asChild variant="outline" class="w-full cursor-pointer">
                        <a :href="waLink" target="_blank" rel="noopener noreferrer">Chat via WhatsApp</a>
                    </Button>
                </div>

                <!-- Input -->
                <div class="border-t p-3">
                    <div class="flex gap-2">
                        <Textarea
                            v-model="input"
                            placeholder="Tulis pertanyaan..."
                            :disabled="loading"
                            class="max-h-28 min-h-[44px] resize-none text-sm"
                            rows="1"
                            @keydown="handleKeydown"
                        />
                        <Button @click="send" :disabled="loading || !input.trim()" class="h-[44px] w-[44px] shrink-0 cursor-pointer">
                            <Send class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Tombol floating -->
        <Button
            @click="toggle"
            class="h-14 w-14 cursor-pointer rounded-full shadow-lg"
            :aria-label="open ? 'Tutup chat' : 'Buka chat customer service'"
        >
            <MessageCircle v-if="!open" class="h-6 w-6" />
            <X v-else class="h-6 w-6" />
        </Button>
    </div>
</template>
