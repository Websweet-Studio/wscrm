<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ArrowDownCircle, ArrowUpCircle, Coins, Search, Cpu, Hash, Zap, TrendingUp } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Bar, Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, LineElement, PointElement, LineController, ArcElement, Title, Tooltip, Legend, Filler } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, LineController, ArcElement, Title, Tooltip, Legend, Filler);

interface Transaction {
    id: number;
    type: 'in' | 'out';
    source: 'purchase' | 'usage' | 'manual_adjust' | 'refund' | 'expired';
    credits: number;
    tokens_input: number | null;
    tokens_output: number | null;
    description: string | null;
    created_at: string;
    customer: { id: number; name: string; email: string } | null;
    package: { id: number; name: string } | null;
    invoice: { id: number; invoice_number: string } | null;
    model: { id: number; model_key: string } | null;
}

interface Analytics {
    total_tokens: number;
    input_tokens: number;
    output_tokens: number;
    total_runs: number;
    credits_spent: number;
    monthly: Array<{ month: string; tokens: number; runs: number }>;
    by_model: Array<{ model_key: string; runs: number; tokens_in: number; tokens_out: number; credits: number }>;
}

interface Props {
    transactions: { data: Transaction[]; current_page: number; last_page: number; per_page: number; total: number; links: any[] };
    analytics: Analytics;
    filters?: { customer_id?: string; type?: string; source?: string; from?: string; to?: string };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Transaksi Kredit AI', href: '/admin/ai/transactions' }];

const filters = ref({
    customer_id: props.filters?.customer_id || '',
    type: props.filters?.type || '',
    source: props.filters?.source || '',
    from: props.filters?.from || '',
    to: props.filters?.to || '',
});

const applyFilters = () => {
    router.get('/admin/ai/transactions', { ...filters.value, ...(filters.value.customer_id ? {} : {}) }, { preserveState: true, replace: true });
};

const sourceLabel: Record<string, string> = {
    purchase: 'Pembelian',
    usage: 'Pemakaian',
    manual_adjust: 'Penyesuaian',
    refund: 'Refund',
    expired: 'Hangus',
};

const formatDate = (d: string) => new Date(d).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' });

const formatNumber = (n: number) => n.toLocaleString('id-ID');

const palette = ['#f59e0b', '#22d3ee', '#a78bfa', '#34d399', '#f87171', '#60a5fa', '#fb923c', '#4ade80'];

const monthlyTokensData = computed(() => ({
    labels: props.analytics.monthly.map(m => m.month),
    datasets: [{
        label: 'Token',
        data: props.analytics.monthly.map(m => m.tokens),
        backgroundColor: 'hsl(var(--primary) / 0.85)',
        borderRadius: 6,
    }],
}));

const monthlyRunsData = computed(() => ({
    labels: props.analytics.monthly.map(m => m.month),
    datasets: [{
        label: 'Runs',
        data: props.analytics.monthly.map(m => m.runs),
        backgroundColor: 'hsl(var(--primary) / 0.55)',
        borderRadius: 6,
    }],
}));

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { mode: 'index' as const, intersect: false } },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
        y: { beginAtZero: true, ticks: { font: { size: 10 } }, grid: { color: 'hsl(var(--border) / 0.6)' } },
    },
};

const runsByModelData = computed(() => ({
    labels: props.analytics.by_model.map(m => m.model_key),
    datasets: [{
        label: 'Runs',
        data: props.analytics.by_model.map(m => m.runs),
        backgroundColor: props.analytics.by_model.map((_, i) => palette[i % palette.length]),
        borderRadius: 6,
    }],
}));

const tokensByModelData = computed(() => ({
    labels: props.analytics.by_model.map(m => m.model_key),
    datasets: [{
        label: 'Token',
        data: props.analytics.by_model.map(m => m.tokens_in + m.tokens_out),
        backgroundColor: props.analytics.by_model.map((_, i) => palette[i % palette.length]),
        borderRadius: 6,
    }],
}));

