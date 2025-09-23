<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { formatDate, formatPrice } from '@/lib/utils';
import customer from '@/routes/customer';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { CreditCard, Eye, FileText } from 'lucide-vue-next';

interface Props {
    invoices: {
        data: Array<any>;
        links?: Array<any>;
        from?: number;
        to?: number;
        total?: number;
    };
}

defineProps<Props>();

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
    
    const itemTypes = order.order_items.map(item => item.item_type);
    const uniqueTypes = [...new Set(itemTypes)];
    
    if (uniqueTypes.length === 1) {
        const type = uniqueTypes[0];
        const typeMap = {
            'hosting': 'Hosting',
            'domain': 'Domain',
            'service': 'Service',
            'app': 'Aplikasi',
            'web': 'Website',
            'maintenance': 'Maintenance'
        };
        return typeMap[type] || type;
    }
    
    return `Mixed (${uniqueTypes.length} types)`;
};
</script>

<template>
    <Head title="Invoice Saya" />

    <CustomerLayout :breadcrumbs="breadcrumbs">

        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Invoice Saya</h1>
                <p class="text-muted-foreground">Kelola dan bayar invoice Anda</p>
            </div>
            <div>
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <FileText class="h-5 w-5" />
                            Daftar Invoice
                        </CardTitle>
                        <CardDescription> Semua invoice yang terkait dengan akun Anda </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="invoices.data.length > 0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Invoice</TableHead>
                                        <TableHead>Order</TableHead>
                                        <TableHead>Amount</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Due Date</TableHead>
                                        <TableHead>Bank</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="invoice in invoices.data" :key="invoice.id">
                                        <TableCell>
                                            <div>
                                                <div class="font-medium">{{ invoice.invoice_number }}</div>
                                                <div class="text-sm text-muted-foreground">
                                                    {{ formatDate(invoice.created_at) }}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div v-if="invoice.order">
                                                <div class="font-medium">{{ invoice.order.order_number }}</div>
                                                <div class="text-sm text-muted-foreground">{{ getOrderTypeDisplay(invoice.order) }}</div>
                                            </div>
                                            <div v-else class="text-sm text-muted-foreground">Service Invoice</div>
                                        </TableCell>
                                        <TableCell>
                                            <span class="font-medium">{{ formatPrice(invoice.amount) }}</span>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :class="getStatusClass(invoice.status)">
                                                {{ getStatusText(invoice.status) }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-sm">
                                                {{ formatDate(invoice.due_date) }}
                                                <div v-if="invoice.paid_at" class="text-xs text-muted-foreground">
                                                    Paid: {{ formatDate(invoice.paid_at) }}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div v-if="invoice.bank" class="text-sm">
                                                <div class="font-medium">{{ invoice.bank.bank_name }}</div>
                                                <div class="text-muted-foreground">{{ invoice.bank.bank_code }}</div>
                                            </div>
                                            <div v-else class="text-sm text-muted-foreground">Belum dipilih</div>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <Button :as="Link" :href="customer.invoices.show(invoice.id).url" variant="outline" size="sm">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    v-if="invoice.status !== 'paid' && invoice.status !== 'cancelled'"
                                                    :as="Link"
                                                    :href="customer.invoices.payment(invoice.id).url"
                                                    size="sm"
                                                >
                                                    <CreditCard class="mr-1 h-4 w-4" />
                                                    Bayar
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>

                            <!-- Pagination -->
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
                                                'rounded-md px-3 py-2 text-sm cursor-pointer',
                                                link.active ? 'bg-primary text-primary-foreground' : 'border bg-background hover:bg-muted',
                                            ]"
                                            v-html="link.label"
                                        />
                                        <span
                                            v-else
                                            :class="[
                                                'rounded-md px-3 py-2 text-sm cursor-not-allowed opacity-50',
                                                'border bg-background',
                                            ]"
                                            v-html="link.label"
                                        />
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="py-12 text-center">
                            <FileText class="mx-auto h-12 w-12 text-muted-foreground/40" />
                            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">Belum ada invoice</h3>
                            <p class="mt-1 text-sm text-muted-foreground">Invoice Anda akan muncul di sini setelah melakukan pemesanan.</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </CustomerLayout>
</template>
