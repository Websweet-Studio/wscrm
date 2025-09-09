<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { FileText, Mail, MapPin, Package, Phone, ShoppingCart } from 'lucide-vue-next';

interface Customer {
    id: number;
    name: string;
    email: string;
    phone?: string;
    address?: string;
    city?: string;
    country?: string;
}

interface OrderItem {
    id: number;
    item_type: string;
    domain_name?: string;
    quantity: number;
    price: number;
}

interface Invoice {
    id: number;
    invoice_number: string;
    total_amount: number;
    status: string;
    due_date: string;
    created_at: string;
}

interface Order {
    id: number;
    total_amount: number;
    status: 'pending' | 'processing' | 'completed' | 'cancelled';
    billing_cycle: string;
    order_type: string;
    created_at: string;
    updated_at: string;
    customer: Customer;
    order_items: OrderItem[];
    invoice?: Invoice;
}

interface Props {
    order: Order;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Orders', href: '/admin/orders' },
    { title: `Order #${props.order.id}`, href: `/admin/orders/${props.order.id}` },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const getStatusClass = (status: string) => {
    switch (status) {
        case 'completed':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'processing':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'cancelled':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        case 'paid':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'unpaid':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        case 'overdue':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const totalItemsAmount = props.order.order_items.reduce((sum, item) => sum + item.price * item.quantity, 0);
</script>

<template>
    <Head :title="`Order #${order.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Order #{{ order.id }}</h1>
                    <p class="text-muted-foreground">Order details and customer information</p>
                </div>
                <Button variant="outline" asChild>
                    <Link href="/admin/orders" class="cursor-pointer"> Back to Orders </Link>
                </Button>
            </div>

            <!-- Order Overview -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Order Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <ShoppingCart class="h-5 w-5" />
                            Order Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Status</span>
                            <Badge :class="getStatusClass(order.status)">
                                {{ order.status }}
                            </Badge>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Order Type</span>
                                <span class="text-sm font-medium capitalize">{{ order.order_type }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Billing Cycle</span>
                                <span class="text-sm font-medium capitalize">{{ order.billing_cycle.replace('_', ' ') }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Total Amount</span>
                                <span class="text-lg font-bold">{{ formatPrice(order.total_amount) }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Created</span>
                                <span class="text-sm">{{ formatDate(order.created_at) }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Last Updated</span>
                                <span class="text-sm">{{ formatDate(order.updated_at) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Customer Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Mail class="h-5 w-5" />
                            Customer Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium">{{ order.customer.name }}</span>
                                <div class="text-sm text-muted-foreground">Customer ID: #{{ order.customer.id }}</div>
                            </div>

                            <div class="flex items-center gap-3">
                                <Mail class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm">{{ order.customer.email }}</span>
                            </div>

                            <div v-if="order.customer.phone" class="flex items-center gap-3">
                                <Phone class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm">{{ order.customer.phone }}</span>
                            </div>

                            <div v-if="order.customer.address || order.customer.city || order.customer.country" class="flex items-start gap-3">
                                <MapPin class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                <div class="text-sm">
                                    <div v-if="order.customer.address">{{ order.customer.address }}</div>
                                    <div>
                                        {{ [order.customer.city, order.customer.country].filter(Boolean).join(', ') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t pt-3">
                            <Button size="sm" variant="outline" asChild>
                                <Link :href="`/admin/customers/${order.customer.id}`"> View Customer Details </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Order Items -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Package class="h-5 w-5" />
                        Order Items
                    </CardTitle>
                    <CardDescription>Items included in this order</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Item Type</TableHead>
                                <TableHead>Details</TableHead>
                                <TableHead>Quantity</TableHead>
                                <TableHead>Unit Price</TableHead>
                                <TableHead>Total</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="item in order.order_items" :key="item.id">
                                <TableCell class="font-medium capitalize">{{ item.item_type }}</TableCell>
                                <TableCell>
                                    <div v-if="item.domain_name" class="font-medium">{{ item.domain_name }}</div>
                                    <div class="text-sm text-muted-foreground">Item ID: #{{ item.item_id }}</div>
                                </TableCell>
                                <TableCell>{{ item.quantity }}</TableCell>
                                <TableCell>{{ formatPrice(item.price) }}</TableCell>
                                <TableCell class="font-medium">{{ formatPrice(item.price * item.quantity) }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div class="flex items-center justify-between border-t pt-4">
                        <span class="text-sm text-muted-foreground">Total ({{ order.order_items.length }} items)</span>
                        <span class="text-xl font-bold">{{ formatPrice(totalItemsAmount) }}</span>
                    </div>
                </CardContent>
            </Card>

            <!-- Invoice Information -->
            <Card v-if="order.invoice">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <FileText class="h-5 w-5" />
                        Invoice Information
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div>
                            <div class="text-sm text-muted-foreground">Invoice Number</div>
                            <div class="font-medium">{{ order.invoice.invoice_number }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-muted-foreground">Amount</div>
                            <div class="font-medium">{{ formatPrice(order.invoice.total_amount) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-muted-foreground">Status</div>
                            <Badge :class="getStatusClass(order.invoice.status)">
                                {{ order.invoice.status }}
                            </Badge>
                        </div>
                        <div>
                            <div class="text-sm text-muted-foreground">Due Date</div>
                            <div class="font-medium">{{ formatDate(order.invoice.due_date) }}</div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- No Invoice State -->
            <Card v-else>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <FileText class="h-5 w-5" />
                        Invoice Information
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="py-8 text-center text-muted-foreground">
                        <FileText class="mx-auto h-12 w-12 text-muted-foreground/40" />
                        <h3 class="mt-2 text-sm font-semibold">No Invoice Generated</h3>
                        <p class="mt-1 text-sm">Invoice will be created when order is processed.</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
