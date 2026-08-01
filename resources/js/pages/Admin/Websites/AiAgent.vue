<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Bot, CheckCircle2, Loader2, Send, Sparkles, Trash2, XCircle, Zap } from 'lucide-vue-next';
import { nextTick, ref } from 'vue';

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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Manage Website', href: '/admin/websites' },
    { title: 'AI Agent', href: '/admin/websites/ai' },
];

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
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content,
            },
            body: JSON.stringify({ message: text }),
        });

        const data = await res.json();

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

const clearChat = () => {
    messages.value = [];
};

const formatActionName = (action: string): string => {
    const names: Record<string, string> = {
        check_updates: 'Cek Update',
        update_wp: 'Update WordPress',
        update_plugins: 'Update Plugin',
        create_article: 'Buat Artikel',
        audit_seo: 'Audit SEO',
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
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="AI Agent - Manage Website" />

        <div class="flex flex-col h-[calc(100vh-120px)] max-w-3xl mx-auto">
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
                <Button variant="ghost" size="sm" @click="clearChat" class="cursor-pointer" v-if="messages.length > 0">
                    <Trash2 class="mr-2 h-4 w-4" /> Bersihkan
                </Button>
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
    </AppLayout>
</template>
