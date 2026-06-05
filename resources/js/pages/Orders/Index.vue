<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { cn, formatDate, formatPrice } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowRight, Eye, ReceiptText, ShoppingCart, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface OrderItem {
    id: number;
    item_type: 'hosting' | 'domain' | 'app' | 'web' | 'maintenance' | 'service';
    domain_name: string | null;
    quantity: number;
    price: number;
    billing_cycle?: string;
    expires_at?: string;
    status?: string;
}

interface Order {
    id: number;
    order_type: string;
    total_amount: number;
    discount_amount?: number;
    status: 'pending' | 'processing' | 'completed' | 'cancelled';
    billing_cycle: string;
    domain_name?: string | null;
    expires_at?: string | null;
    created_at: string;
    order_items: OrderItem[];
}

interface Props {
    orders: Order[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/customer/dashboard' },
    { title: 'My Orders', href: '/customer/orders' },
];

const deletingOrders = ref<Set<number>>(new Set());

const totalOrders = computed(() => props.orders.length);
const pendingOrders = computed(() => props.orders.filter((order) => order.status === 'pending').length);
const processingOrders = computed(() => props.orders.filter((order) => order.status === 'processing').length);
const completedOrders = computed(() => props.orders.filter((order) => order.status === 'completed').length);

const sortOption = ref<'expires_asc' | 'expires_desc' | 'created_desc' | 'created_asc' | 'az' | 'za'>('expires_asc');

const toTime = (value?: string | null) => {
    if (!value) return null;
    const date = new Date(value);
    const time = date.getTime();
    return Number.isNaN(time) ? null : time;
};

const sortedOrders = computed(() => {
    const list = [...props.orders];

    const compareText = (a: string, b: string) =>
        a.localeCompare(b, 'id-ID', {
            numeric: true,
            sensitivity: 'base',
        });

    list.sort((a, b) => {
        if (sortOption.value === 'created_desc' || sortOption.value === 'created_asc') {
            const aTime = toTime(a.created_at) ?? 0;
            const bTime = toTime(b.created_at) ?? 0;
            return sortOption.value === 'created_desc' ? bTime - aTime : aTime - bTime;
        }

        if (sortOption.value === 'expires_asc' || sortOption.value === 'expires_desc') {
            const aTime = toTime(a.expires_at) ?? (sortOption.value === 'expires_asc' ? Number.POSITIVE_INFINITY : Number.NEGATIVE_INFINITY);
            const bTime = toTime(b.expires_at) ?? (sortOption.value === 'expires_asc' ? Number.POSITIVE_INFINITY : Number.NEGATIVE_INFINITY);
            return sortOption.value === 'expires_asc' ? aTime - bTime : bTime - aTime;
        }

        const aLabel = getOrderItemsSummary(a);
        const bLabel = getOrderItemsSummary(b);
        return sortOption.value === 'az' ? compareText(aLabel, bLabel) : compareText(bLabel, aLabel);
    });

    return list;
});

const deleteOrder = (orderId: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus order ini?')) {
        deletingOrders.value.add(orderId);

        router.delete(`/customer/orders/${orderId}`, {
            onSuccess: () => {
                deletingOrders.value.delete(orderId);
            },
            onError: () => {
                deletingOrders.value.delete(orderId);
                alert('Gagal menghapus order. Silakan coba lagi.');
            },
        });
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'completed':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'processing':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'cancelled':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const getPayableAmount = (order: Order) => {
    const discount = Number(order.discount_amount || 0);
    const total = Number(order.total_amount || 0);
    return discount > 0 ? total - discount : total;
};

const getOrderItemsSummary = (order: Order) => {
    const items = order.order_items || [];
    if (items.length === 0) {
        return 'Tanpa item';
    }

    const first = items[0];
    const firstLabel = first.domain_name || order.domain_name || first.item_type;
    const extra = items.length > 1 ? ` +${items.length - 1} item` : '';
    return `${firstLabel}${extra}`;
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'completed':
            return 'default';
        case 'processing':
        case 'pending':
            return 'secondary';
        case 'cancelled':
            return 'destructive';
        default:
            return 'secondary';
    }
};

const getStatusText = (status: string) => {
    switch (status) {
        case 'completed':
            return 'Selesai';
        case 'processing':
            return 'Diproses';
        case 'pending':
            return 'Menunggu';
        case 'cancelled':
            return 'Dibatalkan';
        default:
            return status;
    }
};
</script>

<template>
    <Head title="Pesanan Saya" />

