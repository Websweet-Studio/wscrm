<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ArrowDownCircle, ArrowUpCircle, Coins, Search } from 'lucide-vue-next';
import { ref } from 'vue';

interface Transaction {
    id: number;
    type: 'in' | 'out';
    source: 'purchase' | 'usage' | 'manual_adjust';
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

interface Props {
    transactions: { data: Transaction[]; current_page: number; last_page: number; per_page: number; total: number; links: any[] };
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
};

const formatDate = (d: string) => new Date(d).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' });
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Transaksi Kredit AI" />

        <div class="space-y-6">
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

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="px-3 py-3">Customer</th>
                                    <th class="px-3 py-3">Tipe</th>
                                    <th class="px-3 py-3">Sumber</th>
                                    <th class="px-3 py-3">Kredit</th>
                                    <th class="px-3 py-3">Detail</th>
                                    <th class="px-3 py-3">Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="t in transactions.data" :key="t.id" class="border-b last:border-0">
                                    <td class="px-3 py-3">
                                        <div class="font-medium">{{ t.customer?.name || '#' + '?' }}</div>
                                        <div class="text-xs text-muted-foreground">{{ t.customer?.email }}</div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <Badge :variant="t.type === 'in' ? 'default' : 'destructive'">
                                            {{ t.type === 'in' ? 'Masuk' : 'Keluar' }}
                                        </Badge>
                                    </td>
                                    <td class="px-3 py-3 text-muted-foreground">{{ sourceLabel[t.source] }}</td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex items-center gap-1 font-semibold" :class="t.credits > 0 ? 'text-green-600' : 'text-red-500'">
                                            <ArrowUpCircle v-if="t.credits > 0" class="h-4 w-4" />
                                            <ArrowDownCircle v-else class="h-4 w-4" />
                                            {{ t.credits > 0 ? '+' : '' }}{{ t.credits }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-xs text-muted-foreground">
                                        <template v-if="t.package">{{ t.package.name }} · </template>
                                        <template v-if="t.model">{{ t.model.model_key }}<template v-if="t.tokens_input !== null"> ({{ t.tokens_input }} in / {{ t.tokens_output }} out)</template> · </template>
                                        {{ t.description || '-' }}
                                    </td>
                                    <td class="px-3 py-3 text-xs text-muted-foreground">{{ formatDate(t.created_at) }}</td>
                                </tr>
                                <tr v-if="transactions.data.length === 0">
                                    <td colspan="6" class="px-3 py-10 text-center text-muted-foreground">Belum ada transaksi.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
