<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import customer from '@/routes/customer';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Customer {
    id: number;
    name: string;
    email: string;
    phone?: string;
    status: string;
}

interface HostingPlan {
    id: number;
    plan_name: string;
    storage_gb: number;
}

interface Service {
    id: number;
    service_type: string;
    domain_name: string;
    status: string;
    expires_at: string;
    hosting_plan?: HostingPlan;
}

interface OrderItem {
    id: number;
    item_type: string;
    domain_name: string | null;
    price: number;
}

interface Order {
    id: number;
    total_amount: number;
    status: string;
    created_at: string;
    order_items: OrderItem[];
}

interface Invoice {
    id: number;
    invoice_number: string;
    amount: number;
    status: string;
    due_date: string;
}

interface Props {
    customer: Customer;
    services: Service[];
    recentOrders: Order[];
    unpaidInvoices: Invoice[];
}

const props = defineProps<Props>();

const logoutForm = useForm({});

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Customer Dashboard', href: customer.dashboard().url }];

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
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'active':
        case 'completed':
        case 'paid':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'pending':
        case 'processing':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'suspended':
        case 'overdue':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
        case 'terminated':
        case 'cancelled':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const logout = () => {
    logoutForm.post(customer.logout().url);
};
</script>

<template>
    <Head title="Customer Dashboard" />

    <CustomerLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4 sm:space-y-6 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Selamat datang, {{ customer.name }}</h1>
                    <p class="text-sm text-muted-foreground sm:text-base">{{ customer.email }}</p>
                </div>
                <Button variant="outline" @click="logout" class="w-full sm:w-auto"> Logout </Button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Layanan Aktif</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ services.filter((s) => s.status === 'active').length }}</div>
                        <p class="text-xs text-muted-foreground">{{ services.length }} total layanan</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pesanan Terbaru</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ recentOrders.length }}</div>
                        <p class="text-xs text-muted-foreground">5 pesanan terakhir</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Tagihan Belum Bayar</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ unpaidInvoices.length }}</div>
                        <p class="text-xs text-muted-foreground">Perlu perhatian</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Status Akun</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold capitalize">{{ customer.status }}</div>
                        <span :class="`mt-1 inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${getStatusColor(customer.status)}`">
                            {{ customer.status }}
                        </span>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-4 sm:gap-6 md:grid-cols-2">
                <!-- Recent Services -->
                <Card>
                    <CardHeader>
                        <CardTitle>Layanan Terbaru</CardTitle>
                        <CardDescription>Layanan hosting dan domain terbaru Anda</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="services.length === 0" class="py-4 text-center text-muted-foreground">
                            Belum ada layanan. <Link :href="customer.hosting.index().url" class="text-primary hover:underline">Jelajahi paket hosting</Link>
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="service in services"
                                :key="service.id"
                                class="flex flex-col gap-2 rounded-md bg-muted/30 px-3 py-2 sm:flex-row sm:items-center sm:justify-between sm:gap-0"
                            >
                                <div>
                                    <div class="font-medium">{{ service.domain_name }}</div>
                                    <div class="text-sm text-muted-foreground">
                                        {{ service.service_type }}
                                        <span v-if="service.hosting_plan"> - {{ service.hosting_plan.plan_name }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-right sm:block sm:text-right">
                                    <span
                                        :class="`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${getStatusColor(service.status)}`"
                                    >
                                        {{ service.status }}
                                    </span>
                                    <div class="mt-1 text-xs text-muted-foreground">Berakhir {{ formatDate(service.expires_at) }}</div>
                                </div>
                            </div>
                        </div>
                        <div v-if="services.length > 0" class="mt-4">
                            <Button variant="outline" size="sm" asChild class="w-full sm:w-auto">
                                <Link :href="customer.services.index().url">Lihat Semua Layanan</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Orders -->
                <Card>
                    <CardHeader>
                        <CardTitle>Pesanan Terbaru</CardTitle>
                        <CardDescription>Riwayat dan status pesanan Anda</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentOrders.length === 0" class="py-4 text-center text-muted-foreground">
                            Belum ada pesanan. <Link :href="customer.hosting.index().url" class="text-primary hover:underline">Mulai berbelanja</Link>
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="order in recentOrders"
                                :key="order.id"
                                class="flex flex-col gap-2 rounded-md bg-muted/30 px-3 py-2 sm:flex-row sm:items-center sm:justify-between sm:gap-0"
                            >
                                <div>
                                    <div class="font-medium">Pesanan #{{ order.id }}</div>
                                    <div class="text-sm text-muted-foreground">
                                        {{ formatDate(order.created_at) }}
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-right sm:block sm:text-right">
                                    <template v-if="order.discount_amount && order.discount_amount > 0">
                                        <div class="text-xs text-muted-foreground line-through">
                                            {{ formatPrice(order.total_amount) }}
                                        </div>
                                        <div class="font-medium text-green-600 dark:text-green-400">
                                            {{ formatPrice(Number(order.total_amount) - Number(order.discount_amount)) }}
                                        </div>
                                        <div class="text-xs text-green-600 dark:text-green-400 mb-1">
                                            Hemat: {{ formatPrice(order.discount_amount) }}
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div class="font-medium">{{ formatPrice(order.total_amount) }}</div>
                                    </template>
                                    <span
                                        :class="`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${getStatusColor(order.status)}`"
                                    >
                                        {{ order.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-if="recentOrders.length > 0" class="mt-4">
                            <Button variant="outline" size="sm" asChild class="w-full sm:w-auto">
                                <Link :href="customer.orders.index().url">Lihat Semua Pesanan</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Unpaid Invoices Alert -->
            <Card v-if="unpaidInvoices.length > 0" class="border-orange-200 bg-orange-50 dark:border-orange-800 dark:bg-orange-950">
                <CardHeader>
                    <CardTitle class="text-orange-800 dark:text-orange-200">Tagihan Belum Bayar</CardTitle>
                    <CardDescription class="text-orange-700 dark:text-orange-300">
                        Anda memiliki {{ unpaidInvoices.length }} tagihan belum bayar yang perlu perhatian
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2">
                        <div
                            v-for="invoice in unpaidInvoices"
                            :key="invoice.id"
                            class="flex flex-col gap-2 rounded-md bg-white/50 px-3 py-2 dark:bg-black/20 sm:flex-row sm:items-center sm:justify-between sm:gap-0"
                        >
                            <div>
                                <Link :href="customer.invoices.show(invoice.id).url" class="font-medium hover:underline">
                                    {{ invoice.invoice_number }}
                                </Link>
                                <div class="text-sm text-muted-foreground">Jatuh tempo {{ formatDate(invoice.due_date) }}</div>
                            </div>
                            <div class="flex items-center justify-between text-right sm:block sm:text-right">
                                <div class="font-bold text-orange-800 dark:text-orange-200">{{ formatPrice(invoice.amount) }}</div>
                                <Link
                                    :href="customer.invoices.payment(invoice.id).url"
                                    class="text-xs text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-200"
                                >
                                    Bayar Sekarang
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                        <Button :as="Link" :href="customer.invoices.index().url" variant="outline" size="sm" class="w-full sm:w-auto"> Lihat Semua Tagihan </Button>
                        <Button v-if="unpaidInvoices.length > 0" :as="Link" :href="customer.invoices.payment(unpaidInvoices[0].id).url" size="sm" class="w-full sm:w-auto">
                            Bayar Sekarang
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </CustomerLayout>
</template>
