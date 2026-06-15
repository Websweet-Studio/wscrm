<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { cn, formatDate, formatPrice } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowRight, ChevronLeft, ReceiptText, ShoppingCart, Trash2 } from 'lucide-vue-next';
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

const subtotal = computed(() => {
    return props.order.order_items.reduce((sum, item) => sum + Number(item.price || 0), 0);
});

const payableAmount = computed(() => {
    const discount = Number(props.order.discount_amount || 0);
    return discount > 0 ? subtotal.value - discount : subtotal.value;
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
                                <span>Detail Pesanan</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <Button variant="ghost" size="icon" asChild class="mt-0.5 h-10 w-10">
                                    <Link href="/customer/orders">
                                        <ChevronLeft class="h-5 w-5" />
                                    </Link>
                                </Button>
                                <div class="min-w-0">
                                    <h1 class="font-serif text-2xl font-medium leading-tight tracking-tight sm:text-3xl">
                                        Order <span class="font-mono">#{{ order.id }}</span>
                                    </h1>
                                    <p class="mt-1 text-sm text-muted-foreground">Dibuat {{ formatDate(order.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-[260px]">
                            <div class="flex items-center justify-between">
                                <Badge :variant="getStatusVariant(order.status)" :class="getStatusColor(order.status)">
                                    {{ order.status }}
                                </Badge>
                                <span class="text-xs text-muted-foreground">{{ order.billing_cycle.replace('_', ' ') }}</span>
                            </div>
                            <div class="rounded-lg border border-border/60 bg-background/60 p-3 text-right">
                                <template v-if="order.discount_amount && order.discount_amount > 0">
                                    <div class="text-xs text-muted-foreground line-through">{{ formatPrice(subtotal) }}</div>
                                    <div class="font-serif text-xl font-medium text-emerald-700 dark:text-green-400">{{ formatPrice(payableAmount) }}</div>
                                    <div class="text-xs text-muted-foreground">Diskon: {{ formatPrice(order.discount_amount) }}</div>
                                </template>
                                <template v-else>
                                    <div class="font-serif text-xl font-medium text-emerald-700 dark:text-green-400">{{ formatPrice(subtotal) }}</div>
                                </template>
                            </div>
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

            <div class="grid gap-6 lg:grid-cols-3">
                <Card class="rounded-lg border-border/60 shadow-sm lg:col-span-2">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 font-serif font-medium tracking-tight">
                            <ShoppingCart class="h-5 w-5" />
                            Item Pesanan
                        </CardTitle>
                        <CardDescription>Rincian item yang termasuk dalam order ini</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-hidden rounded-lg border border-border/60">
                            <div class="divide-y divide-border/60">
                                <div v-for="item in order.order_items" :key="item.id" class="flex items-start justify-between gap-3 bg-background/40 px-3 py-3">
                                    <div class="min-w-0">
                                        <div class="truncate font-medium">
                                            {{ item.domain_name || order.domain_name || item.item_type }}
                                        </div>
                                        <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-muted-foreground capitalize">
                                            <span>{{ item.item_type }}</span>
                                            <span v-if="item.quantity > 1">• × {{ item.quantity }}</span>
                                            <span>• {{ (item.billing_cycle || order.billing_cycle).replace('_', ' ') }}</span>
                                            <span v-if="item.expires_at || order.expires_at">• Kadaluarsa {{ formatDate((item.expires_at || order.expires_at) as string) }}</span>
                                        </div>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <div class="font-medium">{{ formatPrice(item.price) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader>
                        <CardTitle class="font-serif font-medium tracking-tight">Ringkasan</CardTitle>
                        <CardDescription>Informasi pembayaran order</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Subtotal</span>
                            <span class="text-sm font-medium">{{ formatPrice(subtotal) }}</span>
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
