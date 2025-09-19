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
import { Activity, AlertTriangle, CheckCircle, Clock, CreditCard, DollarSign, Edit, Plus, Repeat, TrendingDown, TrendingUp, Trash2, X } from 'lucide-vue-next';
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

interface RevenueData {
    monthly: number;
    yearly: number;
    oneTime: number;
    totalMonthly: number;
    totalYearly: number;
    currentMonth: string;
    currentYear: number;
}

interface Props {
    monthlyExpenses: Expense[];
    yearlyExpenses: Expense[];
    oneTimeExpenses: Expense[];
    revenueData?: RevenueData;
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
    { title: 'Data Keuangan', href: '/admin/expenses' },
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
        .filter(expense => expense.status === 'active')
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
        // Use created_at if paid_date is null
        const dateToCheck = expense.paid_date || expense.created_at;
        const expenseYear = new Date(dateToCheck).getFullYear();
        return expenseYear === currentYear && expense.status === 'active';
    });
});

const thisYearOneTimeTotal = computed(() => {
    return thisYearOneTime.value.reduce((total, expense) => {
        const amount = parseFloat(expense.amount) || 0;
        const amountInIDR = expense.currency === 'USD' ? (amount * 15000) : amount;
        return total + amountInIDR;
    }, 0);
});

// Revenue calculations (convert from backend which is in IDR)
const monthlyRevenue = computed(() => props.revenueData?.totalMonthly || 0);
const yearlyRevenue = computed(() => props.revenueData?.totalYearly || 0);

// Profit/Loss calculations
const monthlyProfit = computed(() => monthlyRevenue.value - totalMonthly.value);
const yearlyProfit = computed(() => yearlyRevenue.value - (totalYearly.value + totalMonthly.value * 12));
const overallProfit = computed(() => monthlyRevenue.value + yearlyRevenue.value - grandTotal.value);

// Health Analysis
const cashFlowRatio = computed(() => {
    if (totalMonthly.value === 0) return 100;
    return (monthlyRevenue.value / totalMonthly.value) * 100;
});

const expenseToRevenueRatio = computed(() => {
    const totalRevenue = monthlyRevenue.value + yearlyRevenue.value;
    if (totalRevenue === 0) return 100;
    return (grandTotal.value / totalRevenue) * 100;
});

const financialHealth = computed(() => {
    const profitMargin = (overallProfit.value / (monthlyRevenue.value + yearlyRevenue.value)) * 100;

    if (profitMargin >= 20) {
        return { status: 'excellent', label: 'Sangat Sehat', color: 'text-green-600', bgColor: 'bg-green-100', icon: 'TrendingUp' };
    } else if (profitMargin >= 10) {
        return { status: 'good', label: 'Sehat', color: 'text-blue-600', bgColor: 'bg-blue-100', icon: 'Activity' };
    } else if (profitMargin >= 0) {
        return { status: 'warning', label: 'Perlu Perhatian', color: 'text-yellow-600', bgColor: 'bg-yellow-100', icon: 'AlertTriangle' };
    } else {
        return { status: 'danger', label: 'Tidak Sehat', color: 'text-red-600', bgColor: 'bg-red-100', icon: 'TrendingDown' };
    }
});

const burnRate = computed(() => {
    // Monthly burn rate
    return totalMonthly.value;
});

