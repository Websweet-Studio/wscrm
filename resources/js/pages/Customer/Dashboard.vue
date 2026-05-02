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

const customerRoutes = customer as any;

const getCustomerUrl = (getter: () => string | undefined, fallback: string) => {
    try {
        return getter() || fallback;
    } catch {
        return fallback;
    }
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Customer Dashboard', href: getCustomerUrl(() => customerRoutes?.dashboard?.().url, '/customer/dashboard') }];

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
                    <h1 class="font-serif text-2xl font-medium leading-tight tracking-tight sm:text-3xl">
                        Selamat datang, {{ customer.name }}
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-base">{{ customer.email }}</p>
                </div>
                <Button variant="outline" @click="logout" class="w-full sm:w-auto"> Logout </Button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
                <Card class="rounded-lg shadow-[rgba(0,0,0,0.05)_0px_4px_24px]">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs font-medium tracking-wide">Layanan Aktif</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="font-serif text-2xl font-medium">{{ services.filter((s) => s.status === 'active').length }}</div>
                        <p class="text-xs text-muted-foreground">{{ services.length }} total layanan</p>
                    </CardContent>
                </Card>

                <Card class="rounded-lg shadow-[rgba(0,0,0,0.05)_0px_4px_24px]">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs font-medium tracking-wide">Pesanan Terbaru</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="font-serif text-2xl font-medium">{{ recentOrders.length }}</div>
                        <p class="text-xs text-muted-foreground">5 pesanan terakhir</p>
                    </CardContent>
                </Card>

                <Card class="rounded-lg shadow-[rgba(0,0,0,0.05)_0px_4px_24px]">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs font-medium tracking-wide">Tagihan Belum Bayar</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="font-serif text-2xl font-medium">{{ unpaidInvoices.length }}</div>
                        <p class="text-xs text-muted-foreground">Perlu perhatian</p>
                    </CardContent>
                </Card>

                <Card class="rounded-lg shadow-[rgba(0,0,0,0.05)_0px_4px_24px]">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs font-medium tracking-wide">Status Akun</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="font-serif text-2xl font-medium capitalize">{{ customer.status }}</div>
                        <span :class="`mt-1 inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${getStatusColor(customer.status)}`">
                            {{ customer.status }}
                        </span>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-4 sm:gap-6 md:grid-cols-2">
                <!-- Recent Services -->
                <Card class="rounded-lg shadow-[rgba(0,0,0,0.05)_0px_4px_24px]">
                    <CardHeader>
                        <CardTitle class="font-serif font-medium tracking-tight">Layanan Terbaru</CardTitle>
                        <CardDescription>Layanan hosting dan domain terbaru Anda</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="services.length === 0" class="py-4 text-center text-muted-foreground">
                            Belum ada layanan.
                            <Link href="/hosting" class="text-primary hover:underline">Jelajahi paket hosting</Link>
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
                                <Link :href="getCustomerUrl(() => customerRoutes?.services?.index?.().url, '/customer/services')">Lihat Semua Layanan</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Orders -->
                <Card class="rounded-lg shadow-[rgba(0,0,0,0.05)_0px_4px_24px]">
                    <CardHeader>
                        <CardTitle class="font-serif font-medium tracking-tight">Pesanan Terbaru</CardTitle>
                        <CardDescription>Riwayat dan status pesanan Anda</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentOrders.length === 0" class="py-4 text-center text-muted-foreground">
                            Belum ada pesanan. <Link href="/hosting" class="text-primary hover:underline">Mulai berbelanja</Link>
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
                                        <div class="mb-1 text-xs text-green-600 dark:text-green-400">
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
                                <Link :href="getCustomerUrl(() => customerRoutes?.orders?.index?.().url, '/customer/orders')">Lihat Semua Pesanan</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Unpaid Invoices Alert -->
            <Card v-if="unpaidInvoices.length > 0" class="rounded-lg bg-secondary/40 shadow-[rgba(0,0,0,0.05)_0px_4px_24px]">
                <CardHeader>
                    <CardTitle class="font-serif font-medium tracking-tight text-primary">Tagihan Belum Bayar</CardTitle>
                    <CardDescription>
                        Anda memiliki {{ unpaidInvoices.length }} tagihan belum bayar yang perlu perhatian
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2">
                        <div
                            v-for="invoice in unpaidInvoices"
                            :key="invoice.id"
                            class="flex flex-col gap-2 rounded-md bg-background/60 px-3 py-2 sm:flex-row sm:items-center sm:justify-between sm:gap-0"
                        >
                            <div>
                                <Link
                                    :href="getCustomerUrl(() => customerRoutes?.invoices?.show?.(invoice.id).url, `/customer/invoices/${invoice.id}`)"
                                    class="font-medium hover:underline"
                                >
                                    {{ invoice.invoice_number }}
                                </Link>
                                <div class="text-sm text-muted-foreground">Jatuh tempo {{ formatDate(invoice.due_date) }}</div>
                            </div>
                            <div class="flex items-center justify-between text-right sm:block sm:text-right">
                                <div class="font-serif font-medium text-primary">{{ formatPrice(invoice.amount) }}</div>
                                <Link
                                    :href="getCustomerUrl(() => customerRoutes?.invoices?.payment?.(invoice.id).url, `/customer/invoices/${invoice.id}/payment`)"
                                    class="text-xs text-primary hover:underline"
                                >
                                    Bayar Sekarang
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                        <Button
                            :as="Link"
                            :href="getCustomerUrl(() => customerRoutes?.invoices?.index?.().url, '/customer/invoices')"
                            variant="outline"
                            size="sm"
                            class="w-full sm:w-auto"
                        >
                            Lihat Semua Tagihan
                        </Button>
                        <Button
                            v-if="unpaidInvoices.length > 0"
                            :as="Link"
                            :href="
                                getCustomerUrl(
                                    () => customerRoutes?.invoices?.payment?.(unpaidInvoices[0].id).url,
                                    `/customer/invoices/${unpaidInvoices[0].id}/payment`,
                                )
                            "
                            size="sm"
                            class="w-full sm:w-auto"
                        >
                            Bayar Sekarang
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </CustomerLayout>
</template>
