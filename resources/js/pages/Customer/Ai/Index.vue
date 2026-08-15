<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowDownCircle, ArrowRight, ArrowUpCircle, Check, Copy, Cpu, History, KeyRound, RefreshCw, Server, ShoppingCart } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

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
    description: string | null;
    created_at: string;
    model: { model_key: string } | null;
    package: { name: string } | null;
}

interface Props {
    balance: number;
    total_credits: number;
    api_key: string | null;
    endpoint: string;
    models: Model[];
    credit_price: number | null;
    transactions: Transaction[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/customer/dashboard' },
    { title: 'Token AI', href: '/customer/ai' },
];

const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';

const apiKey = ref(props.api_key);
const showKey = ref(false);
const copied = ref(false);
const showRegenModal = ref(false);
const activeTab = ref<'endpoint' | 'pricing' | 'history'>('pricing');

const TAB_STORAGE_KEY = 'customer-ai-active-tab';

onMounted(() => {
    try {
        const saved = sessionStorage.getItem(TAB_STORAGE_KEY);
        if (saved === 'endpoint' || saved === 'pricing' || saved === 'history') {
            activeTab.value = saved;
        }
    } catch {
        // abaikan bila storage tak tersedia
    }
});

watch(activeTab, (value) => {
    try {
        sessionStorage.setItem(TAB_STORAGE_KEY, value);
    } catch {
        // abaikan
    }
});

const totalTransactions = computed(() => props.transactions.length);
const usageTransactions = computed(() => props.transactions.filter(t => t.type === 'out').length);
const purchaseTransactions = computed(() => props.transactions.filter(t => t.source === 'purchase').length);

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
    copied.value = true;
    setTimeout(() => (copied.value = false), 1500);
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

const priceInput = (m: Model): string => (props.credit_price === null ? '—' : formatRupiah(Number(m.input_rate) * props.credit_price * 1000));
const priceOutput = (m: Model): string => (props.credit_price === null ? '—' : formatRupiah(Number(m.output_rate) * props.credit_price * 1000));

const maskKey = (key: string): string => `${key.slice(0, 8)}••••••••${key.slice(-4)}`;

const formatDate = (d: string) => new Date(d).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' });

const creditPercent = computed(() => {
    if (!props.total_credits) return 0;
    return Math.min(100, Math.max(0, Math.round((props.balance / props.total_credits) * 100)));
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

            <!-- Tabbed: Endpoint API / Harga Token / Riwayat -->
            <Card class="rounded-lg border-border/60 shadow-sm">
                <CardHeader>
                    <div class="flex items-center gap-1 rounded-lg bg-muted p-1 w-fit">
                        <button @click="activeTab = 'endpoint'" :class="['inline-flex items-center px-3 py-1.5 text-sm rounded-md transition-colors cursor-pointer', activeTab === 'endpoint' ? 'bg-background shadow-sm font-medium' : 'text-muted-foreground hover:text-foreground']">
                            <Server class="h-4 w-4 mr-1.5" /> Endpoint API
                        </button>
                        <button @click="activeTab = 'pricing'" :class="['inline-flex items-center px-3 py-1.5 text-sm rounded-md transition-colors cursor-pointer', activeTab === 'pricing' ? 'bg-background shadow-sm font-medium' : 'text-muted-foreground hover:text-foreground']">
                            <Cpu class="h-4 w-4 mr-1.5" /> Harga Token
                        </button>
                        <button @click="activeTab = 'history'" :class="['inline-flex items-center px-3 py-1.5 text-sm rounded-md transition-colors cursor-pointer', activeTab === 'history' ? 'bg-background shadow-sm font-medium' : 'text-muted-foreground hover:text-foreground']">
                            <History class="h-4 w-4 mr-1.5" /> Riwayat Usage
                        </button>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Endpoint API -->
                    <div v-if="activeTab === 'endpoint'" class="space-y-4">
                        <div>
                            <div class="mb-1 flex items-center gap-2 text-sm font-medium">
                                <Server class="h-4 w-4 text-muted-foreground" /> Base URL
                            </div>
                            <div class="flex items-center justify-between gap-2 rounded-md bg-muted px-3 py-2 text-sm font-mono">
                                <span class="truncate">{{ endpoint }}</span>
                                <button class="shrink-0 cursor-pointer text-muted-foreground hover:text-foreground" title="Salin" @click="copyText(endpoint)">
                                    <Copy v-if="!copied" class="h-4 w-4" />
                                    <Check v-else class="h-4 w-4 text-green-600" />
                                </button>
                            </div>
                        </div>
                        <div>
                            <div class="mb-1 flex items-center gap-2 text-sm font-medium">
                                <KeyRound class="h-4 w-4 text-muted-foreground" /> API Key
                            </div>
                            <div v-if="apiKey" class="flex items-center gap-2">
                                <code class="flex-1 truncate rounded-md bg-muted px-3 py-2 text-sm font-mono">{{ showKey ? apiKey : maskKey(apiKey) }}</code>
                                <Button size="sm" variant="ghost" class="cursor-pointer text-xs" @click="showKey = !showKey">{{ showKey ? 'Sembunyikan' : 'Lihat' }}</Button>
                                <Button size="sm" variant="ghost" class="cursor-pointer text-xs" title="Salin key" @click="apiKey && copyText(apiKey)">
                                    <Copy v-if="!copied" class="h-3.5 w-3.5" />
                                    <Check v-else class="h-3.5 w-3.5 text-green-600" />
                                </Button>
                            </div>
                            <div v-else class="flex items-center justify-between rounded-md border border-dashed px-3 py-2 text-sm text-muted-foreground">
                                Belum ada API key
                            </div>
                            <Button size="sm" variant="outline" class="mt-2 cursor-pointer" @click="openRegenModal">
                                <RefreshCw class="mr-1.5 h-3.5 w-3.5" /> Generate / Ganti Key
                            </Button>
                        </div>
                        <div>
                            <p class="mb-1 text-xs font-medium text-muted-foreground">Contoh request</p>
                            <pre class="overflow-x-auto rounded-md bg-muted px-3 py-2 text-xs font-mono">POST {{ endpoint }}/chat/completions
Authorization: Bearer &lt;API_KEY&gt;
Content-Type: application/json

{"model": "grok-4.5", "messages": [{"role": "user", "content": "halo"}]}</pre>
                        </div>
                    </div>

                    <!-- Harga Token -->
                    <div v-if="activeTab === 'pricing'">
                        <p v-if="credit_price !== null" class="mb-3 text-sm text-muted-foreground">
                            Harga per 1 juta token input/output, referensi 1 kredit &approx; {{ formatRupiah(credit_price) }}
                        </p>
                        <p v-else class="mb-3 text-sm text-muted-foreground">Tambahkan paket kredit aktif di panel admin agar harga rupiah tampil.</p>
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
                    <div v-if="activeTab === 'history'">
                        <p class="mb-3 text-sm text-muted-foreground">20 transaksi terakhir (pembelian &amp; pemakaian)</p>
                        <div class="overflow-hidden rounded-lg border border-border/60">
                            <div class="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Waktu</TableHead>
                                            <TableHead>Tipe</TableHead>
                                            <TableHead>Token</TableHead>
                                            <TableHead>Detail</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="t in transactions" :key="t.id">
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
                                            <TableCell class="text-xs text-muted-foreground">
                                                <template v-if="t.model">{{ t.model.model_key }} &middot; </template>
                                                <template v-if="t.package">{{ t.package.name }} &middot; </template>
                                                {{ t.description || '-' }}
                                            </TableCell>
                                        </TableRow>
                                        <TableRow v-if="transactions.length === 0">
                                            <TableCell colspan="4" class="text-center text-muted-foreground">Belum ada transaksi. Beli token untuk mulai memakai.</TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
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
