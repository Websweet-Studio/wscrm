<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DatePicker } from '@/components/ui/date-picker';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatPrice } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CheckCircle, Clock, CreditCard, DollarSign, Edit, Plus, Repeat, Trash2, X } from 'lucide-vue-next';
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

// Modal states
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedExpense = ref<Expense | null>(null);
const expenseToDelete = ref<Expense | null>(null);

// Form states
const createForm = useForm({
    name: '',
    amount: '',
    currency: 'IDR',
    provider: '',
    category: '',
    next_billing: '',
    paid_date: '',
    status: 'active' as 'active' | 'inactive' | 'pending' | 'paid' | 'cancelled',
    type: 'monthly' as 'monthly' | 'yearly' | 'one-time',
    description: '',
});

const editForm = useForm({
    name: '',
    amount: '',
    currency: 'IDR',
    provider: '',
    category: '',
    next_billing: '',
    paid_date: '',
    status: 'active' as 'active' | 'inactive' | 'pending' | 'paid' | 'cancelled',
    type: 'monthly' as 'monthly' | 'yearly' | 'one-time',
    description: '',
});

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
            const amount = parseFloat(expense.amount) || 0;
            const amountInIDR = expense.currency === 'USD' ? (amount * 15000) : amount;
            return total + amountInIDR;
        }, 0);
});

const totalYearly = computed(() => {
    return props.yearlyExpenses
        .filter(expense => expense.status === 'active')
        .reduce((total, expense) => {
            const amount = parseFloat(expense.amount) || 0;
            const amountInIDR = expense.currency === 'USD' ? (amount * 15000) : amount;
            return total + amountInIDR;
        }, 0);
});

const totalOneTime = computed(() => {
    return props.oneTimeExpenses
        .filter(expense => expense.status === 'paid')
        .reduce((total, expense) => {
            const amount = parseFloat(expense.amount) || 0;
            const amountInIDR = expense.currency === 'USD' ? (amount * 15000) : amount;
            return total + amountInIDR;
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
        const amount = parseFloat(expense.amount) || 0;
        const amountInIDR = expense.currency === 'USD' ? (amount * 15000) : amount;
        return total + amountInIDR;
    }, 0);
});

// CRUD functions
const openCreateModal = (type: 'monthly' | 'yearly' | 'one-time') => {
    createForm.reset();
    createForm.type = type;
    showCreateModal.value = true;
};

const submitCreate = () => {
    createForm.post('/admin/expenses', {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
        onError: (errors) => {
            console.error('Create expense error:', errors);
        },
    });
};

