<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowDownCircle, ArrowRight, ArrowUpCircle, BookOpen, Check, ChevronLeft, ChevronRight, Copy, Cpu, Download, History, KeyRound, RefreshCw, Server, ShoppingCart, TrendingUp, Zap } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

interface Model {
    id: number;
    model_key: string;
    display_name: string | null;
    input_rate: string;
    output_rate: string;
    provider: { id: number; name: string } | null;
}

interface Transaction {
    id: number;
    type: 'in' | 'out';
    source: 'purchase' | 'usage' | 'manual_adjust';
    credits: number;
    tokens_input: number | null;
    tokens_output: number | null;
    description: string | null;
    created_at: string;
    model: { model_key: string } | null;
    package: { name: string } | null;
}

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
}

interface Props {
    balance: number;
    total_credits: number;
    api_key: string | null;
    endpoint: string;
    models: Model[];
    credit_price: number | null;
    transactions: Paginated<Transaction>;
    usage_daily: Array<{ date: string; label: string; credits: number; runs: number }>;
    tokens_today: number;
    tokens_30d: number;
    tokens_total: number;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/customer/dashboard' },
    { title: 'Token AI', href: '/customer/ai' },
];

const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';

const apiKey = ref(props.api_key);
const showKey = ref(false);
const copiedEndpoint = ref(false);
const copiedKey = ref(false);
const showRegenModal = ref(false);
const activeTab = ref<'endpoint' | 'pricing' | 'history' | 'docs'>('pricing');

const tabs = [
    { key: 'endpoint', label: 'Endpoint API', icon: Server },
    { key: 'pricing', label: 'Harga Token', icon: Cpu },
    { key: 'history', label: 'Riwayat Usage', icon: History },
    { key: 'docs', label: 'Dokumentasi', icon: BookOpen },
] as const;

const TAB_STORAGE_KEY = 'customer-ai-active-tab';

onMounted(() => {
    try {
        const saved = localStorage.getItem(TAB_STORAGE_KEY);
        if (saved === 'endpoint' || saved === 'pricing' || saved === 'history' || saved === 'docs') {
            activeTab.value = saved;
        }
    } catch {
        // abaikan bila storage tak tersedia
    }
});

watch(activeTab, (value) => {
    try {
        localStorage.setItem(TAB_STORAGE_KEY, value);
    } catch {
        // abaikan
    }
});

const totalTransactions = computed(() => props.transactions.total);
const usageTransactions = computed(() => props.transactions.data.filter(t => t.type === 'out').length);
const purchaseTransactions = computed(() => props.transactions.data.filter(t => t.source === 'purchase').length);

const copyText = async (text: string) => {
    try {
        await navigator.clipboard.writeText(text);
    } catch {
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
    }
};

const copyEndpoint = async () => {
    await copyText(props.endpoint);
    copiedEndpoint.value = true;
    setTimeout(() => (copiedEndpoint.value = false), 1500);
};

const copyApiKey = async () => {
    if (!apiKey.value) return;
    await copyText(apiKey.value);
    copiedKey.value = true;
    setTimeout(() => (copiedKey.value = false), 1500);
};

const openRegenModal = () => { showRegenModal.value = true; };

const confirmRegen = async () => {
    showRegenModal.value = false;
    const res = await fetch('/customer/ai/api-key', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
    if (res.ok) {
        const data = await res.json();
        apiKey.value = data.api_key;
        showKey.value = true;
    }
};

const formatRupiah = (n: number): string =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 2 }).format(n);

// Tanpa paket kredit aktif (credit_price null), tampilkan rate kredit/1M agar kolom tetap berisi.
const formatNum = (n: number): string => new Intl.NumberFormat('id-ID').format(n);
const priceInput = (m: Model): string =>
    props.credit_price === null ? `${formatNum(Number(m.input_rate))} kredit / 1M` : formatRupiah(Number(m.input_rate) * props.credit_price);
