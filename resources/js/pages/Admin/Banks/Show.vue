<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, formatPrice } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Building2, Edit, FileText, ToggleLeft, ToggleRight, Trash2 } from 'lucide-vue-next';

interface Bank {
    id: number;
    bank_name: string;
    bank_code: string;
    account_number: string;
    account_name: string;
    branch?: string;
    swift_code?: string;
    description?: string;
    is_active: boolean;
    admin_fee: number;
    bank_type: 'local' | 'international';
    created_at: string;
    updated_at: string;
}

interface Invoice {
    id: number;
    invoice_number: string;
    amount: number;
    status: string;
    due_date: string;
    paid_at?: string;
    payment_method?: string;
    customer: {
        id: number;
        name: string;
        email: string;
    };
    order: {
        id: number;
        order_number: string;
    };
}

interface Props {
    bank: Bank;
    recentInvoices: Invoice[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Bank Management', href: '/admin/banks' },
    { title: props.bank.bank_name, href: `/admin/banks/${props.bank.id}` },
];

const getStatusClass = (isActive: boolean) => {
    return isActive
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
};

const getBankTypeClass = (type: string) => {
    return type === 'local'
        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
        : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
};

const getInvoiceStatusClass = (status: string) => {
    switch (status) {
        case 'paid':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        case 'overdue':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        case 'cancelled':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
    }
};

const getInvoiceStatusText = (status: string) => {
    switch (status) {
        case 'paid':
            return 'Lunas';
        case 'pending':
            return 'Menunggu';
        case 'overdue':
            return 'Terlambat';
        case 'cancelled':
            return 'Dibatalkan';
        default:
            return status;
    }
};

const toggleBankStatus = () => {
    router.patch(
        `/admin/banks/${props.bank.id}/toggle-status`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                // Success message will be handled by flash messages
            },
        },
    );
};

const deleteBank = () => {
    if (confirm(`Apakah Anda yakin ingin menghapus bank ${props.bank.bank_name}?`)) {
        router.delete(`/admin/banks/${props.bank.id}`);
    }
};
</script>

<template>
    <Head :title="bank.bank_name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href="/admin/banks">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ bank.bank_name }}</h1>
                        <p class="text-muted-foreground">Detail informasi bank pembayaran</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" asChild>
                        <Link :href="`/admin/banks/${bank.id}/edit`">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit
                        </Link>
                    </Button>
                    <Button variant="outline" size="sm" @click="toggleBankStatus">
                        <ToggleRight v-if="bank.is_active" class="mr-2 h-4 w-4 text-green-600" />
                        <ToggleLeft v-else class="mr-2 h-4 w-4 text-red-600" />
                        {{ bank.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </Button>
                    <Button variant="destructive" size="sm" @click="deleteBank">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Hapus
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Bank Details -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Building2 class="h-5 w-5" />
                                Informasi Bank
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Nama Bank</Label>
                                    <p class="text-sm font-medium">{{ bank.bank_name }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Kode Bank</Label>
                                    <p class="inline-block rounded bg-muted px-2 py-1 font-mono text-sm">{{ bank.bank_code }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Nomor Rekening</Label>
                                    <p class="font-mono text-sm">{{ bank.account_number }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Nama Pemilik</Label>
                                    <p class="text-sm">{{ bank.account_name }}</p>
                                </div>
                                <div v-if="bank.branch">
                                    <Label class="text-sm font-medium text-muted-foreground">Cabang</Label>
                                    <p class="text-sm">{{ bank.branch }}</p>
                                </div>
                                <div v-if="bank.swift_code">
                                    <Label class="text-sm font-medium text-muted-foreground">Kode SWIFT</Label>
                                    <p class="font-mono text-sm">{{ bank.swift_code }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Tipe Bank</Label>
                                    <Badge :class="getBankTypeClass(bank.bank_type)">
                                        {{ bank.bank_type === 'local' ? 'Lokal' : 'Internasional' }}
                                    </Badge>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Biaya Admin</Label>
                                    <p class="text-sm font-medium">{{ formatPrice(bank.admin_fee) }}</p>
                                </div>
                            </div>

                            <div v-if="bank.description">
                                <Label class="text-sm font-medium text-muted-foreground">Deskripsi</Label>
                                <p class="mt-1 text-sm text-muted-foreground">{{ bank.description }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recent Invoices -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <FileText class="h-5 w-5" />
                                Invoice Terbaru
                            </CardTitle>
                            <CardDescription> Invoice yang menggunakan bank ini </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="recentInvoices.length > 0">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Invoice</TableHead>
                                            <TableHead>Customer</TableHead>
                                            <TableHead>Amount</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead>Due Date</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="invoice in recentInvoices" :key="invoice.id">
                                            <TableCell>
                                                <div>
                                                    <Link :href="`/admin/invoices/${invoice.id}`" class="font-medium hover:underline">
                                                        {{ invoice.invoice_number }}
                                                    </Link>
                                                    <div class="text-sm text-muted-foreground">Order: {{ invoice.order.order_number }}</div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div>
                                                    <div class="font-medium">{{ invoice.customer.name }}</div>
                                                    <div class="text-sm text-muted-foreground">{{ invoice.customer.email }}</div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <span class="font-medium">{{ formatPrice(invoice.amount) }}</span>
                                            </TableCell>
                                            <TableCell>
                                                <Badge :class="getInvoiceStatusClass(invoice.status)">
                                                    {{ getInvoiceStatusText(invoice.status) }}
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
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                            <div v-else class="py-8 text-center">
                                <FileText class="mx-auto h-12 w-12 text-muted-foreground/40" />
                                <h3 class="mt-2 text-sm font-semibold">Belum ada invoice</h3>
                                <p class="mt-1 text-sm text-muted-foreground">Belum ada invoice yang menggunakan bank ini.</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Status Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Status</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Status Bank</span>
                                <Badge :class="getStatusClass(bank.is_active)">
                                    {{ bank.is_active ? 'Aktif' : 'Nonaktif' }}
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Dibuat</span>
                                <span class="text-sm text-muted-foreground">{{ formatDate(bank.created_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Diperbarui</span>
                                <span class="text-sm text-muted-foreground">{{ formatDate(bank.updated_at) }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Aksi Cepat</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Button variant="outline" size="sm" class="w-full justify-start" asChild>
                                <Link :href="`/admin/banks/${bank.id}/edit`">
                                    <Edit class="mr-2 h-4 w-4" />
                                    Edit Bank
                                </Link>
                            </Button>
                            <Button variant="outline" size="sm" class="w-full justify-start" @click="toggleBankStatus">
                                <ToggleRight v-if="bank.is_active" class="mr-2 h-4 w-4" />
                                <ToggleLeft v-else class="mr-2 h-4 w-4" />
                                {{ bank.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </Button>
                            <Button variant="destructive" size="sm" class="w-full justify-start" @click="deleteBank">
                                <Trash2 class="mr-2 h-4 w-4" />
                                Hapus Bank
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