    <CustomerLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4 sm:space-y-6 sm:p-6">
            <Card class="relative overflow-hidden border-border/60 bg-card/70 shadow-sm backdrop-blur">
                <div class="pointer-events-none absolute inset-0 opacity-60 dark:opacity-80">
                    <div class="absolute -inset-24 bg-[radial-gradient(closest-side,rgba(16,185,129,0.16),transparent_65%)]"></div>
                    <div class="absolute -right-24 -top-32 h-96 w-96 bg-[radial-gradient(closest-side,rgba(34,211,238,0.14),transparent_60%)]"></div>
                    <div class="absolute inset-0 bg-[linear-gradient(to_right,transparent_0,rgba(16,185,129,0.05)_50%,transparent_100%)]"></div>
                </div>
                <CardContent class="relative p-4 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="mb-2 inline-flex items-center gap-2 rounded-full border border-border/60 bg-background/60 px-2.5 py-1 text-xs text-muted-foreground">
                                <ReceiptText class="h-3.5 w-3.5 text-emerald-600 dark:text-green-400" />
                                <span>Riwayat transaksi</span>
                            </div>
                            <h1 class="font-serif text-2xl font-medium tracking-tight sm:text-3xl">Pesanan Saya</h1>
                            <p class="mt-1 text-sm text-muted-foreground sm:text-base">Lihat status dan detail pesanan layanan Anda</p>
                        </div>
                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-[240px]">
                            <Button asChild size="sm" class="w-full justify-between">
                                <Link href="/hosting">
                                    <span class="inline-flex items-center gap-2">
                                        <ShoppingCart class="h-4 w-4" />
                                        Lihat Produk
                                    </span>
                                    <ArrowRight class="h-4 w-4 opacity-80" />
                                </Link>
                            </Button>
                            <Button asChild variant="outline" size="sm" class="w-full justify-between">
                                <Link href="/customer/invoices">
                                    <span class="inline-flex items-center gap-2">
                                        <ReceiptText class="h-4 w-4 text-emerald-600 dark:text-green-400" />
                                        Lihat Tagihan
                                    </span>
                                    <ArrowRight class="h-4 w-4 opacity-70" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div v-if="orders.length" class="grid gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4">
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Total Pesanan</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ totalOrders }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Menunggu</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ pendingOrders }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Diproses</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ processingOrders }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Selesai</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ completedOrders }}</CardTitle>
                    </CardHeader>
                </Card>
            </div>

            <Card v-if="orders.length === 0" class="rounded-lg border-border/60 shadow-sm">
                <CardContent class="flex flex-col items-center gap-4 py-10 text-center">
                    <div class="space-y-1">
                        <div class="font-serif text-xl font-medium">Belum ada pesanan</div>
                        <div class="text-sm text-muted-foreground">Mulai berlangganan layanan hosting atau domain untuk melihat pesanan di sini.</div>
                    </div>
                    <Button asChild class="w-full sm:w-auto">
                        <Link href="/hosting">Mulai Berbelanja</Link>
                    </Button>
                </CardContent>
            </Card>

            <Card v-else class="rounded-lg border-border/60 shadow-sm">
                <CardHeader>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <ShoppingCart class="h-5 w-5" />
                                Daftar Pesanan
                            </CardTitle>
                            <CardDescription>Semua pesanan yang terkait dengan akun Anda</CardDescription>
                        </div>
                        <div class="flex w-full items-center gap-2 sm:w-auto">
                            <select
                                v-model="sortOption"
                                class="flex h-9 w-full cursor-pointer rounded-md border border-border bg-input px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none sm:w-[220px]"
                            >
                                <option value="expires_asc">Kadaluarsa terdekat</option>
                                <option value="expires_desc">Kadaluarsa terjauh</option>
                                <option value="created_desc">Tanggal order terbaru</option>
                                <option value="created_asc">Tanggal order terlama</option>
                                <option value="az">A-Z</option>
                                <option value="za">Z-A</option>
                            </select>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-hidden rounded-lg border border-border/60">
                        <div class="divide-y divide-border/60">
                            <div v-for="order in sortedOrders" :key="order.id" class="flex items-start justify-between gap-3 bg-background/40 px-3 py-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <div class="font-medium">
                                            Order <span class="font-mono">#{{ order.id }}</span>
                                        </div>
                                        <Badge :variant="getStatusVariant(order.status)" :class="getStatusColor(order.status)">
                                            {{ getStatusText(order.status) }}
                                        </Badge>
                                    </div>
                                    <div class="mt-1 text-sm text-muted-foreground">
                                        {{ getOrderItemsSummary(order) }}
                                    </div>
                                    <div class="mt-0.5 text-[11px] text-muted-foreground capitalize">
                                        {{ order.billing_cycle.replace('_', ' ') }}
                                        <span v-if="order.expires_at"> • Kadaluarsa {{ formatDate(order.expires_at) }}</span>
                                    </div>
                                </div>

                                <div class="flex shrink-0 flex-col items-end gap-2">
                                    <div class="text-right">
                                        <template v-if="order.discount_amount && order.discount_amount > 0">
                                            <div class="text-xs text-muted-foreground line-through">{{ formatPrice(order.total_amount) }}</div>
                                            <div class="font-medium">{{ formatPrice(getPayableAmount(order)) }}</div>
                                        </template>
                                        <template v-else>
                                            <div class="font-medium">{{ formatPrice(order.total_amount) }}</div>
                                        </template>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Button variant="outline" size="sm" asChild class="h-8 px-3">
                                            <Link :href="`/customer/orders/${order.id}`" class="inline-flex items-center gap-2">
                                                <Eye class="h-4 w-4" />
                                                <span class="text-xs">Detail</span>
                                            </Link>
                                        </Button>
                                        <Button
                                            v-if="order.status === 'pending'"
                                            variant="destructive"
                                            size="sm"
                                            class="h-8 px-3"
                                            :disabled="deletingOrders.has(order.id)"
                                            @click="deleteOrder(order.id)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </CustomerLayout>
</template>