const priceOutput = (m: Model): string =>
    props.credit_price === null ? `${formatNum(Number(m.output_rate))} kredit / 1M` : formatRupiah(Number(m.output_rate) * props.credit_price);

const maskKey = (key: string): string => `${key.slice(0, 8)}••••••••${key.slice(-4)}`;

const formatDate = (d: string) => new Date(d).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' });

const creditPercent = computed(() => {
    if (!props.total_credits) return 0;
    return Math.min(100, Math.max(0, Math.round((props.balance / props.total_credits) * 100)));
});

const usageDailyData = computed(() => ({
    labels: props.usage_daily.map((d) => d.label),
    datasets: [
        {
            label: 'Kredit Terpakai',
            data: props.usage_daily.map((d) => d.credits),
            backgroundColor: 'hsl(var(--primary) / 0.85)',
            borderRadius: 4,
        },
    ],
}));

const usageBarOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx: { parsed: { y: number }; dataIndex: number }) =>
                    `${props.usage_daily[ctx.dataIndex].runs} request · ${ctx.parsed.y.toLocaleString('id-ID')} kredit`,
            },
        },
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 9 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 15 } },
        y: { beginAtZero: true, ticks: { font: { size: 10 } }, grid: { color: 'hsl(var(--border) / 0.6)' } },
    },
};

const usage30dTotal = computed(() => props.usage_daily.reduce((sum, d) => sum + d.credits, 0));
const usage30dRuns = computed(() => props.usage_daily.reduce((sum, d) => sum + d.runs, 0));

// Riwayat Usage: filter, paginasi, export
const typeFilter = ref<string>('');
const modelFilter = ref<number>(0);
const historyRows = ref<Transaction[]>(props.transactions.data);
const historyPage = ref(props.transactions.current_page);
const historyLastPage = ref(props.transactions.last_page);
const historyTotal = ref(props.transactions.total);

const historyQuery = (page: number) => {
    const params = new URLSearchParams();
    if (typeFilter.value) params.set('type', typeFilter.value);
    if (modelFilter.value) params.set('model_id', String(modelFilter.value));
    params.set('page', String(page));
    return `/customer/ai/history?${params.toString()}`;
};

const loadHistory = async (page: number) => {
    const res = await fetch(historyQuery(page));
    if (!res.ok) return;
    const d = await res.json();
    historyRows.value = d.data;
    historyPage.value = d.current_page;
    historyLastPage.value = d.last_page;
    historyTotal.value = d.total;
};

const applyHistoryFilters = () => loadHistory(1);
const historyPrev = () => historyPage.value > 1 && loadHistory(historyPage.value - 1);
const historyNext = () => historyPage.value < historyLastPage.value && loadHistory(historyPage.value + 1);
const historyExportUrl = computed(() => {
    const params = new URLSearchParams();
    if (typeFilter.value) params.set('type', typeFilter.value);
    if (modelFilter.value) params.set('model_id', String(modelFilter.value));
    const qs = params.toString();
    return `/customer/ai/export${qs ? '?' + qs : ''}`;
});
</script>

