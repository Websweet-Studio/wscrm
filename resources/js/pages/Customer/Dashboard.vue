<script setup lang="ts">
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import customer from '@/routes/customer';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowRight, Building2, CreditCard, Layers, LogOut, QrCode, ReceiptText, Sparkles, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';

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
    service_type?: string;
    domain_name?: string;
    status: string;
    expires_at?: string;
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
    discount_amount?: number;
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

interface PaymentAccount {
    id: number;
    type: 'bank' | 'ewallet' | 'qris';
    name: string;
    account_number: string | null;
    account_name: string | null;
    qris_image_path: string | null;
}

interface Props {
    customer: Customer;
    services: Service[];
    recentOrders: Order[];
    unpaidInvoices: Invoice[];
    paymentAccounts: PaymentAccount[];
}

const props = defineProps<Props>();
const page = usePage();

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

const activeServiceCount = computed(() => props.services.filter((s) => s.status === 'active').length);
const serviceCount = computed(() => props.services.length);
const unpaidInvoiceCount = computed(() => props.unpaidInvoices.length);
const hasUnpaidInvoices = computed(() => unpaidInvoiceCount.value > 0);

const primaryInvoice = computed(() => props.unpaidInvoices[0] || null);

const paymentTypeLabel = (type: PaymentAccount['type']) => {
    if (type === 'bank') return 'Bank';
    if (type === 'ewallet') return 'E-Wallet';
    return 'QRIS';
};

const paymentTypeIcon = (type: PaymentAccount['type']) => {
    if (type === 'bank') return Building2;
    if (type === 'ewallet') return Wallet;
    return QrCode;
};

const displayCustomerName = computed(() => {
    const fromAuth = (page.props as any)?.auth?.customer?.name as string | undefined;
    return fromAuth || props.customer?.name || '';
});

const displayCustomerEmail = computed(() => {
    const fromAuth = (page.props as any)?.auth?.customer?.email as string | undefined;
    return fromAuth || props.customer?.email || '';
});

const timeGreeting = computed(() => {
    const hour = new Date().getHours();
    if (hour >= 5 && hour < 11) return 'Selamat pagi';
    if (hour >= 11 && hour < 15) return 'Selamat siang';
    if (hour >= 15 && hour < 18) return 'Selamat sore';
    return 'Selamat malam';
});

const todayLabel = computed(() =>
    new Date().toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }),
);
</script>