const openEditModal = (expense: Expense) => {
    selectedExpense.value = expense;
    editForm.reset();
    editForm.name = expense.name;
    editForm.amount = expense.amount.toString();
    editForm.currency = expense.currency;
    editForm.provider = expense.provider;
    editForm.category = expense.category;
    editForm.next_billing = expense.next_billing || '';
    editForm.paid_date = expense.paid_date || '';
    editForm.status = expense.status;
    editForm.type = expense.type;
    editForm.description = '';
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!selectedExpense.value) return;

    editForm.put(`/admin/expenses/${selectedExpense.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            selectedExpense.value = null;
        },
        onError: (errors) => {
            console.error('Update expense error:', errors);
        },
    });
};

const openDeleteModal = (expense: Expense) => {
    expenseToDelete.value = expense;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!expenseToDelete.value) return;

    router.delete(`/admin/expenses/${expenseToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            expenseToDelete.value = null;
        },
        onError: (errors) => {
            console.error('Delete expense error:', errors);
        },
    });
};
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
                <Button @click="openCreateModal(activeTab as 'monthly' | 'yearly' | 'one-time')" class="cursor-pointer">
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
                                        <TableHead class="w-[100px]">Aksi</TableHead>
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
                                        <TableCell>
                                            <div class="flex items-center gap-2">
                                                <Button size="sm" variant="outline" @click="openEditModal(expense)" class="cursor-pointer">
                                                    <Edit class="h-3 w-3" />
                                                </Button>
                                                <Button size="sm" variant="outline" @click="openDeleteModal(expense)" class="cursor-pointer">
                                                    <Trash2 class="h-3 w-3" />
                                                </Button>
                                            </div>
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
                                        <TableHead class="w-[100px]">Aksi</TableHead>
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
                                        <TableCell>
                                            <div class="flex items-center gap-2">
                                                <Button size="sm" variant="outline" @click="openEditModal(expense)" class="cursor-pointer">
                                                    <Edit class="h-3 w-3" />
                                                </Button>
                                                <Button size="sm" variant="outline" @click="openDeleteModal(expense)" class="cursor-pointer">
                                                    <Trash2 class="h-3 w-3" />
                                                </Button>
                                            </div>
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
                                        <TableHead class="w-[100px]">Aksi</TableHead>
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
                                        <TableCell>
                                            <div class="flex items-center gap-2">
                                                <Button size="sm" variant="outline" @click="openEditModal(expense)" class="cursor-pointer">
                                                    <Edit class="h-3 w-3" />
                                                </Button>
                                                <Button size="sm" variant="outline" @click="openDeleteModal(expense)" class="cursor-pointer">
                                                    <Trash2 class="h-3 w-3" />
                                                </Button>
                                            </div>
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

        <!-- Create Expense Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Tambah Pengeluaran Baru</h2>
                    <button @click="showCreateModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <Label for="name">Nama Pengeluaran</Label>
                        <Input
                            id="name"
                            v-model="createForm.name"
                            type="text"
                            placeholder="Nama layanan atau pengeluaran"
                            required
                        />
                        <span v-if="createForm.errors.name" class="text-sm text-red-500">{{ createForm.errors.name }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="amount">Jumlah</Label>
                            <Input
                                id="amount"
                                v-model="createForm.amount"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                required
                            />
                            <span v-if="createForm.errors.amount" class="text-sm text-red-500">{{ createForm.errors.amount }}</span>
                        </div>
                        <div>
                            <Label for="currency">Mata Uang</Label>
                            <select
                                id="currency"
                                v-model="createForm.currency"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option value="IDR">IDR (Rupiah)</option>
                                <option value="USD">USD (Dollar)</option>
                            </select>
                            <span v-if="createForm.errors.currency" class="text-sm text-red-500">{{ createForm.errors.currency }}</span>
                        </div>
                    </div>

                    <div>
                        <Label for="provider">Provider</Label>
                        <Input
                            id="provider"
                            v-model="createForm.provider"
                            type="text"
                            placeholder="Nama penyedia layanan"
                            required
                        />
                        <span v-if="createForm.errors.provider" class="text-sm text-red-500">{{ createForm.errors.provider }}</span>
                    </div>

                    <div>
                        <Label for="category">Kategori (Opsional)</Label>
                        <Input
                            id="category"
                            v-model="createForm.category"
                            type="text"
                            placeholder="Kategori pengeluaran (opsional)"
                        />
                        <span v-if="createForm.errors.category" class="text-sm text-red-500">{{ createForm.errors.category }}</span>
                    </div>

                    <div>
                        <Label for="type">Tipe Pengeluaran</Label>
                        <select
                            id="type"
                            v-model="createForm.type"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            required
                        >
                            <option value="monthly">Bulanan</option>
                            <option value="yearly">Tahunan</option>
                            <option value="one-time">Sekali Bayar</option>
                        </select>
                        <span v-if="createForm.errors.type" class="text-sm text-red-500">{{ createForm.errors.type }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="next_billing">Billing Berikutnya</Label>
                            <DatePicker
                                v-model="createForm.next_billing"
                                placeholder="Pilih tanggal billing"
                            />
                            <span v-if="createForm.errors.next_billing" class="text-sm text-red-500">{{ createForm.errors.next_billing }}</span>
                        </div>
                        <div>
                            <Label for="paid_date">Tanggal Pembayaran</Label>
                            <DatePicker
                                v-model="createForm.paid_date"
                                placeholder="Pilih tanggal pembayaran"
                            />
                            <span v-if="createForm.errors.paid_date" class="text-sm text-red-500">{{ createForm.errors.paid_date }}</span>
                        </div>
                    </div>

                    <div>
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            v-model="createForm.status"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            required
                        >
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Dibayar</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                        <span v-if="createForm.errors.status" class="text-sm text-red-500">{{ createForm.errors.status }}</span>
                    </div>

                    <div>
                        <Label for="description">Deskripsi (Opsional)</Label>
                        <Input
                            id="description"
                            v-model="createForm.description"
                            type="text"
                            placeholder="Deskripsi tambahan"
                        />
                        <span v-if="createForm.errors.description" class="text-sm text-red-500">{{ createForm.errors.description }}</span>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="showCreateModal = false" class="cursor-pointer"> Batal </Button>
                        <Button type="submit" :disabled="createForm.processing">
                            {{ createForm.processing ? 'Membuat...' : 'Buat Pengeluaran' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Expense Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Edit Pengeluaran</h2>
                    <button @click="showEditModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <Label for="edit_name">Nama Pengeluaran</Label>
                        <Input
                            id="edit_name"
                            v-model="editForm.name"
                            type="text"
                            placeholder="Nama layanan atau pengeluaran"
                            required
                        />
                        <span v-if="editForm.errors.name" class="text-sm text-red-500">{{ editForm.errors.name }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="edit_amount">Jumlah</Label>
                            <Input
                                id="edit_amount"
                                v-model="editForm.amount"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                required
                            />
                            <span v-if="editForm.errors.amount" class="text-sm text-red-500">{{ editForm.errors.amount }}</span>
                        </div>
                        <div>
                            <Label for="edit_currency">Mata Uang</Label>
                            <select
                                id="edit_currency"
                                v-model="editForm.currency"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option value="IDR">IDR (Rupiah)</option>
                                <option value="USD">USD (Dollar)</option>
                            </select>
                            <span v-if="editForm.errors.currency" class="text-sm text-red-500">{{ editForm.errors.currency }}</span>
                        </div>
                    </div>

                    <div>
                        <Label for="edit_provider">Provider</Label>
                        <Input
                            id="edit_provider"
                            v-model="editForm.provider"
                            type="text"
                            placeholder="Nama penyedia layanan"
                            required
                        />
                        <span v-if="editForm.errors.provider" class="text-sm text-red-500">{{ editForm.errors.provider }}</span>
                    </div>

                    <div>
                        <Label for="edit_category">Kategori (Opsional)</Label>
                        <Input
                            id="edit_category"
                            v-model="editForm.category"
                            type="text"
                            placeholder="Kategori pengeluaran (opsional)"
                        />
                        <span v-if="editForm.errors.category" class="text-sm text-red-500">{{ editForm.errors.category }}</span>
                    </div>

                    <div>
                        <Label for="edit_type">Tipe Pengeluaran</Label>
                        <select
                            id="edit_type"
                            v-model="editForm.type"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            required
                        >
                            <option value="monthly">Bulanan</option>
                            <option value="yearly">Tahunan</option>
                            <option value="one-time">Sekali Bayar</option>
                        </select>
                        <span v-if="editForm.errors.type" class="text-sm text-red-500">{{ editForm.errors.type }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="edit_next_billing">Billing Berikutnya</Label>
                            <DatePicker
                                v-model="editForm.next_billing"
                                placeholder="Pilih tanggal billing"
                            />
                            <span v-if="editForm.errors.next_billing" class="text-sm text-red-500">{{ editForm.errors.next_billing }}</span>
                        </div>
                        <div>
                            <Label for="edit_paid_date">Tanggal Pembayaran</Label>
                            <DatePicker
                                v-model="editForm.paid_date"
                                placeholder="Pilih tanggal pembayaran"
                            />
                            <span v-if="editForm.errors.paid_date" class="text-sm text-red-500">{{ editForm.errors.paid_date }}</span>
                        </div>
                    </div>

                    <div>
                        <Label for="edit_status">Status</Label>
                        <select
                            id="edit_status"
                            v-model="editForm.status"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            required
                        >
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Dibayar</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                        <span v-if="editForm.errors.status" class="text-sm text-red-500">{{ editForm.errors.status }}</span>
                    </div>

                    <div>
                        <Label for="edit_description">Deskripsi (Opsional)</Label>
                        <Input
                            id="edit_description"
                            v-model="editForm.description"
                            type="text"
                            placeholder="Deskripsi tambahan"
                        />
                        <span v-if="editForm.errors.description" class="text-sm text-red-500">{{ editForm.errors.description }}</span>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="showEditModal = false" class="cursor-pointer"> Batal </Button>
                        <Button type="submit" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Memperbarui...' : 'Perbarui Pengeluaran' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Expense Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-red-600">Konfirmasi Penghapusan</h2>
                    <button @click="showDeleteModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        Apakah Anda yakin ingin menghapus pengeluaran ini?
                    </p>

                    <div v-if="expenseToDelete" class="rounded-lg border p-4">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="font-medium">Nama:</span>
                                <span>{{ expenseToDelete.name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Provider:</span>
                                <span>{{ expenseToDelete.provider }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Biaya:</span>
                                <span>{{ formatPrice(expenseToDelete.amount, expenseToDelete.currency) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                        <div class="flex">
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-400">
                                    Peringatan
                                </h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <p>
                                        Tindakan ini tidak dapat dibatalkan. Data pengeluaran akan dihapus secara permanen.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="showDeleteModal = false" class="cursor-pointer"> Batal </Button>
                    <Button type="button" class="cursor-pointer bg-red-600 text-white hover:bg-red-700" @click="confirmDelete">
                        Ya, Hapus Pengeluaran
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>