const runwayMonths = computed(() => {
    if (totalMonthly.value === 0) return Infinity;
    if (monthlyProfit.value <= 0) return 0;
    // Simplified calculation - how many months can survive with current profit
    return Math.floor(monthlyProfit.value / totalMonthly.value * 12);
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
    <Head title="Data Keuangan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Modern Dashboard Header -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-purple-900 to-slate-900 p-4 sm:p-6 lg:p-8 text-white mb-6 sm:mb-8">
                <div class="absolute inset-0 bg-gradient-to-r from-purple-600/20 to-blue-600/20"></div>
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <div class="flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                                <DollarSign class="h-5 w-5 sm:h-6 sm:w-6" />
                            </div>
                            <div>
                                <h1 class="text-2xl sm:text-3xl font-bold">Dashboard Keuangan</h1>
                                <p class="text-white/80 text-sm sm:text-base hidden sm:block">Kelola dan pantau semua pengeluaran bisnis dengan mudah</p>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-6 space-y-2 sm:space-y-0 text-xs sm:text-sm">
                            <div class="flex items-center space-x-2">
                                <div class="h-2 w-2 rounded-full bg-green-400"></div>
                                <span class="text-white/90">{{ props.monthlyExpenses.filter(e => e.status === 'active').length + props.yearlyExpenses.filter(e => e.status === 'active').length }} Layanan Aktif</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="h-2 w-2 rounded-full bg-blue-400"></div>
                                <span class="text-white/90">{{ props.revenueData?.currentMonth || 'September 2025' }}</span>
                            </div>
                            <div class="hidden sm:flex items-center space-x-2">
                                <div class="h-2 w-2 rounded-full bg-purple-400"></div>
                                <span class="text-white/90">Real-time Updates</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <Button @click="openCreateModal(activeTab as 'monthly' | 'yearly' | 'one-time')" class="bg-white/20 hover:bg-white/30 text-white border-white/30 backdrop-blur-sm text-sm">
                            <Plus class="mr-2 h-3 w-3 sm:h-4 sm:w-4" />
                            <span class="hidden sm:inline">Tambah Pengeluaran</span>
                            <span class="sm:hidden">Tambah</span>
                        </Button>
                    </div>
                </div>
                <!-- Decorative elements -->
                <div class="absolute -top-4 -right-4 h-24 w-24 rounded-full bg-gradient-to-r from-purple-400 to-blue-400 opacity-20 blur-xl"></div>
                <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-r from-blue-400 to-purple-400 opacity-20 blur-xl"></div>
            </div>

            <!-- Modern Dashboard Summary -->
            <div class="grid gap-4 sm:gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Monthly Profit Card -->
                <Card :class="[
                    'relative overflow-hidden border-0 text-white',
                    monthlyProfit >= 0
                        ? 'bg-gradient-to-r from-emerald-500 to-emerald-600'
                        : 'bg-gradient-to-r from-red-500 to-red-600'
                ]">
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div class="space-y-2 flex-1 min-w-0">
                                <p :class="monthlyProfit >= 0 ? 'text-emerald-100' : 'text-red-100'" class="text-xs sm:text-sm font-medium">
                                    {{ monthlyProfit >= 0 ? 'Keuntungan Bulan Ini' : 'Kerugian Bulan Ini' }}
                                </p>
                                <div class="text-xl sm:text-2xl lg:text-3xl font-bold truncate">
                                    {{ formatPrice(Math.abs(monthlyProfit), 'IDR') }}
                                </div>
                                <div :class="monthlyProfit >= 0 ? 'text-emerald-100' : 'text-red-100'" class="flex items-center space-x-2 text-xs sm:text-sm">
                                    <DollarSign class="h-3 w-3 sm:h-4 sm:w-4 flex-shrink-0" />
                                    <span class="truncate">{{ props.revenueData?.currentMonth || 'September 2025' }}</span>
                                </div>
                            </div>
                            <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-white/20 flex items-center justify-center ml-3 flex-shrink-0">
                                <DollarSign class="h-5 w-5 sm:h-6 sm:w-6" />
                            </div>
                        </div>
                        <div :class="monthlyProfit >= 0 ? 'border-emerald-400/30' : 'border-red-400/30'" class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t">
                            <div :class="monthlyProfit >= 0 ? 'text-emerald-100' : 'text-red-100'" class="space-y-1">
                                <div class="flex justify-between text-xs sm:text-sm">
                                    <span>Pemasukan:</span>
                                    <span class="font-medium">{{ formatPrice(monthlyRevenue, 'IDR') }}</span>
                                </div>
                                <div class="flex justify-between text-xs sm:text-sm">
                                    <span>Pengeluaran:</span>
                                    <span class="font-medium">{{ formatPrice(totalMonthly, 'IDR') }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Yearly Profit Card -->
                <Card :class="[
                    'relative overflow-hidden border-0 text-white',
                    yearlyProfit >= 0
                        ? 'bg-gradient-to-r from-blue-500 to-blue-600'
                        : 'bg-gradient-to-r from-red-500 to-red-600'
                ]">
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div class="space-y-2 flex-1 min-w-0">
                                <p :class="yearlyProfit >= 0 ? 'text-blue-100' : 'text-red-100'" class="text-xs sm:text-sm font-medium">
                                    {{ yearlyProfit >= 0 ? 'Keuntungan Tahun Ini' : 'Kerugian Tahun Ini' }}
                                </p>
                                <div class="text-xl sm:text-2xl lg:text-3xl font-bold truncate">
                                    {{ formatPrice(Math.abs(yearlyProfit), 'IDR') }}
                                </div>
                                <div :class="yearlyProfit >= 0 ? 'text-blue-100' : 'text-red-100'" class="flex items-center space-x-2 text-xs sm:text-sm">
                                    <Clock class="h-3 w-3 sm:h-4 sm:w-4 flex-shrink-0" />
                                    <span class="truncate">{{ props.revenueData?.currentYear || '2025' }}</span>
                                </div>
                            </div>
                            <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-white/20 flex items-center justify-center ml-3 flex-shrink-0">
                                <Clock class="h-5 w-5 sm:h-6 sm:w-6" />
                            </div>
                        </div>
                        <div :class="yearlyProfit >= 0 ? 'border-blue-400/30' : 'border-red-400/30'" class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t">
                            <div :class="yearlyProfit >= 0 ? 'text-blue-100' : 'text-red-100'" class="space-y-1">
                                <div class="flex justify-between text-xs sm:text-sm">
                                    <span>Pemasukan:</span>
                                    <span class="font-medium">{{ formatPrice(yearlyRevenue, 'IDR') }}</span>
                                </div>
                                <div class="flex justify-between text-xs sm:text-sm">
                                    <span>Pengeluaran:</span>
                                    <span class="font-medium">{{ formatPrice(totalYearly, 'IDR') }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Monthly Expenses -->
                <Card class="hover:shadow-lg transition-shadow">
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div class="space-y-2 flex-1 min-w-0">
                                <p class="text-muted-foreground text-xs sm:text-sm font-medium">Pengeluaran Bulanan</p>
                                <div class="text-xl sm:text-2xl font-bold text-red-600 truncate">
                                    {{ formatPrice(totalMonthly, 'IDR') }}
                                </div>
                                <div class="flex items-center space-x-2 text-muted-foreground text-xs sm:text-sm">
                                    <CheckCircle class="h-3 w-3 sm:h-4 sm:w-4 flex-shrink-0" />
                                    <span class="truncate">{{ props.monthlyExpenses.filter(e => e.status === 'active').length }} layanan aktif</span>
                                </div>
                            </div>
                            <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-red-100 flex items-center justify-center ml-3 flex-shrink-0">
                                <Repeat class="h-5 w-5 sm:h-6 sm:w-6 text-red-600" />
                            </div>
                        </div>
                        <div class="mt-3 sm:mt-4">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full" :style="{ width: '75%' }"></div>
                            </div>
                            <p class="text-xs text-muted-foreground mt-2">Berulang setiap bulan</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Yearly Expenses -->
                <Card class="hover:shadow-lg transition-shadow">
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div class="space-y-2 flex-1 min-w-0">
                                <p class="text-muted-foreground text-xs sm:text-sm font-medium">Pengeluaran Tahunan</p>
                                <div class="text-xl sm:text-2xl font-bold text-orange-600 truncate">
                                    {{ formatPrice(totalYearly, 'IDR') }}
                                </div>
                                <div class="flex items-center space-x-2 text-muted-foreground text-xs sm:text-sm">
                                    <CheckCircle class="h-3 w-3 sm:h-4 sm:w-4 flex-shrink-0" />
                                    <span class="truncate">{{ props.yearlyExpenses.filter(e => e.status === 'active').length }} layanan aktif</span>
                                </div>
                            </div>
                            <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-orange-100 flex items-center justify-center ml-3 flex-shrink-0">
                                <Clock class="h-5 w-5 sm:h-6 sm:w-6 text-orange-600" />
                            </div>
                        </div>
                        <div class="mt-3 sm:mt-4">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-orange-500 h-2 rounded-full" :style="{ width: '60%' }"></div>
                            </div>
                            <p class="text-xs text-muted-foreground mt-2">Pembayaran tahunan</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2 px-4 sm:px-6">
                        <CardTitle class="text-xs sm:text-sm font-medium">Tahun {{ currentYear }}</CardTitle>
                        <CheckCircle class="h-3 w-3 sm:h-4 sm:w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="px-4 sm:px-6">
                        <div class="text-xl sm:text-2xl font-bold truncate">{{ formatPrice(thisYearOneTimeTotal, 'IDR') }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ thisYearOneTime.length }} transaksi sekali bayar
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Financial Health Analysis -->
            <div class="space-y-4 sm:space-y-6">
                <div class="flex items-center space-x-3">
                    <div class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-lg bg-gradient-to-r from-purple-500 to-blue-500 text-white">
                        <Activity class="h-4 w-4 sm:h-5 sm:w-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Analisis Kesehatan Keuangan</h2>
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 hidden sm:block">Evaluasi kondisi finansial bisnis secara real-time</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Overall Health Status -->
                    <Card :class="[
                        'relative overflow-hidden border-0',
                        financialHealth.bgColor
                    ]">
                        <CardContent class="p-4 sm:p-6">
                            <div class="flex items-center justify-between">
                                <div class="space-y-2 flex-1 min-w-0">
                                    <p class="text-xs sm:text-sm font-medium opacity-80">Status Kesehatan</p>
                                    <div :class="financialHealth.color" class="text-lg sm:text-xl lg:text-2xl font-bold truncate">
                                        {{ financialHealth.label }}
                                    </div>
                                    <div class="flex items-center space-x-2 text-xs sm:text-sm opacity-75">
                                        <component :is="financialHealth.icon" class="h-3 w-3 sm:h-4 sm:w-4 flex-shrink-0" />
                                        <span class="hidden sm:inline">Evaluasi menyeluruh</span>
                                        <span class="sm:hidden">Evaluasi</span>
                                    </div>
                                </div>
                                <div :class="[financialHealth.color, 'h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-white/20 flex items-center justify-center ml-3 flex-shrink-0']">
                                    <component :is="financialHealth.icon" class="h-5 w-5 sm:h-6 sm:w-6" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Cash Flow Ratio -->
                    <Card>
                        <CardContent class="p-4 sm:p-6">
                            <div class="flex items-center justify-between">
                                <div class="space-y-2 flex-1 min-w-0">
                                    <p class="text-xs sm:text-sm font-medium text-muted-foreground">Rasio Arus Kas</p>
                                    <div class="text-lg sm:text-xl lg:text-2xl font-bold text-blue-600 truncate">
                                        {{ ((monthlyRevenue / totalMonthly) || 0).toFixed(1) }}x
                                    </div>
                                    <div class="flex items-center space-x-2 text-xs sm:text-sm text-muted-foreground">
                                        <Activity class="h-3 w-3 sm:h-4 sm:w-4 flex-shrink-0" />
                                        <span class="hidden sm:inline">Pemasukan vs Pengeluaran</span>
                                        <span class="sm:hidden">Pemasukan/Keluar</span>
                                    </div>
                                </div>
                                <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-blue-100 flex items-center justify-center ml-3 flex-shrink-0">
                                    <Activity class="h-5 w-5 sm:h-6 sm:w-6 text-blue-600" />
                                </div>
                            </div>
                            <div class="mt-3 sm:mt-4">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div
                                        class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                                        :style="{ width: Math.min(((monthlyRevenue / totalMonthly) || 0) * 25, 100) + '%' }"
                                    ></div>
                                </div>
                                <p class="text-xs text-muted-foreground mt-2">
                                    {{ (monthlyRevenue / totalMonthly) >= 1.5 ? 'Sangat baik' : (monthlyRevenue / totalMonthly) >= 1.2 ? 'Baik' : 'Perlu ditingkatkan' }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Burn Rate -->
                    <Card>
                        <CardContent class="p-4 sm:p-6">
                            <div class="flex items-center justify-between">
                                <div class="space-y-2 flex-1 min-w-0">
                                    <p class="text-xs sm:text-sm font-medium text-muted-foreground">Tingkat Pembakaran</p>
                                    <div class="text-lg sm:text-xl lg:text-2xl font-bold text-orange-600 truncate">
                                        {{ formatPrice(burnRate, 'IDR') }}
                                    </div>
                                    <div class="flex items-center space-x-2 text-xs sm:text-sm text-muted-foreground">
                                        <TrendingDown class="h-3 w-3 sm:h-4 sm:w-4 flex-shrink-0" />
                                        <span>Per bulan</span>
                                    </div>
                                </div>
                                <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-orange-100 flex items-center justify-center ml-3 flex-shrink-0">
                                    <TrendingDown class="h-5 w-5 sm:h-6 sm:w-6 text-orange-600" />
                                </div>
                            </div>
                            <div class="mt-3 sm:mt-4">
                                <p class="text-xs text-muted-foreground">
                                    Pengeluaran rutin bulanan
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Financial Runway -->
                    <Card>
                        <CardContent class="p-4 sm:p-6">
                            <div class="flex items-center justify-between">
                                <div class="space-y-2 flex-1 min-w-0">
                                    <p class="text-xs sm:text-sm font-medium text-muted-foreground">Runway Keuangan</p>
                                    <div class="text-lg sm:text-xl lg:text-2xl font-bold text-green-600 truncate">
                                        {{ runwayMonths === Infinity ? '∞' : runwayMonths }} {{ runwayMonths === 1 ? 'bulan' : 'bulan' }}
                                    </div>
                                    <div class="flex items-center space-x-2 text-xs sm:text-sm text-muted-foreground">
                                        <TrendingUp class="h-3 w-3 sm:h-4 sm:w-4 flex-shrink-0" />
                                        <span class="hidden sm:inline">Ketahanan operasional</span>
                                        <span class="sm:hidden">Ketahanan</span>
                                    </div>
                                </div>
                                <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-green-100 flex items-center justify-center ml-3 flex-shrink-0">
                                    <TrendingUp class="h-5 w-5 sm:h-6 sm:w-6 text-green-600" />
                                </div>
                            </div>
                            <div class="mt-3 sm:mt-4">
                                <p class="text-xs text-muted-foreground">
                                    {{ runwayMonths >= 12 ? 'Sangat stabil' : runwayMonths >= 6 ? 'Stabil' : runwayMonths >= 3 ? 'Perlu perhatian' : 'Kritis' }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Health Recommendations -->
                <Card class="border-l-4" :class="[
                    financialHealth.status === 'excellent' ? 'border-l-green-500 bg-green-50 dark:bg-green-950' :
                    financialHealth.status === 'good' ? 'border-l-blue-500 bg-blue-50 dark:bg-blue-950' :
                    financialHealth.status === 'warning' ? 'border-l-yellow-500 bg-yellow-50 dark:bg-yellow-950' :
                    'border-l-red-500 bg-red-50 dark:bg-red-950'
                ]">
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-start space-x-3 sm:space-x-4">
                            <div :class="[
                                'h-8 w-8 sm:h-10 sm:w-10 rounded-full flex items-center justify-center flex-shrink-0',
                                financialHealth.bgColor
                            ]">
                                <component :is="financialHealth.icon" :class="financialHealth.color" class="h-4 w-4 sm:h-5 sm:w-5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 :class="financialHealth.color" class="text-sm sm:text-base font-semibold mb-2">
                                    <span class="hidden sm:inline">Rekomendasi untuk Bisnis {{ financialHealth.label }}</span>
                                    <span class="sm:hidden">Rekomendasi {{ financialHealth.label }}</span>
                                </h3>
                                <div class="space-y-2 text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                    <p v-if="financialHealth.status === 'excellent'">
                                        Kondisi keuangan sangat baik! Pertimbangkan untuk mengalokasikan lebih banyak dana untuk investasi dan ekspansi bisnis.
                                    </p>
                                    <p v-else-if="financialHealth.status === 'good'">
                                        Kondisi keuangan sehat. Terus pertahankan keseimbangan ini dan pantau pengeluaran secara berkala.
                                    </p>
                                    <p v-else-if="financialHealth.status === 'warning'">
                                        Perlu perhatian lebih pada pengelolaan kas. Evaluasi pengeluaran yang tidak esensial dan tingkatkan sumber pemasukan.
                                    </p>
                                    <p v-else>
                                        Kondisi keuangan memerlukan tindakan segera. Fokus pada efisiensi operasional dan diversifikasi sumber pendapatan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Custom Tabs for Different Types -->
            <div class="w-full">
                <!-- Tab Navigation -->
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-1 mb-6 sm:mb-8">
                    <div class="flex flex-col sm:flex-row space-y-1 sm:space-y-0 sm:space-x-1">
                        <button
                            @click="activeTab = 'monthly'"
                            :class="[
                                'flex items-center justify-between sm:justify-center px-3 sm:px-4 py-2.5 rounded-lg font-medium text-sm transition-all duration-200 w-full sm:w-auto',
                                activeTab === 'monthly'
                                    ? 'bg-white dark:bg-gray-800 text-red-600 shadow-sm'
                                    : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-800/50'
                            ]"
                        >
                            <div class="flex items-center">
                                <Repeat class="h-4 w-4 mr-2" />
                                <span class="sm:hidden">Bulanan</span>
                                <span class="hidden sm:inline">Pengeluaran Bulanan</span>
                            </div>
                            <Badge class="ml-2 text-xs" :class="activeTab === 'monthly' ? 'bg-red-100 text-red-700' : 'bg-gray-200 text-gray-600'">
                                {{ props.monthlyExpenses.filter(e => e.status === 'active').length }}
                            </Badge>
                        </button>
                        <button
                            @click="activeTab = 'yearly'"
                            :class="[
                                'flex items-center justify-between sm:justify-center px-3 sm:px-4 py-2.5 rounded-lg font-medium text-sm transition-all duration-200 w-full sm:w-auto',
                                activeTab === 'yearly'
                                    ? 'bg-white dark:bg-gray-800 text-orange-600 shadow-sm'
                                    : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-800/50'
                            ]"
                        >
                            <div class="flex items-center">
                                <Clock class="h-4 w-4 mr-2" />
                                <span class="sm:hidden">Tahunan</span>
                                <span class="hidden sm:inline">Pengeluaran Tahunan</span>
                            </div>
                            <Badge class="ml-2 text-xs" :class="activeTab === 'yearly' ? 'bg-orange-100 text-orange-700' : 'bg-gray-200 text-gray-600'">
                                {{ props.yearlyExpenses.filter(e => e.status === 'active').length }}
                            </Badge>
                        </button>
                        <button
                            @click="activeTab = 'one-time'"
                            :class="[
                                'flex items-center justify-between sm:justify-center px-3 sm:px-4 py-2.5 rounded-lg font-medium text-sm transition-all duration-200 w-full sm:w-auto',
                                activeTab === 'one-time'
                                    ? 'bg-white dark:bg-gray-800 text-blue-600 shadow-sm'
                                    : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-800/50'
                            ]"
                        >
                            <div class="flex items-center">
                                <CreditCard class="h-4 w-4 mr-2" />
                                <span class="sm:hidden">Sekali Bayar</span>
                                <span class="hidden sm:inline">Sekali Bayar</span>
                            </div>
                            <Badge class="ml-2 text-xs" :class="activeTab === 'one-time' ? 'bg-blue-100 text-blue-700' : 'bg-gray-200 text-gray-600'">
                                {{ props.oneTimeExpenses.length }}
                            </Badge>
                        </button>
                    </div>
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