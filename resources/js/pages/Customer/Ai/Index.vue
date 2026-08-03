<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowDownCircle, ArrowUpCircle, Check, Coins, Copy, KeyRound, RefreshCw, Server, ShoppingCart } from 'lucide-vue-next';
import { ref } from 'vue';

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

const regenerateKey = async () => {
    if (!confirm('Generate API key baru? Key lama langsung tidak berlaku.')) return;
    const res = await fetch('/customer/ai/api-key', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
    if (res.ok) {
        const data = await res.json();
        apiKey.value = data.api_key;
        showKey.value = true;
    }
};

const formatRupiah = (n: number): string =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 2 }).format(n);

// Harga per 1 juta token: rate per 1K × harga 1 kredit × 1000.
const priceInput = (m: Model): string => (props.credit_price === null ? '—' : formatRupiah(Number(m.input_rate) * props.credit_price * 1000));
const priceOutput = (m: Model): string => (props.credit_price === null ? '—' : formatRupiah(Number(m.output_rate) * props.credit_price * 1000));

const maskKey = (key: string): string => `${key.slice(0, 8)}••••••••${key.slice(-4)}`;

const formatDate = (d: string) => new Date(d).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' });
</script>

<template>
    <CustomerLayout :breadcrumbs="breadcrumbs">
        <Head title="Token AI" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">Token AI</h1>
                    <p class="text-muted-foreground">Kelola sisa saldo, API key, dan harga model untuk dipasang di Hermes agent / code editor Anda.</p>
                </div>
                <Link href="/customer/ai/packages">
                    <Button class="cursor-pointer"><ShoppingCart class="mr-2 h-4 w-4" /> Beli Saldo</Button>
                </Link>
            </div>

            <!-- Saldo + Endpoint -->
            <div class="grid gap-4 lg:grid-cols-5">
                <Card class="flex flex-col lg:col-span-2">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2"><Coins class="h-5 w-5 text-amber-500" /> Sisa Saldo</CardTitle>
                    </CardHeader>
                    <CardContent class="flex flex-1 flex-col">
                        <div class="flex items-baseline gap-2">
                            <span class="text-5xl font-bold">{{ balance.toLocaleString('id-ID') }}</span>
                            <span class="text-sm text-muted-foreground">kredit</span>
                        </div>
                        <p class="mt-2 text-sm text-muted-foreground">Saldo kredit. Setiap pemakaian memotong saldo sesuai jumlah token × harga model.</p>
                        <p v-if="credit_price !== null" class="mt-1 text-sm text-muted-foreground">Referensi harga: 1 kredit ≈ {{ formatRupiah(credit_price) }}</p>
                        <div class="mt-auto pt-5">
                            <Link href="/customer/ai/packages" class="block">
                                <Button class="w-full cursor-pointer"><ShoppingCart class="mr-2 h-4 w-4" /> Beli Saldo</Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <Card class="lg:col-span-3">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2"><Server class="h-5 w-5 text-primary" /> Endpoint API</CardTitle>
                        <CardDescription>Isi di Hermes agent atau code editor (OpenAI-compatible)</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
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
                            <Button size="sm" variant="outline" class="mt-2 cursor-pointer" @click="regenerateKey">
                                <RefreshCw class="mr-1.5 h-3.5 w-3.5" /> Generate / Ganti Key
                            </Button>
                        </div>
                        <div>
                            <p class="mb-1 text-xs font-medium text-muted-foreground">Contoh request</p>
                            <pre class="overflow-x-auto rounded-md bg-muted px-3 py-2 text-xs font-mono">POST {{ endpoint }}/chat/completions
Authorization: Bearer &lt;API_KEY&gt;
{"model": "grok-4.5", "messages": [{"role": "user", "content": "halo"}]}</pre>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Harga per model -->
            <Card>
                <CardHeader>
                    <CardTitle>Harga Token per Model</CardTitle>
                    <CardDescription v-if="credit_price !== null">
                        Harga per 1 juta token input/output, referensi 1 kredit ≈ {{ formatRupiah(credit_price) }}
                    </CardDescription>
                    <CardDescription v-else>Tambahkan paket kredit aktif di panel admin agar harga rupiah tampil.</CardDescription>
                </CardHeader>
                <CardContent class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="px-3 py-3">Model</th>
                                <th class="px-3 py-3">Provider</th>
                                <th class="px-3 py-3">Input / 1M token</th>
                                <th class="px-3 py-3">Output / 1M token</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="m in models" :key="m.id" class="border-b transition-colors last:border-0 hover:bg-muted/40">
                                <td class="px-3 py-3">
                                    <div class="font-mono font-medium">{{ m.model_key }}</div>
                                    <div v-if="m.display_name" class="text-xs text-muted-foreground">{{ m.display_name }}</div>
                                </td>
                                <td class="px-3 py-3">{{ m.provider?.name || '-' }}</td>
                                <td class="px-3 py-3">{{ priceInput(m) }}</td>
                                <td class="px-3 py-3">{{ priceOutput(m) }}</td>
                            </tr>
                            <tr v-if="models.length === 0">
                                <td colspan="4" class="px-3 py-8 text-center text-muted-foreground">Belum ada model aktif.</td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>

            <!-- Riwayat usage -->
            <Card>
                <CardHeader>
                    <CardTitle>Riwayat Token &amp; Usage</CardTitle>
                    <CardDescription>20 transaksi terakhir (pembelian & pemakaian)</CardDescription>
                </CardHeader>
                <CardContent class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="px-3 py-3">Waktu</th>
                                <th class="px-3 py-3">Tipe</th>
                                <th class="px-3 py-3">Token</th>
                                <th class="px-3 py-3">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="t in transactions" :key="t.id" class="border-b transition-colors last:border-0 hover:bg-muted/40">
                                <td class="px-3 py-3 text-xs text-muted-foreground">{{ formatDate(t.created_at) }}</td>
                                <td class="px-3 py-3">
                                    <Badge :variant="t.type === 'in' ? 'default' : 'secondary'">
                                        {{ t.source === 'purchase' ? 'Pembelian' : t.source === 'usage' ? 'Pemakaian' : 'Penyesuaian' }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center gap-1 font-semibold" :class="t.credits > 0 ? 'text-green-600' : 'text-red-500'">
                                        <ArrowUpCircle v-if="t.credits > 0" class="h-4 w-4" />
                                        <ArrowDownCircle v-else class="h-4 w-4" />
                                        {{ t.credits > 0 ? '+' : '' }}{{ t.credits.toLocaleString('id-ID') }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-xs text-muted-foreground">
                                    <template v-if="t.model">{{ t.model.model_key }} · </template>
                                    <template v-if="t.package">{{ t.package.name }} · </template>
                                    {{ t.description || '-' }}
                                </td>
                            </tr>
                            <tr v-if="transactions.length === 0">
                                <td colspan="4" class="px-3 py-8 text-center text-muted-foreground">Belum ada transaksi. Beli token untuk mulai memakai.</td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        </div>
    </CustomerLayout>
</template>
