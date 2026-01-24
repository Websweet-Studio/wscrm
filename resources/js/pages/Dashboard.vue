<script setup lang="ts">
import MonthlyStatsStrips from '@/components/MonthlyStatsStrips.vue';
import OrderChart from '@/components/OrderChart.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { BarChart3, Calendar, DollarSign, ShoppingCart, TrendingDown, TrendingUp, Users, CheckCircle2, Clock, ArrowRight, RefreshCw, CheckSquare, ListTodo, UserPlus, CreditCard } from 'lucide-vue-next';
import { computed } from 'vue';

interface Stats {
    customers: {
        total: number;
        active: number;
        newThisMonth: number;
        growth: number;
    };
    orders: {
        total: number;
        completed: number;
        thisMonth: number;
        growth: number;
    };
    revenue: {
        total: number;
        thisMonth: number;
        growth: number;
    };
    tasks: {
        pendingCount: number;
    };
}

interface Task {
    id: number;
    title: string;
    status: 'todo' | 'in_progress' | 'done' | 'cancelled';
    priority: 'low' | 'medium' | 'high';
    due_date?: string;
    category?: {
        name: string;
        color: string;
    };
}

interface Customer {
    id: number;
    name: string;
    email: string;
    created_at: string;
}

interface OrderItem {
    item_type: string;
    domain_name?: string;
}

interface Order {
    id: number;
    total_amount: number;
    discount_amount?: number;
    status: string;
    created_at: string;
    expires_at?: string;
    domain_name?: string;
    billing_cycle?: string;
    customer: Customer;
    order_items: OrderItem[];
}

interface ChartDataPoint {
    date: string;
    day: number;
    orders: number;
}

interface MonthlyStats {
    month: string;
    month_short: string;
    orders: number;
    revenue: number;
    customers: number;
}

interface Props {
    stats: Stats;
    recentActivities: {
        orders: Order[];
        customers: Customer[];
    };
    expiringServices: Order[];
    activeServices: Order[];
    chartData: {
        dailyOrders: ChartDataPoint[];
        monthlyStats: MonthlyStats[];
    };
    myPendingTasks: Task[];
}

const props = defineProps<Props>();
const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const greeting = computed(() => {
    const hour = new Date().getHours();
    const user = page.props.auth.user;
    const name = user.name.split(' ')[0]; // First name

    if (hour < 12) return `Good Morning, ${name}`;
    if (hour < 18) return `Good Afternoon, ${name}`;
    return `Good Evening, ${name}`;
});

