<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatPrice } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { CheckCircle, Clock, CreditCard, DollarSign, Plus, Repeat } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Expense {
    id: number;
    name: string;
    amount: number;
    currency: string;
    provider: string;
    category: string;
    next_billing?: string;
    paid_date?: string;
    status: 'active' | 'inactive' | 'pending' | 'paid' | 'cancelled';
    type: 'monthly' | 'yearly' | 'one-time';
}

interface Props {
    monthlyExpenses: Expense[];
    yearlyExpenses: Expense[];
    oneTimeExpenses: Expense[];
}

const props = defineProps<Props>();

// Active tab state
const activeTab = ref('monthly');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Financial', href: '#' },
    { title: 'Data Pengeluaran', href: '/admin/expenses' },
];

const getStatusColor = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'inactive':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'paid':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'cancelled':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
    }
};

const getStatusText = (status: string) => {
    switch (status) {
        case 'active': return 'Aktif';
        case 'inactive': return 'Tidak Aktif';
        case 'pending': return 'Pending';
        case 'paid': return 'Dibayar';
        case 'cancelled': return 'Dibatalkan';
        default: return status;
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// Calculate totals
const totalMonthly = computed(() => {
    return props.monthlyExpenses
        .filter(expense => expense.status === 'active')
        .reduce((total, expense) => {
            if (expense.currency === 'USD') {
                return total + (expense.amount * 15000);
            }
            return total + expense.amount;
        }, 0);
});

const totalYearly = computed(() => {
    return props.yearlyExpenses
        .filter(expense => expense.status === 'active')
        .reduce((total, expense) => {
            if (expense.currency === 'USD') {
                return total + (expense.amount * 15000);
            }
            return total + expense.amount;
        }, 0);
});

const totalOneTime = computed(() => {
    return props.oneTimeExpenses
        .filter(expense => expense.status === 'paid')
        .reduce((total, expense) => {
            if (expense.currency === 'USD') {
                return total + (expense.amount * 15000);
            }
            return total + expense.amount;
        }, 0);
});

const grandTotal = computed(() => {
    return totalMonthly.value + totalYearly.value + totalOneTime.value;
});

const currentYear = new Date().getFullYear();
const thisYearOneTime = computed(() => {
    return props.oneTimeExpenses.filter(expense => {
        const expenseYear = new Date(expense.paid_date || '').getFullYear();
        return expenseYear === currentYear && expense.status === 'paid';
    });
});

const thisYearOneTimeTotal = computed(() => {
    return thisYearOneTime.value.reduce((total, expense) => {
        if (expense.currency === 'USD') {
            return total + (expense.amount * 15000);
        }
        return total + expense.amount;
    }, 0);
});
</script>

<template>
    <Head title="Data Pengeluaran" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Data Pengeluaran</h1>
                    <p class="text-muted-foreground">Kelola semua pengeluaran bisnis terorganisir berdasarkan jenis pembayaran</p>
                </div>
                <Button class="cursor-pointer">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Pengeluaran
                </Button>
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Keseluruhan</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatPrice(grandTotal, 'IDR') }}</div>
                        <p class="text-xs text-muted-foreground">
                            Bulanan + Tahunan + One-time
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pengeluaran Bulanan</CardTitle>
                        <Repeat class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatPrice(totalMonthly, 'IDR') }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ props.monthlyExpenses.filter(e => e.status === 'active').length }} layanan aktif
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pengeluaran Tahunan</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatPrice(totalYearly, 'IDR') }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ props.yearlyExpenses.filter(e => e.status === 'active').length }} layanan aktif
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Tahun {{ currentYear }}</CardTitle>
                        <CheckCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatPrice(thisYearOneTimeTotal, 'IDR') }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ thisYearOneTime.length }} transaksi sekali bayar
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Custom Tabs for Different Types -->
            <div class="w-full">
                <!-- Tab Navigation -->
                <div class="border-b border-border">
                    <nav class="-mb-px flex space-x-8">
                        <button
                            @click="activeTab = 'monthly'"
                            :class="[
                                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
                                activeTab === 'monthly'
                                    ? 'border-primary text-primary'
                                    : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'
                            ]"
                        >
                            <Repeat class="h-4 w-4 mr-2 inline" />
                            Bulanan
                        </button>
                        <button
                            @click="activeTab = 'yearly'"
                            :class="[
                                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
                                activeTab === 'yearly'
                                    ? 'border-primary text-primary'
                                    : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'
                            ]"
                        >
                            <Clock class="h-4 w-4 mr-2 inline" />
                            Tahunan
                        </button>
                        <button
                            @click="activeTab = 'one-time'"
                            :class="[
                                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
                                activeTab === 'one-time'
                                    ? 'border-primary text-primary'
                                    : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'
                            ]"
                        >
                            <CreditCard class="h-4 w-4 mr-2 inline" />
                            Sekali Bayar
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="mt-6">
                    <!-- Monthly Expenses -->
                    <div v-show="activeTab === 'monthly'" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Repeat class="h-5 w-5" />
                                Pengeluaran Bulanan
                            </CardTitle>
                            <CardDescription>
                                Kelola pengeluaran berulang setiap bulan seperti lisensi software dan layanan
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Nama Layanan</TableHead>
                                        <TableHead>Provider</TableHead>
                                        <TableHead>Kategori</TableHead>
                                        <TableHead>Biaya</TableHead>
                                        <TableHead>Billing Berikutnya</TableHead>
                                        <TableHead>Status</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="expense in props.monthlyExpenses" :key="expense.id">
                                        <TableCell class="font-medium">{{ expense.name }}</TableCell>
                                        <TableCell>{{ expense.provider }}</TableCell>
                                        <TableCell>{{ expense.category }}</TableCell>
                                        <TableCell>{{ formatPrice(expense.amount, expense.currency) }}</TableCell>
                                        <TableCell>{{ expense.next_billing ? formatDate(expense.next_billing) : '-' }}</TableCell>
                                        <TableCell>
                                            <Badge :class="getStatusColor(expense.status)">
                                                {{ getStatusText(expense.status) }}
                                            </Badge>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                    </div>

                    <!-- Yearly Expenses -->
                    <div v-show="activeTab === 'yearly'" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Clock class="h-5 w-5" />
                                Pengeluaran Tahunan
                            </CardTitle>
                            <CardDescription>
                                Kelola pengeluaran berulang setiap tahun seperti lisensi domain dan software
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Nama Layanan</TableHead>
                                        <TableHead>Provider</TableHead>
                                        <TableHead>Kategori</TableHead>
                                        <TableHead>Biaya</TableHead>
                                        <TableHead>Renewal Berikutnya</TableHead>
                                        <TableHead>Status</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="expense in props.yearlyExpenses" :key="expense.id">
                                        <TableCell class="font-medium">{{ expense.name }}</TableCell>
                                        <TableCell>{{ expense.provider }}</TableCell>
                                        <TableCell>{{ expense.category }}</TableCell>
                                        <TableCell>{{ formatPrice(expense.amount, expense.currency) }}</TableCell>
                                        <TableCell>{{ expense.next_billing ? formatDate(expense.next_billing) : '-' }}</TableCell>
                                        <TableCell>
                                            <Badge :class="getStatusColor(expense.status)">
                                                {{ getStatusText(expense.status) }}
                                            </Badge>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                    </div>

                    <!-- One-time Expenses -->
                    <div v-show="activeTab === 'one-time'" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <CreditCard class="h-5 w-5" />
                                Pengeluaran Sekali Bayar
                            </CardTitle>
                            <CardDescription>
                                Riwayat pengeluaran yang dibayar sekali untuk investasi dan setup infrastruktur
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Nama Pengeluaran</TableHead>
                                        <TableHead>Provider</TableHead>
                                        <TableHead>Kategori</TableHead>
                                        <TableHead>Biaya</TableHead>
                                        <TableHead>Tanggal Pembayaran</TableHead>
                                        <TableHead>Status</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="expense in props.oneTimeExpenses" :key="expense.id">
                                        <TableCell class="font-medium">{{ expense.name }}</TableCell>
                                        <TableCell>{{ expense.provider }}</TableCell>
                                        <TableCell>{{ expense.category }}</TableCell>
                                        <TableCell>{{ formatPrice(expense.amount, expense.currency) }}</TableCell>
                                        <TableCell>{{ expense.paid_date ? formatDate(expense.paid_date) : '-' }}</TableCell>
                                        <TableCell>
                                            <Badge :class="getStatusColor(expense.status)">
                                                {{ getStatusText(expense.status) }}
                                            </Badge>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>