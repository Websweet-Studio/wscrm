<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { formatDate, formatPrice } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronLeft, ShoppingCart, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface OrderItem {
    id: number;
    item_type: string;
    domain_name: string | null;
    quantity: number;
    price: number;
    billing_cycle?: string;
    expires_at?: string;
}

interface Order {
    id: number;
    total_amount: number;
    discount_amount?: number;
    status: string;
    billing_cycle: string;
    domain_name?: string | null;
    expires_at?: string | null;
    created_at: string;
    order_items: OrderItem[];
}

interface Props {
    order: Order;
}

const props = defineProps<Props>();

const deleting = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/customer/dashboard' },
    { title: 'My Orders', href: '/customer/orders' },
    { title: `Order #${props.order.id}`, href: `/customer/orders/${props.order.id}` },
];

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

const payableAmount = computed(() => {
    const total = Number(props.order.total_amount || 0);
    const discount = Number(props.order.discount_amount || 0);
    return discount > 0 ? total - discount : total;
});

const deleteOrder = () => {
    if (props.order.status !== 'pending') {
        return;
    }

    if (!confirm('Apakah Anda yakin ingin menghapus order ini?')) {
        return;
    }

    deleting.value = true;
    router.delete(`/customer/orders/${props.order.id}`, {
        onFinish: () => {
            deleting.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`Order #${order.id}`" />

    <CustomerLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4 sm:space-y-6 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button variant="ghost" size="icon" asChild class="h-11 w-11">
                            <Link href="/customer/orders">
                                <ChevronLeft class="h-5 w-5" />
                            </Link>
                        </Button>
                        <div>
                            <h1 class="font-serif text-2xl font-medium leading-tight tracking-tight sm:text-3xl">
                                Order <span class="font-mono">#{{ order.id }}</span>
                            </h1>
                            <p class="text-sm text-muted-foreground">Dibuat {{ formatDate(order.created_at) }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:items-end">
                    <Badge :variant="getStatusVariant(order.status)" :class="getStatusColor(order.status)">
                        {{ order.status }}
                    </Badge>
                    <div class="text-right">
                        <template v-if="order.discount_amount && order.discount_amount > 0">
                            <div class="text-xs text-muted-foreground line-through">{{ formatPrice(order.total_amount) }}</div>
                            <div class="font-serif text-xl font-medium">{{ formatPrice(payableAmount) }}</div>
                            <div class="text-xs text-muted-foreground">Diskon: {{ formatPrice(order.discount_amount) }}</div>
                        </template>
                        <template v-else>
                            <div class="font-serif text-xl font-medium">{{ formatPrice(order.total_amount) }}</div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <Card class="rounded-lg shadow-[rgba(0,0,0,0.05)_0px_4px_24px] lg:col-span-2">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 font-serif font-medium tracking-tight">
                            <ShoppingCart class="h-5 w-5" />
                            Item Pesanan
                        </CardTitle>
                        <CardDescription>Rincian item yang termasuk dalam order ini</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Item</TableHead>
                                    <TableHead>Billing</TableHead>
                                    <TableHead class="text-right">Harga</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="item in order.order_items" :key="item.id">
                                    <TableCell>
                                        <div class="space-y-0.5">
                                            <div class="font-medium">
                                                {{ item.domain_name || order.domain_name || item.item_type }}
                                            </div>
                                            <div class="text-[11px] text-muted-foreground capitalize">
                                                {{ item.item_type }}
                                                <span v-if="item.quantity > 1"> • × {{ item.quantity }}</span>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm capitalize">
                                            {{ (item.billing_cycle || order.billing_cycle).replace('_', ' ') }}
                                        </div>
                                        <div v-if="item.expires_at || order.expires_at" class="text-[11px] text-muted-foreground">
                                            Kadaluarsa {{ formatDate((item.expires_at || order.expires_at) as string) }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="font-medium">{{ formatPrice(item.price) }}</div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card class="rounded-lg shadow-[rgba(0,0,0,0.05)_0px_4px_24px]">
                    <CardHeader>
                        <CardTitle class="font-serif font-medium tracking-tight">Ringkasan</CardTitle>
                        <CardDescription>Informasi pembayaran order</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Subtotal</span>
                            <span class="text-sm font-medium">{{ formatPrice(order.total_amount) }}</span>
                        </div>
                        <div v-if="order.discount_amount && order.discount_amount > 0" class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Diskon</span>
                            <span class="text-sm font-medium">-{{ formatPrice(order.discount_amount) }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t pt-3">
                            <span class="text-sm font-medium">Total</span>
                            <span class="font-serif text-lg font-medium">{{ formatPrice(payableAmount) }}</span>
                        </div>

                        <div class="pt-2">
                            <Button
                                v-if="order.status === 'pending'"
                                variant="destructive"
                                class="w-full"
                                :disabled="deleting"
                                @click="deleteOrder"
                            >
                                <Trash2 class="mr-2 h-4 w-4" />
                                {{ deleting ? 'Menghapus...' : 'Hapus Order' }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </CustomerLayout>
</template>

