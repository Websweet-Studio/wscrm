<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { formatDate, formatPrice } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, ShoppingCart, Trash2 } from 'lucide-vue-next';
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
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="font-serif text-2xl font-medium tracking-tight sm:text-3xl">Pesanan Saya</h1>
                    <p class="text-muted-foreground">Lihat dan kelola pesanan layanan Anda</p>
                </div>
                <Button asChild class="w-full sm:w-auto">
                    <Link href="/hosting">Lihat Produk</Link>
                </Button>
            </div>

            <div v-if="orders.length" class="grid gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4">
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Total Pesanan</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ totalOrders }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Menunggu</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ pendingOrders }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Diproses</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ processingOrders }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Selesai</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ completedOrders }}</CardTitle>
                    </CardHeader>
                </Card>
            </div>

            <Card v-if="orders.length === 0">
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

            <Card v-else>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <ShoppingCart class="h-5 w-5" />
                        Daftar Pesanan
                    </CardTitle>
                    <CardDescription>Semua pesanan yang terkait dengan akun Anda</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Pesanan</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Total</TableHead>
                                <TableHead class="text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="order in orders" :key="order.id">
                                <TableCell>
                                    <div class="space-y-0.5">
                                        <div class="font-medium">Order <span class="font-mono">#{{ order.id }}</span></div>
                                        <div class="text-sm text-muted-foreground">
                                            {{ formatDate(order.created_at) }} • {{ getOrderItemsSummary(order) }}
                                        </div>
                                        <div class="text-[11px] text-muted-foreground capitalize">
                                            {{ order.billing_cycle.replace('_', ' ') }}
                                            <span v-if="order.expires_at"> • Kadaluarsa {{ formatDate(order.expires_at) }}</span>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusVariant(order.status)" :class="getStatusColor(order.status)">
                                        {{ getStatusText(order.status) }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <template v-if="order.discount_amount && order.discount_amount > 0">
                                        <div class="text-xs text-muted-foreground line-through">{{ formatPrice(order.total_amount) }}</div>
                                        <div class="font-medium">{{ formatPrice(getPayableAmount(order)) }}</div>
                                    </template>
                                    <template v-else>
                                        <div class="font-medium">{{ formatPrice(order.total_amount) }}</div>
                                    </template>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button variant="outline" size="sm" asChild>
                                            <Link :href="`/customer/orders/${order.id}`">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            v-if="order.status === 'pending'"
                                            variant="destructive"
                                            size="sm"
                                            :disabled="deletingOrders.has(order.id)"
                                            @click="deleteOrder(order.id)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </CustomerLayout>
</template>
