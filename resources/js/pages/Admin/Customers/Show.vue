<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, Edit, FileText, Mail, MapPin, Phone, Settings, ShoppingCart } from 'lucide-vue-next';

interface Customer {
    id: number;
    name: string;
    email: string;
    phone?: string;
    address?: string;
    city?: string;
    country?: string;
    postal_code?: string;
    status: 'active' | 'inactive' | 'suspended';
    created_at: string;
    updated_at: string;
    orders?: Order[];
    services?: Service[];
    invoices?: Invoice[];
}

interface OrderItem {
    item_type: string;
    domain_name?: string;
    quantity: number;
    unit_price: number;
}

interface Order {
    id: number;
    total_amount: number;
    discount_amount?: number;
    status: string;
    created_at: string;
    order_items: OrderItem[];
}

interface Service {
    id: number;
    domain_name: string;
    service_type: string;
    status: string;
    expires_at: string;
    created_at: string;
    hosting_plan?: {
        plan_name: string;
        storage_gb: number;
        bandwidth_gb: number;
    };
}

interface Invoice {
    id: number;
    invoice_number: string;
    total_amount: number;
    status: string;
    due_date: string;
    created_at: string;
}

interface Props {
    customer: Customer;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Customers', href: '/admin/customers' },
    { title: props.customer.name, href: `/admin/customers/${props.customer.id}` },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
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
        case 'active':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'suspended':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        case 'inactive':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        case 'completed':
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
</script>

<template>
    <Head :title="`Customer - ${customer.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ customer.name }}</h1>
                    <p class="text-muted-foreground">Customer details and activity</p>
                </div>
                <Button variant="outline" asChild>
                    <Link :href="`/admin/customers`" class="cursor-pointer"> Back to Customers </Link>
                </Button>
            </div>

            <!-- Customer Information -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Edit class="h-5 w-5" />
                            Customer Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Status</span>
                            <Badge :class="getStatusClass(customer.status)">
                                {{ customer.status }}
                            </Badge>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <Mail class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm">{{ customer.email }}</span>
                            </div>

                            <div v-if="customer.phone" class="flex items-center gap-3">
                                <Phone class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm">{{ customer.phone }}</span>
                            </div>

                            <div v-if="customer.address || customer.city || customer.country" class="flex items-start gap-3">
                                <MapPin class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                <div class="text-sm">
                                    <div v-if="customer.address">{{ customer.address }}</div>
                                    <div>
                                        {{ [customer.city, customer.postal_code, customer.country].filter(Boolean).join(', ') }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <Calendar class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm">Joined {{ formatDate(customer.created_at) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quick Stats -->
                <Card>
                    <CardHeader>
                        <CardTitle>Activity Summary</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-bold text-blue-600">{{ customer.orders?.length || 0 }}</div>
                                <div class="text-xs text-muted-foreground">Orders</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-green-600">{{ customer.services?.length || 0 }}</div>
                                <div class="text-xs text-muted-foreground">Services</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-orange-600">{{ customer.invoices?.length || 0 }}</div>
                                <div class="text-xs text-muted-foreground">Invoices</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Orders -->
            <Card v-if="customer.orders && customer.orders.length > 0">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <ShoppingCart class="h-5 w-5" />
                        Recent Orders
                    </CardTitle>
                    <CardDescription>Order history for this customer</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Order ID</TableHead>
                                <TableHead>Items</TableHead>
                                <TableHead>Total</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Date</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="order in customer.orders" :key="order.id">
                                <TableCell class="font-medium">#{{ order.id }}</TableCell>
                                <TableCell>
                                    <div class="space-y-1">
                                        <div v-for="item in order.order_items" :key="item.item_type" class="text-xs">
                                            {{ item.quantity }}x {{ item.item_type }}
                                            <span v-if="item.domain_name" class="text-muted-foreground"> ({{ item.domain_name }}) </span>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <template v-if="order.discount_amount && order.discount_amount > 0">
                                        <div class="text-xs text-muted-foreground line-through">
                                            {{ formatPrice(order.total_amount) }}
                                        </div>
                                        <div class="font-medium text-green-600 dark:text-green-400">
                                            {{ formatPrice(Number(order.total_amount) - Number(order.discount_amount)) }}
                                        </div>
                                        <div class="text-xs text-green-600 dark:text-green-400">
                                            Hemat: {{ formatPrice(order.discount_amount) }}
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div class="font-medium">{{ formatPrice(order.total_amount) }}</div>
                                    </template>
                                </TableCell>
                                <TableCell>
                                    <Badge :class="getStatusClass(order.status)">
                                        {{ order.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ formatDate(order.created_at) }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Services -->
            <Card v-if="customer.services && customer.services.length > 0">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Settings class="h-5 w-5" />
                        Active Services
                    </CardTitle>
                    <CardDescription>Services currently active for this customer</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Domain</TableHead>
                                <TableHead>Service Type</TableHead>
                                <TableHead>Plan</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Expires</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="service in customer.services" :key="service.id">
                                <TableCell class="font-medium">{{ service.domain_name }}</TableCell>
                                <TableCell>{{ service.service_type }}</TableCell>
                                <TableCell>
                                    <div v-if="service.hosting_plan">
                                        {{ service.hosting_plan.plan_name }}
                                        <div class="text-xs text-muted-foreground">{{ service.hosting_plan.storage_gb }}GB Storage</div>
                                    </div>
                                    <span v-else class="text-muted-foreground">-</span>
                                </TableCell>
                                <TableCell>
                                    <Badge :class="getStatusClass(service.status)">
                                        {{ service.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ formatDate(service.expires_at) }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Invoices -->
            <Card v-if="customer.invoices && customer.invoices.length > 0">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <FileText class="h-5 w-5" />
                        Recent Invoices
                    </CardTitle>
                    <CardDescription>Invoice history for this customer</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Invoice #</TableHead>
                                <TableHead>Amount</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Due Date</TableHead>
                                <TableHead>Created</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="invoice in customer.invoices" :key="invoice.id">
                                <TableCell class="font-medium">{{ invoice.invoice_number }}</TableCell>
                                <TableCell>{{ formatPrice(invoice.total_amount) }}</TableCell>
                                <TableCell>
                                    <Badge :class="getStatusClass(invoice.status)">
                                        {{ invoice.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ formatDate(invoice.due_date) }}</TableCell>
                                <TableCell>{{ formatDate(invoice.created_at) }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Empty States -->
            <div
                v-if="
                    (!customer.orders || customer.orders.length === 0) &&
                    (!customer.services || customer.services.length === 0) &&
                    (!customer.invoices || customer.invoices.length === 0)
                "
                class="py-12 text-center"
            >
                <div class="text-muted-foreground">
                    <h3 class="text-lg font-medium">No Activity Yet</h3>
                    <p class="mt-1 text-sm">This customer hasn't placed any orders or used any services yet.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