const refreshDashboard = () => {
    router.reload({ only: ['stats', 'recentActivities', 'expiringServices', 'activeServices', 'chartData', 'myPendingTasks'] });
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatGrowth = (growth: number) => {
    const isPositive = growth >= 0;
    return {
        value: Math.abs(growth),
        isPositive,
        color: isPositive ? 'text-green-600' : 'text-red-600',
        icon: isPositive ? TrendingUp : TrendingDown,
    };
};

const getDaysUntilExpiry = (expiresAt: string) => {
    const now = new Date();
    const expiry = new Date(expiresAt);
    const diffTime = expiry.getTime() - now.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};

const getExpiryBadgeClass = (daysLeft: number) => {
    if (daysLeft <= 15) {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
    } else if (daysLeft <= 30) {
        return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
    } else {
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 sm:space-y-6">
            <!-- Welcome Header -->
            <div class="flex flex-col space-y-2 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Dashboard</h1>
                    <p class="hidden text-sm text-muted-foreground sm:block sm:text-base">
                        {{ greeting }}! Here's what's happening today.
                    </p>
                    <p class="text-sm text-muted-foreground sm:hidden">{{ greeting }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" @click="refreshDashboard">
                        <RefreshCw class="mr-2 h-4 w-4" />
                        Refresh
                    </Button>
                </div>
            </div>

            <!-- Quick Actions -->
            <Card>
                <CardHeader class="px-4 sm:px-6">
                    <CardTitle class="text-base sm:text-lg">Quick Actions</CardTitle>
                    <CardDescription class="text-xs sm:text-sm">Tugas admin umum</CardDescription>
                </CardHeader>
                <CardContent class="px-4 sm:px-6">
                    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                        <Link href="/admin/customers/create" class="group flex flex-col items-center justify-center rounded-lg border border-muted bg-transparent p-4 transition-colors hover:bg-muted/50 hover:text-primary">
                            <UserPlus class="mb-2 h-6 w-6 text-muted-foreground group-hover:text-primary" />
                            <span class="text-xs font-medium sm:text-sm">Add Customer</span>
                        </Link>
                        <Link href="/admin/orders/create" class="group flex flex-col items-center justify-center rounded-lg border border-muted bg-transparent p-4 transition-colors hover:bg-muted/50 hover:text-primary">
                            <ShoppingCart class="mb-2 h-6 w-6 text-muted-foreground group-hover:text-primary" />
                            <span class="text-xs font-medium sm:text-sm">New Order</span>
                        </Link>
                        <Link href="/admin/tasks/create" class="group flex flex-col items-center justify-center rounded-lg border border-muted bg-transparent p-4 transition-colors hover:bg-muted/50 hover:text-primary">
                            <CheckSquare class="mb-2 h-6 w-6 text-muted-foreground group-hover:text-primary" />
                            <span class="text-xs font-medium sm:text-sm">New Task</span>
                        </Link>
                        <Link href="/admin/invoices/create" class="group flex flex-col items-center justify-center rounded-lg border border-muted bg-transparent p-4 transition-colors hover:bg-muted/50 hover:text-primary">
                            <CreditCard class="mb-2 h-6 w-6 text-muted-foreground group-hover:text-primary" />
                            <span class="text-xs font-medium sm:text-sm">New Invoice</span>
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <!-- Key Metrics Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
                <!-- Customers Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 px-4 pb-2 sm:px-6">
                        <CardTitle class="text-xs font-medium sm:text-sm">Total Customers</CardTitle>
                        <Users class="h-3 w-3 text-muted-foreground sm:h-4 sm:w-4" />
                    </CardHeader>
                    <CardContent class="px-4 sm:px-6">
                        <div class="truncate text-xl font-bold sm:text-2xl">{{ stats.customers.total.toLocaleString() }}</div>
                        <div class="flex items-center space-x-2 text-xs">
                            <component
                                :is="formatGrowth(stats.customers.growth).icon"
                                :class="`h-3 w-3 ${formatGrowth(stats.customers.growth).color}`"
                            />
                            <span :class="formatGrowth(stats.customers.growth).color" class="truncate">
                                {{ formatGrowth(stats.customers.growth).value }}%<span class="hidden sm:inline"> from last month</span>
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            <span class="hidden sm:inline"
                                >{{ stats.customers.active }} active • {{ stats.customers.newThisMonth }} new this month</span
                            >
                            <span class="sm:hidden">{{ stats.customers.active }} aktif • {{ stats.customers.newThisMonth }} baru</span>
                        </p>
                    </CardContent>
                </Card>

                <!-- Orders Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 px-4 pb-2 sm:px-6">
                        <CardTitle class="text-xs font-medium sm:text-sm">Total Orders</CardTitle>
                        <ShoppingCart class="h-3 w-3 text-muted-foreground sm:h-4 sm:w-4" />
                    </CardHeader>
                    <CardContent class="px-4 sm:px-6">
                        <div class="truncate text-xl font-bold sm:text-2xl">{{ stats.orders.total.toLocaleString() }}</div>
                        <div class="flex items-center space-x-2 text-xs">
                            <component :is="formatGrowth(stats.orders.growth).icon" :class="`h-3 w-3 ${formatGrowth(stats.orders.growth).color}`" />
                            <span :class="formatGrowth(stats.orders.growth).color" class="truncate">
                                {{ formatGrowth(stats.orders.growth).value }}%<span class="hidden sm:inline"> from last month</span>
                            </span>
                        </div>
                        <div class="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                            <span class="inline-flex items-center gap-1 rounded bg-green-100 px-2 py-0.5 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                <CheckCircle2 class="h-3 w-3" /> {{ stats.orders.completed }} completed
                            </span>
                            <span class="inline-flex items-center gap-1 rounded bg-blue-100 px-2 py-0.5 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                <Calendar class="h-3 w-3" /> {{ stats.orders.thisMonth }} this month
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Revenue Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 px-4 pb-2 sm:px-6">
                        <CardTitle class="text-xs font-medium sm:text-sm">Total Revenue</CardTitle>
                        <DollarSign class="h-3 w-3 text-muted-foreground sm:h-4 sm:w-4" />
                    </CardHeader>
                    <CardContent class="px-4 sm:px-6">
                        <div class="truncate text-lg font-bold sm:text-xl lg:text-2xl">{{ formatPrice(stats.revenue.total) }}</div>
                        <div class="flex items-center space-x-2 text-xs">
                            <component :is="formatGrowth(stats.revenue.growth).icon" :class="`h-3 w-3 ${formatGrowth(stats.revenue.growth).color}`" />
                            <span :class="formatGrowth(stats.revenue.growth).color" class="truncate">
                                {{ formatGrowth(stats.revenue.growth).value }}%<span class="hidden sm:inline"> from last month</span>
                            </span>
                        </div>
                        <p class="mt-1 truncate text-xs text-muted-foreground">
                            <span class="hidden sm:inline">{{ formatPrice(stats.revenue.thisMonth) }} this month</span>
                            <span class="sm:hidden">{{ formatPrice(stats.revenue.thisMonth) }} bulan ini</span>
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- My Tasks Section -->
            <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3">
                 <Card class="lg:col-span-2">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 px-4 pb-2 sm:px-6">
                        <div class="flex items-center gap-2">
                            <ListTodo class="h-4 w-4 text-muted-foreground sm:h-5 sm:w-5" />
                             <CardTitle class="text-base sm:text-lg">Tugas Saya Pending</CardTitle>
                        </div>
                        <Button variant="ghost" size="sm" asChild class="text-xs">
                             <Link href="/admin/tasks">Lihat Semua</Link>
                        </Button>
                    </CardHeader>
                    <CardContent class="px-4 sm:px-6">
                         <div v-if="props.myPendingTasks.length === 0" class="py-6 text-center text-sm text-muted-foreground">
                             Tidak ada tugas pending. Kerja bagus!
                         </div>
                         <div v-else class="space-y-2">
                             <div v-for="task in props.myPendingTasks" :key="task.id" class="flex items-center justify-between rounded-md border p-3 hover:bg-muted/50 transition-colors">
                                 <div class="flex items-center gap-3">
                                     <div :class="{
                                         'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': task.priority === 'high',
                                         'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400': task.priority === 'medium',
                                         'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': task.priority === 'low'
                                     }" class="flex h-8 w-8 items-center justify-center rounded-full shrink-0">
                                         <Clock class="h-4 w-4" />
                                     </div>
                                     <div class="min-w-0">
                                         <div class="flex items-center gap-2">
                                            <div class="font-medium text-sm truncate">{{ task.title }}</div>
                                            <span v-if="task.category" 
                                                  class="rounded px-1.5 py-0.5 text-[10px] whitespace-nowrap"
                                                  :style="{ backgroundColor: task.category.color || '#e9d5ff', color: task.category.color ? '#fff' : '#6b21a8' }"
                                            >
                                              {{ task.category.name }}
                                            </span>
                                         </div>
                                         <div class="text-xs text-muted-foreground" v-if="task.due_date">Jatuh Tempo: {{ formatDate(task.due_date) }}</div>
                                     </div>
                                 </div>
                                 <Link :href="`/admin/tasks?edit=${task.id}`" class="shrink-0">
                                     <Button variant="ghost" size="icon" class="h-8 w-8">
                                         <ArrowRight class="h-4 w-4" />
                                     </Button>
                                 </Link>
                             </div>
                         </div>
                    </CardContent>
                 </Card>

                 <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 px-4 pb-2 sm:px-6">
                        <CardTitle class="text-xs font-medium sm:text-sm">Tugas Pending</CardTitle>
                        <CheckSquare class="h-3 w-3 text-muted-foreground sm:h-4 sm:w-4" />
                    </CardHeader>
                    <CardContent class="px-4 sm:px-6">
                        <div class="truncate text-xl font-bold sm:text-2xl">{{ stats.tasks.pendingCount }}</div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Tugas yang diberikan kepada Anda yang belum selesai.
                        </p>
                        <div class="mt-4">
                             <Button class="w-full" size="sm" asChild>
                                 <Link href="/admin/tasks/create">Buat Tugas Baru</Link>
                             </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-2">
                <!-- Daily Orders Chart -->
                <Card>
                    <CardHeader class="px-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2 text-base sm:text-lg">
                                    <BarChart3 class="h-4 w-4 sm:h-5 sm:w-5" />
                                    <span class="hidden sm:inline">Orders This Month</span>
                                    <span class="sm:hidden">Orders Bulan Ini</span>
                                </CardTitle>
                                <CardDescription class="text-xs sm:text-sm">
                                    <span class="hidden sm:inline">Daily order trends for current month</span>
                                    <span class="sm:hidden">Trend harian order</span>
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="px-4 sm:px-6">
                        <OrderChart :data="chartData.dailyOrders" :height="180" />
                    </CardContent>
                </Card>

                <!-- Monthly Overview -->
                <Card>
                    <CardHeader class="px-4 sm:px-6">
                        <CardTitle class="flex items-center gap-2 text-base sm:text-lg">
                            <Calendar class="h-4 w-4 sm:h-5 sm:w-5" />
                            <span class="hidden sm:inline">Monthly Overview</span>
                            <span class="sm:hidden">Overview Bulanan</span>
                        </CardTitle>
                        <CardDescription class="text-xs sm:text-sm">
                            <span class="hidden sm:inline">Statistics for the last 6 months</span>
                            <span class="sm:hidden">Statistik 6 bulan terakhir</span>
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-4 sm:px-6">
                        <MonthlyStatsStrips :data="chartData.monthlyStats" />
                    </CardContent>
                </Card>
            </div>

            <!-- Services Section -->
            <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader class="px-4 sm:px-6">
                        <CardTitle class="flex items-center gap-2 text-base sm:text-lg">
                            <CheckCircle2 class="h-4 w-4 sm:h-5 sm:w-5" />
                            Layanan Aktif
                        </CardTitle>
                        <CardDescription class="text-xs sm:text-sm">
                            Diurutkan dari yang akan kadaluarsa
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-4 sm:px-6">
                        <div v-if="activeServices.length === 0" class="py-4 text-center text-xs text-muted-foreground sm:text-sm">
                            Tidak ada layanan aktif.
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="service in activeServices"
                                :key="service.id"
                                class="flex items-center justify-between rounded-md bg-muted/30 p-3"
                            >
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-xs font-medium sm:text-sm">{{ service.domain_name || `Service #${service.id}` }}</div>
                                    <div class="truncate text-xs text-muted-foreground">{{ service.customer.name }}</div>
                                </div>
                                <div class="ml-3 flex-shrink-0 text-right">
                                    <span
                                        v-if="service.expires_at"
                                        class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                        :class="getExpiryBadgeClass(getDaysUntilExpiry(service.expires_at))"
                                    >
                                        {{ getDaysUntilExpiry(service.expires_at) }} hari lagi
                                    </span>
                                    <div class="mt-1 text-xs text-muted-foreground" v-if="service.expires_at">{{ formatDate(service.expires_at!) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <Button variant="outline" size="sm" asChild class="w-full text-xs sm:text-sm">
                                <Link href="/admin/orders?view=services&status=active">Kelola Layanan</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

            </div>

            <!-- Recent Activities Grid -->
            <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-2">
                <!-- Recent Orders -->
                <Card>
                    <CardHeader class="px-4 sm:px-6">
                        <CardTitle class="text-base sm:text-lg">Recent Orders</CardTitle>
                        <CardDescription class="text-xs sm:text-sm">Latest customer orders</CardDescription>
                    </CardHeader>
                    <CardContent class="px-4 sm:px-6">
                        <div v-if="recentActivities.orders.length === 0" class="py-4 text-center text-xs text-muted-foreground sm:text-sm">
                            No recent orders
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="order in recentActivities.orders"
                                :key="order.id"
                                class="flex items-center justify-between rounded-md bg-muted/30 p-3"
                            >
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-xs font-medium sm:text-sm">Order #{{ order.id }}</div>
                                    <div class="truncate text-xs text-muted-foreground">{{ order.customer.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ formatDate(order.created_at) }}</div>
                                </div>
                                <div class="ml-3 flex-shrink-0 text-right">
                                    <template v-if="order.discount_amount && order.discount_amount > 0">
                                        <div class="text-xs text-muted-foreground line-through">
                                            {{ formatPrice(order.total_amount) }}
                                        </div>
                                        <div class="text-xs font-bold text-green-600 sm:text-sm dark:text-green-400">
                                            {{ formatPrice(Number(order.total_amount) - Number(order.discount_amount)) }}
                                        </div>
                                        <div class="text-xs text-green-600 dark:text-green-400">Hemat: {{ formatPrice(order.discount_amount) }}</div>
                                    </template>
                                    <template v-else>
                                        <div class="text-xs font-bold sm:text-sm">{{ formatPrice(order.total_amount) }}</div>
                                    </template>
                                    <div class="text-xs text-muted-foreground capitalize">{{ order.status }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <Button variant="outline" size="sm" asChild class="w-full text-xs sm:text-sm">
                                <Link href="/admin/orders">View All Orders</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Customers -->
                <Card>
                    <CardHeader class="px-4 sm:px-6">
                        <CardTitle class="text-base sm:text-lg">New Customers</CardTitle>
                        <CardDescription class="text-xs sm:text-sm">Recently registered customers</CardDescription>
                    </CardHeader>
                    <CardContent class="px-4 sm:px-6">
                        <div v-if="recentActivities.customers.length === 0" class="py-4 text-center text-xs text-muted-foreground sm:text-sm">
                            No new customers
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="customer in recentActivities.customers"
                                :key="customer.id"
                                class="flex items-center justify-between rounded-md bg-muted/30 p-3"
                            >
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-xs font-medium sm:text-sm">{{ customer.name }}</div>
                                    <div class="truncate text-xs text-muted-foreground">{{ customer.email }}</div>
                                </div>
                                <div class="ml-3 flex-shrink-0 text-right">
                                    <div class="text-xs text-muted-foreground">{{ formatDate(customer.created_at) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <Button variant="outline" size="sm" asChild class="w-full text-xs sm:text-sm">
                                <Link href="/admin/customers">View All Customers</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <Card>
                <CardHeader class="px-4 sm:px-6">
                    <CardTitle class="text-base sm:text-lg">Quick Actions</CardTitle>
                    <CardDescription class="text-xs sm:text-sm">Tugas admin umum</CardDescription>
                </CardHeader>
                <CardContent class="px-4 sm:px-6">
                    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                        <Link href="/admin/customers/create" class="group flex flex-col items-center justify-center rounded-lg border border-muted bg-transparent p-4 transition-colors hover:bg-muted/50 hover:text-primary">
                            <UserPlus class="mb-2 h-6 w-6 text-muted-foreground group-hover:text-primary" />
                            <span class="text-xs font-medium sm:text-sm">Add Customer</span>
                        </Link>
                        <Link href="/admin/orders/create" class="group flex flex-col items-center justify-center rounded-lg border border-muted bg-transparent p-4 transition-colors hover:bg-muted/50 hover:text-primary">
                            <ShoppingCart class="mb-2 h-6 w-6 text-muted-foreground group-hover:text-primary" />
                            <span class="text-xs font-medium sm:text-sm">New Order</span>
                        </Link>
                        <Link href="/admin/tasks/create" class="group flex flex-col items-center justify-center rounded-lg border border-muted bg-transparent p-4 transition-colors hover:bg-muted/50 hover:text-primary">
                            <CheckSquare class="mb-2 h-6 w-6 text-muted-foreground group-hover:text-primary" />
                            <span class="text-xs font-medium sm:text-sm">New Task</span>
                        </Link>
                        <Link href="/admin/invoices/create" class="group flex flex-col items-center justify-center rounded-lg border border-muted bg-transparent p-4 transition-colors hover:bg-muted/50 hover:text-primary">
                            <CreditCard class="mb-2 h-6 w-6 text-muted-foreground group-hover:text-primary" />
                            <span class="text-xs font-medium sm:text-sm">New Invoice</span>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