const spendByModelData = computed(() => ({
    labels: props.analytics.by_model.map(m => m.model_key),
    datasets: [{
        label: 'Kredit',
        data: props.analytics.by_model.map(m => m.credits),
        backgroundColor: props.analytics.by_model.map((_, i) => palette[i % palette.length]),
        borderWidth: 0,
    }],
}));

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '60%',
    plugins: { legend: { position: 'bottom' as const, labels: { boxWidth: 12, boxHeight: 12, font: { size: 10 } } } },
};

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Transaksi Kredit AI" />

        <div class="space-y-6">
            <!-- Statistik pemakaian -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Tokens</CardTitle>
                        <Hash class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold">{{ formatNumber(analytics.total_tokens) }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">{{ formatNumber(analytics.input_tokens) }} in · {{ formatNumber(analytics.output_tokens) }} out</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Runs</CardTitle>
                        <Zap class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold">{{ formatNumber(analytics.total_runs) }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">Request pemakaian AI</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Kredit Terpakai</CardTitle>
                        <Coins class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold">{{ formatNumber(analytics.credits_spent) }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">Total kredit keluar (usage)</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Model Aktif Dipakai</CardTitle>
                        <Cpu class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold">{{ analytics.by_model.length }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">Model dengan riwayat usage</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Grafik bulanan -->
            <div class="grid gap-4 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base"><TrendingUp class="h-4 w-4 text-muted-foreground" /> Monthly Usage — Tokens</CardTitle>
                        <CardDescription>Total token per bulan (6 bulan terakhir)</CardDescription>
                    </CardHeader>
                    <CardContent><div class="h-56"><Bar :data="monthlyTokensData" :options="barOptions" /></div></CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base"><Zap class="h-4 w-4 text-muted-foreground" /> Monthly Usage — Runs</CardTitle>
                        <CardDescription>Total runs per bulan (6 bulan terakhir)</CardDescription>
                    </CardHeader>
                    <CardContent><div class="h-56"><Bar :data="monthlyRunsData" :options="barOptions" /></div></CardContent>
                </Card>
            </div>

            <!-- Grafik per model -->
            <div class="grid gap-4 lg:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base"><Zap class="h-4 w-4 text-muted-foreground" /> Requests by Model</CardTitle>
                        <CardDescription>Jumlah runs per model</CardDescription>
                    </CardHeader>
                    <CardContent><div class="h-56"><Bar :data="runsByModelData" :options="barOptions" /></div></CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base"><Hash class="h-4 w-4 text-muted-foreground" /> Tokens by Model</CardTitle>
                        <CardDescription>All tokens per model</CardDescription>
                    </CardHeader>
                    <CardContent><div class="h-56"><Bar :data="tokensByModelData" :options="barOptions" /></div></CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base"><Coins class="h-4 w-4 text-muted-foreground" /> Spend by Model</CardTitle>
                        <CardDescription>Kredit terpakai per model</CardDescription>
                    </CardHeader>
                    <CardContent><div class="h-56"><Doughnut :data="spendByModelData" :options="doughnutOptions" /></div></CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Riwayat Transaksi Kredit AI</CardTitle>
                    <CardDescription>Ledger semua perubahan saldo kredit AI</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="mb-4 grid grid-cols-2 gap-3 md:grid-cols-5">
                        <div>
                            <Label>Customer ID</Label>
                            <Input v-model="filters.customer_id" type="number" min="1" placeholder="ID" @keyup.enter="applyFilters" />
                        </div>
                        <div>
                            <Label>Tipe</Label>
                            <select v-model="filters.type" class="mt-1 flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm" @change="applyFilters">
                                <option value="">Semua</option>
                                <option value="in">Masuk</option>
                                <option value="out">Keluar</option>
                            </select>
                        </div>
                        <div>
                            <Label>Sumber</Label>
                            <select v-model="filters.source" class="mt-1 flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm" @change="applyFilters">
                                <option value="">Semua</option>
                                <option value="purchase">Pembelian</option>
                                <option value="usage">Pemakaian</option>
                                <option value="manual_adjust">Penyesuaian</option>
                                <option value="refund">Refund</option>
                                <option value="expired">Hangus</option>
                            </select>
                        </div>
                        <div>
                            <Label>Dari</Label>
                            <DatePicker v-model="filters.from" placeholder="Dari tanggal" @update:model-value="applyFilters" />
                        </div>
                        <div>
                            <Label>Sampai</Label>
                            <DatePicker v-model="filters.to" placeholder="Sampai tanggal" @update:model-value="applyFilters" />
                        </div>
                    </div>

                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Customer</TableHead>
                                <TableHead>Tipe</TableHead>
                                <TableHead>Sumber</TableHead>
                                <TableHead>Kredit</TableHead>
                                <TableHead>Detail</TableHead>
                                <TableHead>Waktu</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="t in transactions.data" :key="t.id">
                                <TableCell>
                                    <div class="font-medium">{{ t.customer?.name || '#' + '?' }}</div>
                                    <div class="text-xs text-muted-foreground">{{ t.customer?.email }}</div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="t.type === 'in' ? 'default' : 'destructive'">
                                        {{ t.type === 'in' ? 'Masuk' : 'Keluar' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-muted-foreground">{{ sourceLabel[t.source] }}</TableCell>
                                <TableCell>
                                    <span class="inline-flex items-center gap-1 font-semibold" :class="t.credits > 0 ? 'text-green-600' : 'text-red-500'">
                                        <ArrowUpCircle v-if="t.credits > 0" class="h-4 w-4" />
                                        <ArrowDownCircle v-else class="h-4 w-4" />
                                        {{ t.credits > 0 ? '+' : '' }}{{ t.credits }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-xs text-muted-foreground">
                                    <template v-if="t.package">{{ t.package.name }} · </template>
                                    <template v-if="t.model">{{ t.model.model_key }}<template v-if="t.tokens_input !== null"> ({{ t.tokens_input }} in / {{ t.tokens_output }} out)</template> · </template>
                                    {{ t.description || '-' }}
                                </TableCell>
                                <TableCell class="text-xs text-muted-foreground">{{ formatDate(t.created_at) }}</TableCell>
                            </TableRow>
                            <TableRow v-if="transactions.data.length === 0">
                                <TableCell colspan="6" class="py-10 text-center text-muted-foreground">Belum ada transaksi.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
