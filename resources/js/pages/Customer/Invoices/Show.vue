<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { cn } from '@/lib/utils';
import { formatDate, formatPrice } from '@/lib/utils';
import customer from '@/routes/customer';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Building2, Calendar, CreditCard, FileText, Package, ReceiptText, ShieldCheck, User } from 'lucide-vue-next';

const props = defineProps<{
    invoice: any;
}>();

const customerRoutes = customer as any;
const getCustomerUrl = (getter: () => string | undefined, fallback: string) => {
    try {
        return getter() || fallback;
    } catch {
        return fallback;
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Customer Dashboard', href: getCustomerUrl(() => customerRoutes?.dashboard?.().url, '/customer/dashboard') },
    { title: 'Tagihan', href: getCustomerUrl(() => customerRoutes?.invoices?.index?.().url, '/customer/invoices') },
    { title: props.invoice.invoice_number || 'Invoice', href: getCustomerUrl(() => customerRoutes?.invoices?.show?.(props.invoice.id).url, `/customer/invoices/${props.invoice.id}`) },
];

const getStatusClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
        sent: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        overdue: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        cancelled: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
    };
    return classes[status] || classes.draft;
};

const getStatusText = (status) => {
    const texts = {
        draft: 'Draft',
        sent: 'Terkirim',
        paid: 'Dibayar',
        overdue: 'Terlambat',
        cancelled: 'Dibatalkan',
    };
    return texts[status] || status;
};

const getOrderTypeDisplay = (order) => {
    if (!order || !order.order_items || order.order_items.length === 0) {
        return 'Mixed Order';
    }

    const itemTypes = order.order_items.map((item) => item.item_type);
    const uniqueTypes = [...new Set(itemTypes)];

    if (uniqueTypes.length === 1) {
        const type = uniqueTypes[0];
        const typeMap = {
            hosting: 'Hosting',
            domain: 'Domain',
            service: 'Service',
            app: 'Aplikasi',
            web: 'Website',
            maintenance: 'Maintenance',
        };
        return typeMap[type] || type;
    }

    return `Mixed (${uniqueTypes.length} types)`;
};

const isOverdue = () => {
    if (props.invoice.status === 'paid') return false;
    return new Date(props.invoice.due_date) < new Date();
};

const canPay = () => {
    return props.invoice.status !== 'paid' && props.invoice.status !== 'cancelled';
};
</script>