<template>
    <Head title="Token AI" />

    <CustomerLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4 sm:space-y-6 sm:p-6">
            <!-- Hero Card -->
            <Card class="relative overflow-hidden border-border/60 bg-card/70 shadow-sm backdrop-blur">
                <div class="pointer-events-none absolute inset-0 opacity-60 dark:opacity-80">
                    <div class="absolute -inset-24 bg-[radial-gradient(closest-side,rgba(245,158,11,0.16),transparent_65%)]"></div>
                    <div class="absolute -right-24 -top-32 h-96 w-96 bg-[radial-gradient(closest-side,rgba(34,211,238,0.14),transparent_60%)]"></div>
                    <div class="absolute inset-0 bg-[linear-gradient(to_right,transparent_0,rgba(245,158,11,0.05)_50%,transparent_100%)]"></div>
                </div>
                <CardContent class="relative p-4 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="mb-2 inline-flex items-center gap-2 rounded-full border border-border/60 bg-background/60 px-2.5 py-1 text-xs text-muted-foreground">
                                <Cpu class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />
                                <span>AI &amp; LLM</span>
                            </div>
                            <h1 class="font-heading text-2xl font-medium tracking-tight sm:text-3xl">Token AI</h1>
                            <p class="mt-1 text-sm text-muted-foreground sm:text-base">Kelola saldo, API key, dan harga model untuk dipasang di Hermes agent / code editor Anda.</p>
                        </div>
                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-[240px]">
                            <Button asChild size="sm" class="w-full justify-between">
                                <Link href="/customer/ai/packages">
                                    <span class="inline-flex items-center gap-2">
                                        <ShoppingCart class="h-4 w-4" />
                                        Beli Saldo
                                    </span>
                                    <ArrowRight class="h-4 w-4 opacity-80" />
                                </Link>
                            </Button>
                            <Button asChild variant="outline" size="sm" class="w-full justify-between">
                                <Link :href="`${endpoint}/chat/completions`" target="_blank">
                                    <span class="inline-flex items-center gap-2">
                                        <Server class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                                        Dokumentasi API
                                    </span>
                                    <ArrowRight class="h-4 w-4 opacity-70" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Stat Cards -->
            <div class="grid gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4">
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Sisa Saldo</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ balance.toLocaleString('id-ID') }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Model Tersedia</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ models.length }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Pemakaian</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ usageTransactions }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Pembelian</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ purchaseTransactions }}</CardTitle>
                    </CardHeader>
                </Card>
            </div>

            <!-- Penggunaan Token -->
            <Card class="rounded-lg border-border/60 shadow-sm">
                <CardHeader class="pb-2">
                    <CardTitle class="flex items-center gap-2 text-base">
                        <BookOpen class="h-4 w-4 text-muted-foreground" /> Penggunaan Token
                    </CardTitle>
                    <CardDescription>Total token (input + output) dari pemakaian API</CardDescription>
                </CardHeader>
                <CardContent class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg border border-border/60 p-3">
                        <div class="text-xs font-medium text-muted-foreground">Hari Ini</div>
                        <div class="mt-1 text-xl font-semibold tabular-nums">{{ tokens_today.toLocaleString('id-ID') }}</div>
                    </div>
                    <div class="rounded-lg border border-border/60 p-3">
                        <div class="text-xs font-medium text-muted-foreground">30 Hari Terakhir</div>
                        <div class="mt-1 text-xl font-semibold tabular-nums">{{ tokens_30d.toLocaleString('id-ID') }}</div>
                    </div>
                    <div class="rounded-lg border border-border/60 p-3">
                        <div class="text-xs font-medium text-muted-foreground">Total</div>
                        <div class="mt-1 text-xl font-semibold tabular-nums">{{ tokens_total.toLocaleString('id-ID') }}</div>
                    </div>
                </CardContent>
            </Card>

            <!-- Progress bar sisa kredit -->
            <Card class="rounded-lg border-border/60 shadow-sm">
                <CardContent class="pt-6">
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="font-medium">Sisa Kredit</span>
                        <span class="font-semibold tabular-nums">{{ creditPercent }}%</span>
                    </div>
                    <div
                        class="h-3 w-full overflow-hidden rounded-full"
                        style="background-color: rgba(148, 163, 184, 0.2);"
                    >
                        <div
                            class="h-full rounded-full"
                            :style="{
                                width: creditPercent + '%',
                                background: creditPercent <= 20
                                    ? 'linear-gradient(90deg, #ef4444, #f87171)'
                                    : creditPercent <= 50
                                        ? 'linear-gradient(90deg, #f59e0b, #fbbf24)'
                                        : 'linear-gradient(90deg, #10b981, #34d399)',
                                transition: 'width 0.4s ease',
                            }"
                        />
                    </div>
                    <p class="mt-2 text-xs text-muted-foreground">
                        {{ balance.toLocaleString('id-ID') }} dari {{ total_credits.toLocaleString('id-ID') }} kredit terpakai
                    </p>
                </CardContent>
            </Card>

            <!-- Grafik penggunaan 30 hari terakhir -->
            <Card class="rounded-lg border-border/60 shadow-sm">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <TrendingUp class="h-4 w-4 text-muted-foreground" /> Penggunaan 30 Hari Terakhir
                        </CardTitle>
                        <CardDescription>Kredit terpakai per hari dari request API</CardDescription>
                    </div>
                    <div class="flex gap-4 text-sm">
                        <span class="text-muted-foreground">{{ usage30dRuns.toLocaleString('id-ID') }} request</span>
                        <span class="font-semibold">{{ usage30dTotal.toLocaleString('id-ID') }} kredit</span>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="usage30dTotal > 0" class="h-56">
                        <Bar :data="usageDailyData" :options="usageBarOptions" />
                    </div>
                    <div v-else class="flex h-56 flex-col items-center justify-center gap-2 text-muted-foreground">
                        <Zap class="h-8 w-8 opacity-50" />
                        <p class="text-sm">Belum ada pemakaian dalam 30 hari terakhir.</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Tabbed: Endpoint API / Harga Token / Riwayat -->
            <Card class="overflow-hidden rounded-xl border-border/60 shadow-sm">
                <CardHeader class="border-b border-border/60 pb-0">
                    <div class="flex w-full items-center gap-1 border-b border-transparent">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            @click="activeTab = tab.key"
                            class="relative inline-flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors cursor-pointer focus-visible:outline-none"
                            :class="activeTab === tab.key ? 'text-foreground' : 'text-muted-foreground hover:text-foreground'"
                        >
                            <component :is="tab.icon" class="h-4 w-4" />
                            {{ tab.label }}
                            <span
                                v-if="activeTab === tab.key"
                                class="absolute inset-x-3 -bottom-px h-0.5 rounded-full bg-amber-600 dark:bg-amber-400"
                            />
                        </button>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <!-- Endpoint API -->
                    <div v-if="activeTab === 'endpoint'" class="p-4 sm:p-6">
                        <div class="grid gap-5 lg:grid-cols-2">
                            <!-- Base URL -->
                            <div class="space-y-2">
                                <Label class="text-xs font-medium text-muted-foreground">Base URL</Label>
                                <div class="group flex items-center gap-2 rounded-lg border border-border/70 bg-muted/40 px-3 py-2.5 transition-colors focus-within:border-amber-600/50">
                                    <Server class="h-4 w-4 shrink-0 text-muted-foreground" />
                                    <code class="min-w-0 flex-1 truncate text-sm text-foreground">{{ endpoint }}</code>
                                    <button
                                        class="shrink-0 inline-flex items-center gap-1 rounded-md border border-transparent px-2 py-1 text-xs font-medium text-muted-foreground transition-colors hover:border-border hover:bg-background hover:text-foreground cursor-pointer"
                                        title="Salin base URL"
                                        @click="copyEndpoint"
                                    >
                                        <Check v-if="copiedEndpoint" class="h-3.5 w-3.5 text-green-600" />
                                        <Copy v-else class="h-3.5 w-3.5" />
                                        {{ copiedEndpoint ? 'Tersalin' : 'Salin' }}
                                    </button>
                                </div>
                            </div>

                            <!-- API Key -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <Label class="text-xs font-medium text-muted-foreground">API Key</Label>
                                    <button
                                        v-if="apiKey"
                                        class="text-xs font-medium text-amber-600 hover:text-amber-500 dark:text-amber-400 cursor-pointer"
                                        @click="showKey = !showKey"
                                    >
                                        {{ showKey ? 'Sembunyikan' : 'Lihat' }}
                                    </button>
                                </div>
                                <div class="flex items-center gap-2 rounded-lg border border-border/70 bg-muted/40 px-3 py-2.5 transition-colors focus-within:border-amber-600/50">
                                    <KeyRound class="h-4 w-4 shrink-0 text-muted-foreground" />
                                    <code v-if="apiKey" class="min-w-0 flex-1 truncate text-sm text-foreground">{{ showKey ? apiKey : maskKey(apiKey) }}</code>
                                    <span v-else class="flex-1 text-sm text-muted-foreground">Belum ada API key</span>
                                    <button
                                        v-if="apiKey"
                                        class="shrink-0 inline-flex items-center gap-1 rounded-md border border-transparent px-2 py-1 text-xs font-medium text-muted-foreground transition-colors hover:border-border hover:bg-background hover:text-foreground cursor-pointer"
                                        title="Salin API key"
                                        @click="copyApiKey"
                                    >
                                        <Check v-if="copiedKey" class="h-3.5 w-3.5 text-green-600" />
                                        <Copy v-else class="h-3.5 w-3.5" />
                                        {{ copiedKey ? 'Tersalin' : 'Salin' }}
                                    </button>
                                </div>
                                <Button size="sm" variant="outline" class="cursor-pointer" @click="openRegenModal">
                                    <RefreshCw class="mr-1.5 h-3.5 w-3.5" /> Generate / Ganti Key
                                </Button>
                            </div>
                        </div>

                        <!-- Contoh request -->
                        <div class="mt-6 space-y-2">
                            <div class="flex items-center justify-between">
                                <Label class="text-xs font-medium text-muted-foreground">Contoh request</Label>
                                <span class="rounded bg-muted px-2 py-0.5 text-[11px] font-medium text-muted-foreground">cURL</span>
                            </div>
                            <div class="overflow-hidden rounded-lg border border-border/70 bg-zinc-950 dark:bg-zinc-900">
                                <div class="flex items-center gap-1.5 border-b border-white/10 px-3 py-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-red-500/80" />
                                    <span class="h-2.5 w-2.5 rounded-full bg-amber-500/80" />
                                    <span class="h-2.5 w-2.5 rounded-full bg-green-500/80" />
                                </div>
                                <pre class="overflow-x-auto p-4 text-xs leading-relaxed text-zinc-100"><code>curl {{ endpoint }}/chat/completions \
  -H "Authorization: Bearer &lt;API_KEY&gt;" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "grok-4.5",
    "messages": [{"role": "user", "content": "halo"}]
  }'</code></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Harga Token -->
                    <div v-if="activeTab === 'pricing'" class="p-4 sm:p-6">
                        <p v-if="credit_price !== null" class="mb-4 text-sm text-muted-foreground">
                            Harga per 1 juta token input/output, referensi 1 kredit &approx; {{ formatRupiah(credit_price) }}
                        </p>
                        <p v-else class="mb-4 text-sm text-muted-foreground">Belum ada paket kredit aktif — harga menampilkan rate kredit per 1 juta token.</p>
                        <div class="overflow-hidden rounded-lg border border-border/60">
                            <div class="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Model</TableHead>
                                            <TableHead>Provider</TableHead>
                                            <TableHead>Input / 1M token</TableHead>
                                            <TableHead>Output / 1M token</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="m in models" :key="m.id">
                                            <TableCell>
                                                <div class="font-mono font-medium">{{ m.model_key }}</div>
                                                <div v-if="m.display_name" class="text-xs text-muted-foreground">{{ m.display_name }}</div>
                                            </TableCell>
                                            <TableCell>{{ m.provider?.name || '-' }}</TableCell>
                                            <TableCell>{{ priceInput(m) }}</TableCell>
                                            <TableCell>{{ priceOutput(m) }}</TableCell>
                                        </TableRow>
                                        <TableRow v-if="models.length === 0">
                                            <TableCell colspan="4" class="text-center text-muted-foreground">Belum ada model aktif.</TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Usage -->
                    <div v-if="activeTab === 'history'" class="p-4 sm:p-6">
                        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                            <div class="flex flex-wrap items-end gap-3">
                                <div class="space-y-1">
                                    <Label class="text-xs font-medium text-muted-foreground">Tipe</Label>
                                    <select
                                        v-model="typeFilter"
                                        @change="applyHistoryFilters"
                                        class="h-9 rounded-md border border-border/70 bg-background px-3 text-sm focus-visible:outline-none"
                                    >
                                        <option value="">Semua</option>
                                        <option value="in">Pembelian / Masuk</option>
                                        <option value="out">Pemakaian</option>
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-xs font-medium text-muted-foreground">Model</Label>
                                    <select
                                        v-model="modelFilter"
                                        @change="applyHistoryFilters"
                                        class="h-9 rounded-md border border-border/70 bg-background px-3 text-sm focus-visible:outline-none"
                                    >
                                        <option :value="0">Semua</option>
                                        <option v-for="m in models" :key="m.id" :value="m.id">{{ m.model_key }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-muted-foreground">{{ historyTotal.toLocaleString('id-ID') }} transaksi</span>
                                <Button asChild variant="outline" size="sm">
                                    <a :href="historyExportUrl" download>
                                        <Download class="h-4 w-4" /> Export CSV
                                    </a>
                                </Button>
                            </div>
                        </div>
                        <div class="overflow-hidden rounded-lg border border-border/60">
                            <div class="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Waktu</TableHead>
                                            <TableHead>Tipe</TableHead>
                                            <TableHead>Kredit</TableHead>
                                            <TableHead>Token In / Out</TableHead>
                                            <TableHead>Detail</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="t in historyRows" :key="t.id">
                                            <TableCell class="text-xs text-muted-foreground">{{ formatDate(t.created_at) }}</TableCell>
                                            <TableCell>
                                                <Badge :variant="t.type === 'in' ? 'default' : 'secondary'">
                                                    {{ t.source === 'purchase' ? 'Pembelian' : t.source === 'usage' ? 'Pemakaian' : 'Penyesuaian' }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <span class="inline-flex items-center gap-1 font-semibold" :class="t.credits > 0 ? 'text-green-600' : 'text-red-500'">
                                                    <ArrowUpCircle v-if="t.credits > 0" class="h-4 w-4" />
                                                    <ArrowDownCircle v-else class="h-4 w-4" />
                                                    {{ t.credits > 0 ? '+' : '' }}{{ t.credits.toLocaleString('id-ID') }}
                                                </span>
                                            </TableCell>
                                            <TableCell class="text-xs tabular-nums">
                                                <template v-if="t.tokens_input !== null || t.tokens_output !== null">
                                                    <span class="text-muted-foreground">In {{ (t.tokens_input || 0).toLocaleString('id-ID') }}</span>
                                                    &middot;
                                                    <span class="text-muted-foreground">Out {{ (t.tokens_output || 0).toLocaleString('id-ID') }}</span>
                                                </template>
                                                <span v-else class="text-muted-foreground">-</span>
                                            </TableCell>
                                            <TableCell class="text-xs text-muted-foreground">
                                                <template v-if="t.model">{{ t.model.model_key }} &middot; </template>
                                                <template v-if="t.package">{{ t.package.name }} &middot; </template>
                                                {{ t.description || '-' }}
                                            </TableCell>
                                        </TableRow>
                                        <TableRow v-if="historyRows.length === 0">
                                            <TableCell colspan="5" class="text-center text-muted-foreground">Belum ada transaksi. Beli token untuk mulai memakai.</TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                        </div>
                        <!-- Paginasi -->
                        <div v-if="historyLastPage > 1" class="mt-4 flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Halaman {{ historyPage }} dari {{ historyLastPage }}</span>
                            <div class="flex gap-2">
                                <Button variant="outline" size="sm" class="cursor-pointer" :disabled="historyPage <= 1" @click="historyPrev">
                                    <ChevronLeft class="h-4 w-4" /> Sebelumnya
                                </Button>
                                <Button variant="outline" size="sm" class="cursor-pointer" :disabled="historyPage >= historyLastPage" @click="historyNext">
                                    Berikutnya <ChevronRight class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>

    <!-- Dokumentasi -->
    <div v-if="activeTab === 'docs'" class="space-y-6 p-4 sm:p-6">
        <p class="text-sm text-muted-foreground">
            API ini kompatibel dengan <span class="font-medium text-foreground">OpenAI Chat Completions</span>.
            Bisa dipasang di Trae, Cline, Roo Code, Continue, atau code editor / AI agent lain yang mendukung
            provider <em>OpenAI-compatible</em>.
        </p>

        <!-- Info dasar -->
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-border/60 p-3">
                <div class="text-xs font-medium text-muted-foreground">Base URL</div>
                <div class="mt-1 break-all font-mono text-sm font-medium">{{ endpoint }}</div>
            </div>
            <div class="rounded-lg border border-border/60 p-3">
                <div class="text-xs font-medium text-muted-foreground">Autentikasi</div>
                <div class="mt-1 break-all font-mono text-sm font-medium">Authorization: Bearer &lt;API_KEY&gt;</div>
            </div>
            <div class="rounded-lg border border-border/60 p-3">
                <div class="text-xs font-medium text-muted-foreground">Endpoint Chat</div>
                <div class="mt-1 break-all font-mono text-sm font-medium">{{ endpoint }}/chat/completions</div>
            </div>
        </div>

        <div v-if="!apiKey" class="rounded-lg border border-amber-600/40 bg-amber-500/5 p-4 text-sm">
            Anda belum punya API key. <Link href="/customer/ai" class="font-medium text-amber-600 hover:underline dark:text-amber-400">Generate API key</Link> di tab Endpoint API terlebih dahulu.
        </div>

        <!-- Model tersedia -->
        <div class="space-y-2">
            <Label class="text-xs font-medium text-muted-foreground">Model yang tersedia</Label>
            <div class="flex flex-wrap gap-2">
                <span v-for="m in models" :key="m.id" class="rounded-full border border-border/60 bg-muted/40 px-3 py-1 font-mono text-xs">{{ m.model_key }}</span>
                <span v-if="models.length === 0" class="text-sm text-muted-foreground">Belum ada model aktif.</span>
            </div>
        </div>

        <!-- Uji cepat cURL -->
        <div class="space-y-2">
            <Label class="text-xs font-medium text-muted-foreground">Uji cepat (cURL)</Label>
            <div class="overflow-hidden rounded-lg border border-border/70 bg-zinc-950 dark:bg-zinc-900">
                <div class="flex items-center gap-1.5 border-b border-white/10 px-3 py-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-red-500/80" />
                    <span class="h-2.5 w-2.5 rounded-full bg-amber-500/80" />
                    <span class="h-2.5 w-2.5 rounded-full bg-green-500/80" />
                </div>
                <pre class="overflow-x-auto p-4 text-xs leading-relaxed text-zinc-100"><code>curl {{ endpoint }}/chat/completions \
  -H "Authorization: Bearer &lt;API_KEY&gt;" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "{{ models[0]?.model_key || 'gpt-4o-mini' }}",
    "messages": [{"role": "user", "content": "halo"}],
    "stream": false
  }'</code></pre>
            </div>
        </div>

        <!-- Trae -->
        <div class="space-y-3 rounded-lg border border-border/60 p-4">
            <div class="flex items-center gap-2">
                <div class="flex h-6 w-6 items-center justify-center rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <Zap class="h-3.5 w-3.5" />
                </div>
                <h3 class="text-sm font-semibold">Trae</h3>
            </div>
            <ol class="list-inside list-decimal space-y-1.5 text-sm text-muted-foreground">
                <li>Buka <span class="font-medium text-foreground">Settings</span> di Trae → bagian <span class="font-medium text-foreground">Model</span> / AI Provider.</li>
                <li>Tambahkan provider baru dengan tipe <span class="font-medium text-foreground">OpenAI Compatible</span> (Custom).</li>
                <li>Isi <span class="font-mono text-xs">Base URL</span> = <code class="rounded bg-muted px-1.5 py-0.5 font-mono text-xs">{{ endpoint }}</code>, dan <span class="font-mono text-xs">API Key</span> = key Anda.</li>
                <li>Pilih model dari daftar di atas (misal <code class="rounded bg-muted px-1.5 py-0.5 font-mono text-xs">{{ models[0]?.model_key || 'gpt-4o-mini' }}</code>).</li>
                <li>Aktifkan, lalu gunakan sebagai model chat di Trae.</li>
            </ol>
        </div>

        <!-- Cline / Roo Code / Continue -->
        <div class="space-y-3 rounded-lg border border-border/60 p-4">
            <div class="flex items-center gap-2">
                <div class="flex h-6 w-6 items-center justify-center rounded-md bg-cyan-500/10 text-cyan-600 dark:text-cyan-400">
                    <Cpu class="h-3.5 w-3.5" />
                </div>
                <h3 class="text-sm font-semibold">Cline, Roo Code, Continue &amp; lainnya</h3>
            </div>
            <p class="text-sm text-muted-foreground">
                Di ekstensi/agent apa pun yang mendukung provider OpenAI-compatible, masukkan kredensial berikut:
            </p>
            <div class="overflow-hidden rounded-lg border border-border/70 bg-zinc-950 dark:bg-zinc-900">
                <pre class="overflow-x-auto p-4 text-xs leading-relaxed text-zinc-100"><code>{
  "apiProvider": "openai",
  "openAiBaseUrl": "{{ endpoint }}",
  "openAiApiKey": "&lt;API_KEY&gt;",
  "openAiModelId": "{{ models[0]?.model_key || 'gpt-4o-mini' }}"
}</code></pre>
            </div>
            <p class="text-xs text-muted-foreground">
                Nilai di atas juga berlaku untuk CLI yang memakai variabel lingkungan
                <code class="rounded bg-muted px-1.5 py-0.5 font-mono">OPENAI_BASE_URL</code> dan
                <code class="rounded bg-muted px-1.5 py-0.5 font-mono">OPENAI_API_KEY</code>.
            </p>
        </div>

        <!-- Catatan -->
        <div class="space-y-1.5 rounded-lg border border-border/60 bg-muted/30 p-4 text-sm text-muted-foreground">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-foreground">Catatan</h3>
            <ul class="list-inside list-disc space-y-1">
                <li>Setiap request memotong saldo kredit sesuai model &amp; jumlah token (lihat tab <span class="font-medium text-foreground">Harga Token</span>).</li>
                <li>Streaming (<code class="rounded bg-muted px-1 font-mono text-xs">stream: true</code>) dan <em>function calling</em> (<code class="rounded bg-muted px-1 font-mono text-xs">tools</code>) didukung.</li>
                <li>Model harus persis sesuai daftar di atas (case-sensitive). Daftar model tersedia di <code class="rounded bg-muted px-1.5 py-0.5 font-mono text-xs">{{ endpoint }}/models</code>.</li>
                <li>Saldo habis → request ditolak dengan kode <span class="font-mono text-xs">429 insufficient_quota</span>; top up di halaman Paket.</li>
            </ul>
        </div>
    </div>
</CardContent>
            </Card>
        </div>
        <!-- Regenerate Key Modal -->
        <div v-if="showRegenModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showRegenModal = false"></div>
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold">Generate API Key Baru</h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Key lama akan <strong>langsung tidak berlaku</strong>. Pastikan Anda sudah memperbarui key di semua aplikasi yang menggunakannya.
                    </p>
                </div>
                <div class="flex justify-end gap-3">
                    <Button variant="outline" @click="showRegenModal = false" class="cursor-pointer">Batal</Button>
                    <Button @click="confirmRegen" class="cursor-pointer">Generate Baru</Button>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