<template>
    <Head title="Customer Dashboard" />

    <CustomerLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4 sm:space-y-6 sm:p-6">
            <Card class="relative overflow-hidden border-border/60 bg-card/70 shadow-sm backdrop-blur">
                <div class="pointer-events-none absolute inset-0 opacity-60 dark:opacity-80">
                    <div class="absolute -inset-24 bg-[radial-gradient(closest-side,rgba(16,185,129,0.18),transparent_65%)]"></div>
                    <div class="absolute -right-24 -top-32 h-96 w-96 bg-[radial-gradient(closest-side,rgba(34,211,238,0.16),transparent_60%)]"></div>
                    <div class="absolute inset-0 bg-[linear-gradient(to_right,transparent_0,rgba(16,185,129,0.05)_50%,transparent_100%)]"></div>
                </div>
                <CardContent class="relative p-4 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="mb-2 inline-flex items-center gap-2 rounded-full border border-border/60 bg-background/60 px-2.5 py-1 text-xs text-muted-foreground">
                                <Sparkles class="h-3.5 w-3.5 text-emerald-500 dark:text-green-400" />
                                <span class="truncate">Area Pelanggan</span>
                            </div>
                            <h1 class="font-serif text-2xl font-medium leading-tight tracking-tight sm:text-3xl">
                                <span class="block text-sm font-semibold uppercase tracking-wider text-emerald-700/80 dark:text-green-300/80">
                                    {{ timeGreeting }}, {{ displayCustomerName }}
                                </span>
                            </h1>
                            <p class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-muted-foreground">
                                <span>{{ todayLabel }}</span>
                                <span class="hidden sm:inline">•</span>
                                <span class="truncate">{{ displayCustomerEmail }}</span>
                            </p>
                        </div>
                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-[260px]">
                            <div class="grid grid-cols-2 gap-2">
                                <Button
                                    :as="Link"
                                    :href="getCustomerUrl(() => customerRoutes?.services?.index?.().url, '/customer/services')"
                                    variant="outline"
                                    size="sm"
                                    class="w-full justify-between"
                                >
                                    <span class="inline-flex items-center gap-2">
                                        <Layers class="h-4 w-4 text-emerald-600 dark:text-green-400" />
                                        Layanan
                                    </span>
                                    <ArrowRight class="h-4 w-4 opacity-70" />
                                </Button>
                                <Button
                                    :as="Link"
                                    :href="getCustomerUrl(() => customerRoutes?.orders?.index?.().url, '/customer/orders')"
                                    variant="outline"
                                    size="sm"
                                    class="w-full justify-between"
                                >
                                    <span class="inline-flex items-center gap-2">
                                        <ReceiptText class="h-4 w-4 text-emerald-600 dark:text-green-400" />
                                        Pesanan
                                    </span>
                                    <ArrowRight class="h-4 w-4 opacity-70" />
                                </Button>
                            </div>
                            <Button
                                v-if="primaryInvoice"
                                :as="Link"
                                :href="getCustomerUrl(() => customerRoutes?.invoices?.payment?.(primaryInvoice.id).url, `/customer/invoices/${primaryInvoice.id}/payment`)"
                                size="sm"
                                class="w-full justify-between"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <CreditCard class="h-4 w-4" />
                                    Bayar Tagihan
                                </span>
                                <ArrowRight class="h-4 w-4 opacity-80" />
                            </Button>
                            <Button variant="outline" size="sm" @click="logout" class="w-full justify-between">
                                <span class="inline-flex items-center gap-2">
                                    <LogOut class="h-4 w-4" />
                                    Logout
                                </span>
                                <span class="text-xs text-muted-foreground">Keluar</span>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs font-medium tracking-wide">Layanan Aktif</CardTitle>
                        <Layers class="h-4 w-4 text-emerald-600 dark:text-green-400" />
                    </CardHeader>
                    <CardContent>
                        <div class="font-serif text-2xl font-medium">{{ activeServiceCount }}</div>
                        <p class="text-xs text-muted-foreground">{{ serviceCount }} total layanan</p>
                    </CardContent>
                </Card>

                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs font-medium tracking-wide">Tagihan Belum Bayar</CardTitle>
                        <CreditCard class="h-4 w-4 text-emerald-600 dark:text-green-400" />
                    </CardHeader>
                    <CardContent>
                        <div class="font-serif text-2xl font-medium">{{ unpaidInvoiceCount }}</div>
                        <p class="text-xs text-muted-foreground">Perlu perhatian</p>
                    </CardContent>
                </Card>

                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs font-medium tracking-wide">Status Akun</CardTitle>
                        <Sparkles class="h-4 w-4 text-emerald-600 dark:text-green-400" />
                    </CardHeader>
                    <CardContent>
                        <div class="font-serif text-2xl font-medium capitalize">{{ customer.status }}</div>
                        <span :class="`mt-1 inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${getStatusColor(customer.status)}`">
                            {{ customer.status }}
                        </span>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-4 sm:gap-6 lg:grid-cols-2">
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader>
                        <CardTitle class="font-serif font-medium tracking-tight">Metode Pembayaran</CardTitle>
                        <CardDescription>Metode yang tersedia untuk pembayaran tagihan</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="paymentAccounts.length === 0" class="py-6 text-center text-sm text-muted-foreground">
                            Belum ada metode pembayaran yang tersedia.
                        </div>
                        <div v-else class="overflow-hidden rounded-lg border border-border/60">
                            <div class="divide-y divide-border/60">
                                <div v-for="account in paymentAccounts" :key="account.id" class="flex items-start justify-between gap-3 bg-background/40 px-3 py-2">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <component :is="paymentTypeIcon(account.type)" class="h-4 w-4 text-emerald-600 dark:text-green-400" />
                                            <div class="truncate font-medium">{{ account.name }}</div>
                                            <span class="rounded-full bg-muted px-2 py-0.5 text-[11px] text-muted-foreground">{{ paymentTypeLabel(account.type) }}</span>
                                        </div>
                                        <div class="mt-1 text-xs text-muted-foreground">
                                            <template v-if="account.type === 'bank'">
                                                <span class="font-mono">{{ account.account_number || '-' }}</span>
                                                <span v-if="account.account_name"> • a.n. {{ account.account_name }}</span>
                                            </template>
                                            <template v-else-if="account.type === 'ewallet'">
                                                <span>Nomor: </span><span class="font-mono">{{ account.account_number || '-' }}</span>
                                            </template>
                                            <template v-else>
                                                <span v-if="account.qris_image_path">Scan QR untuk bayar</span>
                                                <span v-else>QR belum diupload</span>
                                            </template>
                                        </div>
                                    </div>
                                    <a
                                        v-if="account.type === 'qris' && account.qris_image_path"
                                        :href="account.qris_image_path"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="shrink-0"
                                    >
                                        <img
                                            :src="account.qris_image_path"
                                            alt="QRIS"
                                            class="h-20 w-20 rounded-md border border-border/60 bg-background object-contain"
                                        />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    :class="
                        cn(
                            'rounded-lg border-border/60 shadow-sm',
                            hasUnpaidInvoices &&
                                'bg-[linear-gradient(to_right,rgba(16,185,129,0.08),transparent_60%)] dark:bg-[linear-gradient(to_right,rgba(34,197,94,0.14),transparent_60%)]',
                        )
                    "
                >
                    <CardHeader>
                        <CardTitle class="font-serif font-medium tracking-tight" :class="hasUnpaidInvoices ? 'text-primary' : undefined">Tagihan Belum Bayar</CardTitle>
                        <CardDescription v-if="hasUnpaidInvoices">
                            Anda memiliki {{ unpaidInvoices.length }} tagihan belum bayar yang perlu perhatian
                        </CardDescription>
                        <CardDescription v-else>Tidak ada tagihan belum bayar saat ini.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="hasUnpaidInvoices" class="overflow-hidden rounded-lg border border-border/60 bg-background/40">
                            <div class="divide-y divide-border/60">
                                <div v-for="invoice in unpaidInvoices" :key="invoice.id" class="flex items-start justify-between gap-3 px-3 py-2">
                                    <div class="min-w-0">
                                        <Link
                                            :href="getCustomerUrl(() => customerRoutes?.invoices?.show?.(invoice.id).url, `/customer/invoices/${invoice.id}`)"
                                            class="truncate font-medium hover:underline"
                                        >
                                            {{ invoice.invoice_number }}
                                        </Link>
                                        <div class="mt-0.5 text-xs text-muted-foreground">Jatuh tempo {{ formatDate(invoice.due_date) }}</div>
                                    </div>
                                    <div class="flex shrink-0 flex-col items-end gap-1">
                                        <div class="font-serif font-medium text-emerald-700 dark:text-green-400">{{ formatPrice(invoice.amount) }}</div>
                                        <Button
                                            :as="Link"
                                            :href="getCustomerUrl(() => customerRoutes?.invoices?.payment?.(invoice.id).url, `/customer/invoices/${invoice.id}/payment`)"
                                            size="sm"
                                            class="h-8 px-3"
                                        >
                                            Bayar
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="rounded-lg border border-border/60 bg-background/40 p-4 text-sm text-muted-foreground">
                            Semua tagihan sudah dibayar atau belum ada tagihan.
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
        </div>
    </CustomerLayout>
</template>