<template>
    <Head :title="`Invoice ${invoice.invoice_number}`" />

    <CustomerLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-5xl space-y-4 p-4 sm:space-y-6 sm:p-6">
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
                                <span>Detail Invoice</span>
                            </div>
                            <h1 class="font-serif text-2xl font-medium leading-tight tracking-tight sm:text-3xl">
                                Invoice <span class="font-mono">{{ invoice.invoice_number }}</span>
                            </h1>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <Badge :class="getStatusClass(invoice.status)">{{ getStatusText(invoice.status) }}</Badge>
                                <span class="text-sm text-muted-foreground">
                                    Jatuh tempo
                                    <span :class="cn('font-medium', isOverdue() ? 'text-red-600 dark:text-red-400' : '')">{{ formatDate(invoice.due_date) }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-[260px]">
                            <Button
                                v-if="canPay()"
                                :as="Link"
                                :href="`/customer/invoices/${invoice.id}/payment`"
                                size="sm"
                                class="w-full justify-between"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <CreditCard class="h-4 w-4" />
                                    Bayar Sekarang
                                </span>
                                <ArrowRight class="h-4 w-4 opacity-80" />
                            </Button>
                            <Button
                                :as="Link"
                                :href="getCustomerUrl(() => customerRoutes?.invoices?.index?.().url, '/customer/invoices')"
                                variant="outline"
                                size="sm"
                                class="w-full justify-between"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <ShieldCheck class="h-4 w-4 text-emerald-600 dark:text-green-400" />
                                    Kembali ke Tagihan
                                </span>
                                <ArrowRight class="h-4 w-4 opacity-70" />
                            </Button>
                            <div class="rounded-lg border border-border/60 bg-background/60 p-3">
                                <div class="text-xs text-muted-foreground">Total</div>
                                <div class="mt-0.5 font-serif text-xl font-medium text-emerald-700 dark:text-green-400">
                                    {{ formatPrice(invoice.amount) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid gap-4 md:grid-cols-5 md:gap-6">
                <div class="space-y-4 md:col-span-3">
                <!-- Invoice Status Alert -->
                <div v-if="isOverdue()" class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <Calendar class="h-5 w-5 text-red-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Invoice Terlambat</h3>
                            <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                                Invoice ini sudah melewati tanggal jatuh tempo. Silakan lakukan pembayaran segera.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-else-if="invoice.status === 'paid'"
                    class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20"
                >
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <CreditCard class="h-5 w-5 text-green-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Pembayaran Berhasil</h3>
                            <p class="mt-1 text-sm text-green-700 dark:text-green-300">
                                Invoice ini sudah dibayar pada {{ formatDate(invoice.paid_at) }}.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Invoice Details -->
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <FileText class="h-5 w-5" />
                            Detail Invoice
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Invoice Number</label>
                                    <p class="text-lg font-semibold">{{ invoice.invoice_number }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Status</label>
                                    <div class="mt-1">
                                        <Badge :class="getStatusClass(invoice.status)">
                                            {{ getStatusText(invoice.status) }}
                                        </Badge>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Total Amount</label>
                                    <p class="text-2xl font-bold text-primary">{{ formatPrice(invoice.amount) }}</p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Due Date</label>
                                    <p class="text-lg">{{ formatDate(invoice.due_date) }}</p>
                                </div>
                                <div v-if="invoice.paid_at">
                                    <label class="text-sm font-medium text-muted-foreground">Paid Date</label>
                                    <p class="text-lg">{{ formatDate(invoice.paid_at) }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Created Date</label>
                                    <p class="text-lg">{{ formatDate(invoice.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Customer Information -->
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <User class="h-5 w-5" />
                            Informasi Customer
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Nama</label>
                                <p class="font-medium">{{ invoice.customer.name }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Email</label>
                                <p>{{ invoice.customer.email }}</p>
                            </div>
                            <div v-if="invoice.customer.phone">
                                <label class="text-sm font-medium text-muted-foreground">Telepon</label>
                                <p>{{ invoice.customer.phone }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Order Information -->
                <Card v-if="invoice.order" class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Package class="h-5 w-5" />
                            Informasi Order
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Order Number</label>
                                <p class="font-medium">{{ invoice.order.order_number }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Order Type</label>
                                <p>{{ getOrderTypeDisplay(invoice.order) }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Billing Cycle</label>
                                <p>{{ invoice.order.billing_cycle }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Payment Information -->
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Building2 class="h-5 w-5" />
                            Informasi Pembayaran
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="invoice.payment_account" class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Jenis</label>
                                    <p class="font-medium">{{ invoice.payment_account.type }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Nama</label>
                                    <p class="font-medium">{{ invoice.payment_account.name }}</p>
                                </div>
                            </div>

                            <div v-if="invoice.payment_account.type === 'bank'" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Nomor Rekening</label>
                                    <p class="font-mono">{{ invoice.payment_account.account_number || '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Nama Pemilik</label>
                                    <p>{{ invoice.payment_account.account_name || '-' }}</p>
                                </div>
                            </div>

                            <div v-else-if="invoice.payment_account.type === 'qris'" class="space-y-2">
                                <label class="text-sm font-medium text-muted-foreground">QRIS</label>
                                <img
                                    v-if="invoice.payment_account.qris_image_path"
                                    :src="invoice.payment_account.qris_image_path"
                                    alt="QRIS"
                                    class="h-56 w-56 rounded-md border border-border/60 object-contain"
                                />
                            </div>

                            <div v-if="invoice.payment_method" class="text-sm text-muted-foreground">
                                {{ invoice.payment_method }}
                            </div>
                        </div>
                        <div v-else-if="invoice.bank" class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Bank</label>
                                    <p class="font-medium">{{ invoice.bank.bank_name }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Nomor Rekening</label>
                                    <p class="font-mono">{{ invoice.bank.account_number }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-sm font-medium text-muted-foreground">Nama Pemilik</label>
                                    <p>{{ invoice.bank.account_name }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-8 text-center text-muted-foreground">
                            <Building2 class="mx-auto h-12 w-12 text-muted-foreground/40" />
                            <h3 class="mt-2 text-sm font-semibold">Metode Pembayaran Belum Dipilih</h3>
                            <p class="mt-1 text-sm">Silakan pilih metode pembayaran untuk melanjutkan.</p>
                            <Button v-if="canPay()" :as="Link" :href="`/customer/invoices/${invoice.id}/payment`" class="mt-4">
                                <CreditCard class="mr-2 h-4 w-4" />
                                Pilih Metode Pembayaran
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Notes -->
                <Card v-if="invoice.notes" class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader>
                        <CardTitle>Catatan</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">{{ invoice.notes }}</p>
                    </CardContent>
                </Card>
                </div>
                <div class="space-y-4 md:col-span-2">
                    <Card class="rounded-lg border-border/60 shadow-sm">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-base">Ringkasan</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Invoice</span>
                                <span class="font-mono font-medium">{{ invoice.invoice_number }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Status</span>
                                <Badge :class="getStatusClass(invoice.status)">{{ getStatusText(invoice.status) }}</Badge>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Jatuh tempo</span>
                                <span :class="cn('font-medium', isOverdue() ? 'text-red-600 dark:text-red-400' : '')">{{ formatDate(invoice.due_date) }}</span>
                            </div>
                            <div v-if="invoice.paid_at" class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Dibayar</span>
                                <span class="font-medium">{{ formatDate(invoice.paid_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Dibuat</span>
                                <span class="font-medium">{{ formatDate(invoice.created_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Total</span>
                                <span class="font-serif text-xl font-medium text-emerald-700 dark:text-green-400">{{ formatPrice(invoice.amount) }}</span>
                            </div>
                            <Button
                                v-if="canPay()"
                                :as="Link"
                                :href="`/customer/invoices/${invoice.id}/payment`"
                                size="sm"
                                class="w-full justify-between"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <CreditCard class="h-4 w-4" />
                                    Bayar Sekarang
                                </span>
                                <ArrowRight class="h-4 w-4 opacity-80" />
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
