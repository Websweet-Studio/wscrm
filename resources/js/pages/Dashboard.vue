<script setup lang="ts">
import MonthlyStatsStrips from '@/components/MonthlyStatsStrips.vue';
import OrderChart from '@/components/OrderChart.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, BarChart3, Calendar, DollarSign, ShoppingCart, TrendingDown, TrendingUp, Users } from 'lucide-vue-next';

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
    chartData: {
        dailyOrders: ChartDataPoint[];
        monthlyStats: MonthlyStats[];
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

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
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Dashboard</h1>
                    <p class="text-sm sm:text-base text-muted-foreground hidden sm:block">Welcome back! Here's what's happening with your dashboard.</p>
                    <p class="text-sm text-muted-foreground sm:hidden">Overview singkat sistem Anda</p>
                </div>
            </div>

            <!-- Key Metrics Cards -->
            <div class="grid gap-4 sm:gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Customers Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2 px-4 sm:px-6">
                        <CardTitle class="text-xs sm:text-sm font-medium">Total Customers</CardTitle>
                        <Users class="h-3 w-3 sm:h-4 sm:w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="px-4 sm:px-6">
                        <div class="text-xl sm:text-2xl font-bold truncate">{{ stats.customers.total.toLocaleString() }}</div>
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
                            <span class="hidden sm:inline">{{ stats.customers.active }} active • {{ stats.customers.newThisMonth }} new this month</span>
                            <span class="sm:hidden">{{ stats.customers.active }} aktif • {{ stats.customers.newThisMonth }} baru</span>
                        </p>
                    </CardContent>
                </Card>

                <!-- Orders Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2 px-4 sm:px-6">
                        <CardTitle class="text-xs sm:text-sm font-medium">Total Orders</CardTitle>
                        <ShoppingCart class="h-3 w-3 sm:h-4 sm:w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="px-4 sm:px-6">
                        <div class="text-xl sm:text-2xl font-bold truncate">{{ stats.orders.total.toLocaleString() }}</div>
                        <div class="flex items-center space-x-2 text-xs">
                            <component :is="formatGrowth(stats.orders.growth).icon" :class="`h-3 w-3 ${formatGrowth(stats.orders.growth).color}`" />
                            <span :class="formatGrowth(stats.orders.growth).color" class="truncate">
                                {{ formatGrowth(stats.orders.growth).value }}%<span class="hidden sm:inline"> from last month</span>
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            <span class="hidden sm:inline">{{ stats.orders.completed }} completed • {{ stats.orders.thisMonth }} this month</span>
                            <span class="sm:hidden">{{ stats.orders.completed }} selesai • {{ stats.orders.thisMonth }} bulan ini</span>
                        </p>
                    </CardContent>
                </Card>

                <!-- Revenue Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2 px-4 sm:px-6">
                        <CardTitle class="text-xs sm:text-sm font-medium">Total Revenue</CardTitle>
                        <DollarSign class="h-3 w-3 sm:h-4 sm:w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="px-4 sm:px-6">
                        <div class="text-lg sm:text-xl lg:text-2xl font-bold truncate">{{ formatPrice(stats.revenue.total) }}</div>
                        <div class="flex items-center space-x-2 text-xs">
                            <component :is="formatGrowth(stats.revenue.growth).icon" :class="`h-3 w-3 ${formatGrowth(stats.revenue.growth).color}`" />
                            <span :class="formatGrowth(stats.revenue.growth).color" class="truncate">
                                {{ formatGrowth(stats.revenue.growth).value }}%<span class="hidden sm:inline"> from last month</span>
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground truncate">
                            <span class="hidden sm:inline">{{ formatPrice(stats.revenue.thisMonth) }} this month</span>
                            <span class="sm:hidden">{{ formatPrice(stats.revenue.thisMonth) }} bulan ini</span>
                        </p>
                    </CardContent>
                </Card>

            </div>

            <!-- Charts Section -->
            <div class="grid gap-4 sm:gap-6 grid-cols-1 lg:grid-cols-2">
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

            <!-- Expiring Services Alert -->
            <Card v-if="expiringServices.length > 0" class="border-orange-200 bg-orange-50 dark:border-orange-800 dark:bg-orange-950">
                <CardHeader class="px-4 sm:px-6">
                    <CardTitle class="flex items-center gap-2 text-base sm:text-lg text-orange-800 dark:text-orange-200">
                        <AlertTriangle class="h-4 w-4 sm:h-5 sm:w-5" />
                        Layanan Akan Berakhir
                    </CardTitle>
                    <CardDescription class="text-xs sm:text-sm text-orange-700 dark:text-orange-300">
                        {{ expiringServices.length }} layanan akan berakhir dalam 1 bulan ke depan
                    </CardDescription>
                </CardHeader>
                <CardContent class="px-4 sm:px-6">
                    <div class="space-y-3">
                        <div
                            v-for="service in expiringServices"
                            :key="service.id"
                            class="flex items-center justify-between rounded-md bg-white/50 p-3 dark:bg-gray-900/50"
                        >
                            <div class="flex-1 min-w-0">
                                <div class="text-xs sm:text-sm font-medium truncate">{{ service.domain_name || `Service #${service.id}` }}</div>
                                <div class="text-xs text-muted-foreground truncate">{{ service.customer.name }}</div>
                            </div>
                            <div class="text-right ml-3 flex-shrink-0">
                                <span
                                    v-if="service.expires_at"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                    :class="getExpiryBadgeClass(getDaysUntilExpiry(service.expires_at))"
                                >
                                    {{ getDaysUntilExpiry(service.expires_at) }} hari lagi
                                </span>
                                <div class="text-xs text-muted-foreground mt-1">{{ formatDate(service.expires_at!) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <Button variant="outline" size="sm" asChild class="w-full text-xs sm:text-sm">
                            <Link href="/admin/orders?view=services&status=active">Lihat Semua Layanan</Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Recent Activities Grid -->
            <div class="grid gap-4 sm:gap-6 grid-cols-1 lg:grid-cols-2">
                <!-- Recent Orders -->
                <Card>
                    <CardHeader class="px-4 sm:px-6">
                        <CardTitle class="text-base sm:text-lg">Recent Orders</CardTitle>
                        <CardDescription class="text-xs sm:text-sm">Latest customer orders</CardDescription>
                    </CardHeader>
                    <CardContent class="px-4 sm:px-6">
                        <div v-if="recentActivities.orders.length === 0" class="py-4 text-center text-muted-foreground text-xs sm:text-sm">No recent orders</div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="order in recentActivities.orders"
                                :key="order.id"
                                class="flex items-center justify-between rounded-md bg-muted/30 p-3"
                            >
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs sm:text-sm font-medium truncate">Order #{{ order.id }}</div>
                                    <div class="text-xs text-muted-foreground truncate">{{ order.customer.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ formatDate(order.created_at) }}</div>
                                </div>
                                <div class="text-right ml-3 flex-shrink-0">
                                    <template v-if="order.discount_amount && order.discount_amount > 0">
                                        <div class="text-xs text-muted-foreground line-through">
                                            {{ formatPrice(order.total_amount) }}
                                        </div>
                                        <div class="text-xs sm:text-sm font-bold text-green-600 dark:text-green-400">
                                            {{ formatPrice(Number(order.total_amount) - Number(order.discount_amount)) }}
                                        </div>
                                        <div class="text-xs text-green-600 dark:text-green-400">
                                            Hemat: {{ formatPrice(order.discount_amount) }}
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div class="text-xs sm:text-sm font-bold">{{ formatPrice(order.total_amount) }}</div>
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
                        <div v-if="recentActivities.customers.length === 0" class="py-4 text-center text-muted-foreground text-xs sm:text-sm">No new customers</div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="customer in recentActivities.customers"
                                :key="customer.id"
                                class="flex items-center justify-between rounded-md bg-muted/30 p-3"
                            >
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs sm:text-sm font-medium truncate">{{ customer.name }}</div>
                                    <div class="text-xs text-muted-foreground truncate">{{ customer.email }}</div>
                                </div>
                                <div class="text-right ml-3 flex-shrink-0">
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
                    <CardDescription class="text-xs sm:text-sm">Common administrative tasks</CardDescription>
                </CardHeader>
                <CardContent class="px-4 sm:px-6">
                    <div class="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                        <Button asChild size="sm" class="text-xs sm:text-sm">
                            <Link href="/admin/customers">Manage Customers</Link>
                        </Button>
                        <Button asChild size="sm" class="text-xs sm:text-sm">
                            <Link href="/admin/orders">View Orders</Link>
                        </Button>
                        <Button asChild size="sm" class="text-xs sm:text-sm">
                            <Link href="/hosting">View Hosting Plans</Link>
                        </Button>
                        <Button asChild size="sm" class="text-xs sm:text-sm">
                            <Link href="/domains">Check Domain Pricing</Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
