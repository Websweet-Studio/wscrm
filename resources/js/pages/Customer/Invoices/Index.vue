<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { cn, formatDate, formatPrice } from '@/lib/utils';
import customer from '@/routes/customer';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, CreditCard, Eye, FileText, ReceiptText } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    invoices: {
        data: Array<any>;
        links?: Array<any>;
        from?: number;
        to?: number;
        total?: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: customer.dashboard().url },
    { title: 'Invoices', href: customer.invoices.index().url },
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

const totalInvoices = computed(() => props.invoices.total ?? props.invoices.data.length);
const unpaidInvoices = computed(() => props.invoices.data.filter((i) => i.status === 'sent' || i.status === 'overdue').length);
const paidInvoices = computed(() => props.invoices.data.filter((i) => i.status === 'paid').length);
</script>

<template>
    <Head title="Invoice Saya" />

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
                                <span>Tagihan</span>
                            </div>
                            <h1 class="font-serif text-2xl font-medium tracking-tight sm:text-3xl">Invoice Saya</h1>
                            <p class="mt-1 text-sm text-muted-foreground sm:text-base">Kelola dan bayar invoice Anda</p>
                        </div>
                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-[240px]">
                            <Button asChild size="sm" class="w-full justify-between">
                                <Link href="/hosting">
                                    <span class="inline-flex items-center gap-2">
                                        <CreditCard class="h-4 w-4" />
                                        Bayar / Upgrade
                                    </span>
                                    <ArrowRight class="h-4 w-4 opacity-80" />
                                </Link>
                            </Button>
                            <Button asChild variant="outline" size="sm" class="w-full justify-between">
                                <Link href="/customer/orders">
                                    <span class="inline-flex items-center gap-2">
                                        <FileText class="h-4 w-4 text-emerald-600 dark:text-green-400" />
                                        Lihat Pesanan
                                    </span>
                                    <ArrowRight class="h-4 w-4 opacity-70" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div v-if="invoices.data.length" class="grid gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-3">
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Total Invoice</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ totalInvoices }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Belum Bayar</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ unpaidInvoices }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Sudah Dibayar</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ paidInvoices }}</CardTitle>
                    </CardHeader>
                </Card>
            </div>

            <Card class="rounded-lg border-border/60 shadow-sm">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <FileText class="h-5 w-5" />
                        Daftar Invoice
                    </CardTitle>
                    <CardDescription>Semua invoice yang terkait dengan akun Anda</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="invoices.data.length > 0">
                        <div class="overflow-hidden rounded-lg border border-border/60">
                            <div class="divide-y divide-border/60">
                                <div v-for="invoice in invoices.data" :key="invoice.id" class="flex items-start justify-between gap-3 bg-background/40 px-3 py-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <div class="font-medium">{{ invoice.invoice_number }}</div>
                                            <Badge :class="getStatusClass(invoice.status)">{{ getStatusText(invoice.status) }}</Badge>
                                        </div>
                                        <div class="mt-1 text-sm text-muted-foreground">
                                            {{ formatDate(invoice.created_at) }}
                                            <span v-if="invoice.order"> • {{ getOrderTypeDisplay(invoice.order) }}</span>
                                        </div>
                                        <div class="mt-0.5 text-[11px] text-muted-foreground">
                                            Jatuh tempo {{ formatDate(invoice.due_date) }}
                                            <span v-if="invoice.paid_at"> • Dibayar {{ formatDate(invoice.paid_at) }}</span>
                                            <span v-if="invoice.bank"> • {{ invoice.bank.bank_name }} ({{ invoice.bank.bank_code }})</span>
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 flex-col items-end gap-2">
                                        <div class="font-serif font-medium text-emerald-700 dark:text-green-400">{{ formatPrice(invoice.amount) }}</div>
                                        <div class="flex items-center gap-2">
                                            <Button :as="Link" :href="customer.invoices.show(invoice.id).url" variant="outline" size="sm" class="h-8 px-3">
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                v-if="invoice.status !== 'paid' && invoice.status !== 'cancelled'"
                                                :as="Link"
                                                :href="customer.invoices.payment(invoice.id).url"
                                                size="sm"
                                                class="h-8 px-3"
                                            >
                                                <CreditCard class="mr-1 h-4 w-4" />
                                                Bayar
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="invoices.links" class="mt-6 flex items-center justify-between">
                            <div class="text-sm text-muted-foreground">
                                Showing {{ invoices.from }} to {{ invoices.to }} of {{ invoices.total }} results
                            </div>
                            <div class="flex items-center gap-2">
                                <template v-for="link in invoices.links" :key="link.label">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        :class="[
                                            'cursor-pointer rounded-md px-3 py-2 text-sm',
                                            link.active ? 'bg-primary text-primary-foreground' : 'border bg-background hover:bg-muted',
                                        ]"
                                        v-html="link.label"
                                    />
                                    <span
                                        v-else
                                        :class="['cursor-not-allowed rounded-md px-3 py-2 text-sm opacity-50', 'border bg-background']"
                                        v-html="link.label"
                                    />
                                </template>
                            </div>
                        </div>
                    </div>

                    <div v-else class="py-12 text-center">
                        <FileText class="mx-auto h-12 w-12 text-muted-foreground/40" />
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">Belum ada invoice</h3>
                        <p class="mt-1 text-sm text-muted-foreground">Invoice Anda akan muncul di sini setelah melakukan pemesanan.</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </CustomerLayout>
</template>
