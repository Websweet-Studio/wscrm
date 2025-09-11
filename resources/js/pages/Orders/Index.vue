<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface OrderItem {
    id: number;
    item_type: 'hosting' | 'domain';
    domain_name: string | null;
    quantity: number;
    price: number;
}

interface Order {
    id: number;
    order_type: string;
    total_amount: number;
    discount_amount?: number;
    status: 'pending' | 'processing' | 'completed' | 'cancelled';
    billing_cycle: string;
    created_at: string;
    order_items: OrderItem[];
}

interface Props {
    orders: Order[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/customer/dashboard' },
    { title: 'My Orders', href: '/customer/orders' }
];

const deletingOrders = ref<Set<number>>(new Set());

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
            }
        });
    }
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
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

const getStatusText = (status: string) => {
    switch (status) {
        case 'completed':
            return 'Completed';
        case 'processing':
            return 'Processing';
        case 'pending':
            return 'Pending';
        case 'cancelled':
            return 'Cancelled';
        default:
            return status;
    }
};
</script>

<template>
    <Head title="My Orders" />

    <CustomerLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">My Orders</h1>
                    <p class="text-muted-foreground">Track and manage your orders</p>
                </div>
                <Button asChild>
                    <Link href="/customer/hosting">Browse Products</Link>
                </Button>
            </div>

            <div v-if="orders.length === 0" class="py-12 text-center">
                <div class="text-muted-foreground">
                    <p class="mb-4 text-lg">You haven't placed any orders yet.</p>
                    <Button asChild>
                        <Link href="/customer/hosting">Start Shopping</Link>
                    </Button>
                </div>
            </div>

            <div v-else class="space-y-4">
                <Card v-for="order in orders" :key="order.id">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Order #{{ order.id }}</CardTitle>
                                <CardDescription>{{ formatDate(order.created_at) }}</CardDescription>
                            </div>
                            <div class="flex items-center gap-3">
                                <span
                                    :class="`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${getStatusColor(order.status)}`"
                                >
                                    {{ getStatusText(order.status) }}
                                </span>
                                <div class="text-right">
                                    <template v-if="order.discount_amount && order.discount_amount > 0">
                                        <div class="text-sm text-muted-foreground line-through">
                                            {{ formatPrice(order.total_amount) }}
                                        </div>
                                        <div class="text-lg font-bold text-green-600 dark:text-green-400">
                                            {{ formatPrice(Number(order.total_amount) - Number(order.discount_amount)) }}
                                        </div>
                                        <div class="text-xs text-green-600 dark:text-green-400">
                                            Hemat: {{ formatPrice(order.discount_amount) }}
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div class="text-lg font-bold">{{ formatPrice(order.total_amount) }}</div>
                                    </template>
                                    <div class="text-sm text-muted-foreground capitalize">{{ order.billing_cycle.replace('_', ' ') }}</div>
                                </div>
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent>
                        <div class="space-y-3">
                            <div class="text-sm font-medium">Order Items:</div>
                            <div class="space-y-2">
                                <div
                                    v-for="item in order.order_items"
                                    :key="item.id"
                                    class="flex items-center justify-between rounded-md bg-muted/30 px-3 py-2"
                                >
                                    <div class="flex items-center gap-3">
                                        <span
                                            :class="`inline-flex items-center rounded px-2 py-1 text-xs font-medium ${
                                                item.item_type === 'hosting'
                                                    ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
                                                    : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'
                                            }`"
                                        >
                                            {{ item.item_type }}
                                        </span>
                                        <span>{{ item.domain_name || `${item.item_type} service` }}</span>
                                        <span v-if="item.quantity > 1" class="text-muted-foreground">× {{ item.quantity }}</span>
                                    </div>
                                    <span class="font-medium">{{ formatPrice(item.price) }}</span>
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 pt-2">
                                <Button variant="outline" size="sm" asChild>
                                    <Link :href="`/customer/orders/${order.id}`">View Details</Link>
                                </Button>
                                <Button 
                                    v-if="order.status === 'pending'" 
                                    variant="destructive" 
                                    size="sm"
                                    :disabled="deletingOrders.has(order.id)"
                                    @click="deleteOrder(order.id)"
                                    class="cursor-pointer"
                                >
                                    <Trash2 class="h-4 w-4 mr-1" />
                                    {{ deletingOrders.has(order.id) ? 'Menghapus...' : 'Hapus' }}
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </CustomerLayout>
</template>
