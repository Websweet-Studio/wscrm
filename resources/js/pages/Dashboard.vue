<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    ArrowUpRight,
    BarChart3,
    Calendar,
    CheckCircle2,
    Clock,
    DollarSign,
    Download,
    ListTodo,
    RefreshCw,
    ShoppingCart,
    TrendingDown,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, LineElement, PointElement, LineController, Title, Tooltip, Legend, Filler } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, LineController, Title, Tooltip, Legend, Filler);

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
    const name = user.name.split(' ')[0];

    if (hour < 12) return `Selamat Pagi, ${name}`;
    if (hour < 18) return `Selamat Siang, ${name}`;
    return `Selamat Malam, ${name}`;
});

const today = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

const refreshDashboard = () => {
    router.reload({ only: ['stats', 'recentActivities', 'expiringServices', 'chartData', 'myPendingTasks'] });
};

const githubRepoUrl = 'https://github.com/Websweet-Studio/wscrm/';
const githubReleasesUrl = 'https://github.com/Websweet-Studio/wscrm/releases/';
const updateInfo = ref<any | null>(null);
const isCheckingUpdate = ref(false);
const updateCheckError = ref<string>('');

const checkForUpdates = async () => {
    isCheckingUpdate.value = true;
    updateCheckError.value = '';
    try {
        const response = await fetch('/admin/system/check-updates');
        const data = await response.json();
        if (response.ok) {
            updateInfo.value = data;
        } else {
            updateCheckError.value = data?.error || 'Gagal mengecek update';
        }
    } catch (e: any) {
        updateCheckError.value = e?.message || 'Gagal mengecek update';
    } finally {
        isCheckingUpdate.value = false;
    }
};

onMounted(() => {
    checkForUpdates();
});

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
        color: isPositive ? 'text-emerald-600 dark:text-emerald-400' : 'text-destructive',
        icon: isPositive ? TrendingUp : TrendingDown,
    };
};

const initials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
};

const avatarColors = [
    'bg-blue-500/15 text-blue-600 dark:text-blue-400',
    'bg-violet-500/15 text-violet-600 dark:text-violet-400',
    'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
    'bg-amber-500/15 text-amber-600 dark:text-amber-400',
    'bg-rose-500/15 text-rose-600 dark:text-rose-400',
];

const avatarColor = (id: number) => avatarColors[id % avatarColors.length];

const priorityBadge = (priority: Task['priority']) => {
    switch (priority) {
        case 'high':
            return 'bg-rose-500/15 text-rose-600 dark:text-rose-400';
        case 'medium':
            return 'bg-amber-500/15 text-amber-600 dark:text-amber-400';
        default:
            return 'bg-slate-500/15 text-slate-600 dark:text-slate-400';
    }
};

const dailyChartData = computed(() => ({
    labels: props.chartData.dailyOrders.map(d => d.day.toString()),
    datasets: [{
        label: 'Pesanan',
        data: props.chartData.dailyOrders.map(d => d.orders),
        borderColor: 'hsl(var(--primary))',
        backgroundColor: 'hsla(var(--primary) / 0.12)',
        fill: true,
        tension: 0.4,
        pointRadius: 3,
        pointBackgroundColor: 'hsl(var(--primary))',
        pointBorderColor: 'hsl(var(--card))',
        pointBorderWidth: 2,
    }],
}));

const dailyChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { mode: 'index' as const, intersect: false } },
    scales: {
        x: { grid: { display: false }, ticks: { maxTicksLimit: 10, font: { size: 10 } } },
        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: 'hsl(var(--border) / 0.6)' } },
    },
    interaction: { intersect: false, mode: 'index' as const },
};

const monthlyChartData = computed(() => ({
    labels: props.chartData.monthlyStats.map(m => m.month_short),
    datasets: [
        { label: 'Pesanan', data: props.chartData.monthlyStats.map(m => m.orders), backgroundColor: 'hsl(var(--primary))', borderRadius: 6 },
        { label: 'Pelanggan', data: props.chartData.monthlyStats.map(m => m.customers), backgroundColor: 'hsl(var(--primary) / 0.55)', borderRadius: 6 },
        { label: 'Pendapatan (jt)', data: props.chartData.monthlyStats.map(m => Math.round(m.revenue / 1000000)), backgroundColor: 'hsl(var(--primary) / 0.25)', borderRadius: 6 },
    ],
}));

const monthlyChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'top' as const, labels: { boxWidth: 12, boxHeight: 12, padding: 16, font: { size: 11 } } }, tooltip: { mode: 'index' as const, intersect: false } },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
        y: { beginAtZero: true, ticks: { font: { size: 10 } }, grid: { color: 'hsl(var(--border) / 0.6)' } },
    },
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">{{ greeting }}</h1>
                    <p class="mt-1 text-sm text-muted-foreground">{{ today }}</p>
                </div>
                <Button variant="outline" size="sm" class="w-fit" @click="refreshDashboard">
                    <RefreshCw class="mr-2 h-4 w-4" />
                    Refresh
                </Button>
            </div>

            <!-- Update banner -->
            <Card v-if="updateInfo?.has_update" class="border-emerald-200/60 bg-emerald-50/60 dark:border-emerald-900/50 dark:bg-emerald-950/30">
                <CardHeader class="px-5 pb-3">
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Download class="h-4 w-4 text-emerald-700 dark:text-emerald-400" />
                        Update tersedia
                    </CardTitle>
                    <CardDescription class="text-xs">
                        Versi saat ini <span class="font-mono">{{ updateInfo.current_version }}</span> • Versi terbaru
                        <span class="font-mono font-medium">{{ updateInfo.latest_version }}</span>
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex flex-col gap-2 px-5 pb-5 sm:flex-row sm:items-center sm:justify-between">
                    <a :href="githubReleasesUrl" target="_blank" rel="noreferrer" class="text-xs text-muted-foreground underline underline-offset-4 hover:text-foreground">
                        Lihat rilis di GitHub
                    </a>
                    <Button size="sm" @click="router.visit('/admin/system/update')">
                        Install Sekarang
                        <ArrowRight class="ml-2 h-4 w-4" />
                    </Button>
                </CardContent>
            </Card>

            <!-- Stat cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Card>
                    <CardContent class="flex items-start justify-between p-5">
                        <div class="min-w-0">
                            <p class="text-sm text-muted-foreground">Total Pelanggan</p>
                            <p class="mt-2 text-3xl font-semibold tracking-tight">{{ stats.customers.total.toLocaleString() }}</p>
                            <div class="mt-3 flex items-center gap-1.5 text-xs">
                                <component
                                    :is="formatGrowth(stats.customers.growth).icon"
                                    :class="`h-3.5 w-3.5 ${formatGrowth(stats.customers.growth).color}`"
                                />
                                <span :class="formatGrowth(stats.customers.growth).color" class="font-medium">
                                    {{ formatGrowth(stats.customers.growth).value }}%
                                </span>
                                <span class="text-muted-foreground">dari bulan lalu</span>
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ stats.customers.active }} aktif • {{ stats.customers.newThisMonth }} baru bulan ini
                            </p>
                        </div>
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-500/15 text-blue-600 dark:text-blue-400">
                            <Users class="h-5 w-5" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="flex items-start justify-between p-5">
                        <div class="min-w-0">
                            <p class="text-sm text-muted-foreground">Total Pesanan</p>
                            <p class="mt-2 text-3xl font-semibold tracking-tight">{{ stats.orders.total.toLocaleString() }}</p>
                            <div class="mt-3 flex items-center gap-1.5 text-xs">
                                <component :is="formatGrowth(stats.orders.growth).icon" :class="`h-3.5 w-3.5 ${formatGrowth(stats.orders.growth).color}`" />
                                <span :class="formatGrowth(stats.orders.growth).color" class="font-medium">
                                    {{ formatGrowth(stats.orders.growth).value }}%
                                </span>
                                <span class="text-muted-foreground">dari bulan lalu</span>
                            </div>
                            <div class="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                                <span class="inline-flex items-center gap-1">
                                    <CheckCircle2 class="h-3 w-3 text-emerald-500" /> {{ stats.orders.completed }} selesai
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <Calendar class="h-3 w-3" /> {{ stats.orders.thisMonth }} bulan ini
                                </span>
                            </div>
                        </div>
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-500/15 text-violet-600 dark:text-violet-400">
                            <ShoppingCart class="h-5 w-5" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="sm:col-span-2 lg:col-span-1">
                    <CardContent class="flex items-start justify-between p-5">
                        <div class="min-w-0">
                            <p class="text-sm text-muted-foreground">Total Pendapatan</p>
                            <p class="mt-2 truncate text-3xl font-semibold tracking-tight">{{ formatPrice(stats.revenue.total) }}</p>
                            <div class="mt-3 flex items-center gap-1.5 text-xs">
                                <component :is="formatGrowth(stats.revenue.growth).icon" :class="`h-3.5 w-3.5 ${formatGrowth(stats.revenue.growth).color}`" />
                                <span :class="formatGrowth(stats.revenue.growth).color" class="font-medium">
                                    {{ formatGrowth(stats.revenue.growth).value }}%
                                </span>
                                <span class="text-muted-foreground">dari bulan lalu</span>
                            </div>
                            <p class="mt-1 truncate text-xs text-muted-foreground">{{ formatPrice(stats.revenue.thisMonth) }} bulan ini</p>
                        </div>
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                            <DollarSign class="h-5 w-5" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
                <Card class="lg:col-span-3">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <BarChart3 class="h-4 w-4 text-muted-foreground" />
                            Pesanan Bulan Ini
                        </CardTitle>
                        <CardDescription>Tren pesanan harian untuk bulan ini</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="h-56">
                            <Bar :data="dailyChartData" :options="dailyChartOptions" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <Calendar class="h-4 w-4 text-muted-foreground" />
                            Ringkasan Bulanan
                        </CardTitle>
                        <CardDescription>Statistik 6 bulan terakhir</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="h-56">
                            <Bar :data="monthlyChartData" :options="monthlyChartOptions" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tasks + recent activity -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <!-- My tasks -->
                <Card class="lg:col-span-2">
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <ListTodo class="h-4 w-4 text-muted-foreground" />
                            Tugas Saya
                        </CardTitle>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href="/admin/tasks">Lihat Semua <ArrowUpRight class="ml-1 h-3.5 w-3.5" /></Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div v-if="props.myPendingTasks.length === 0" class="py-8 text-center text-sm text-muted-foreground">
                            Tidak ada tugas pending. Kerja bagus!
                        </div>
                        <div v-else class="space-y-2">
                            <div
                                v-for="task in props.myPendingTasks"
                                :key="task.id"
                                class="flex items-center justify-between gap-3 rounded-lg border p-3 transition-colors hover:bg-muted/50"
                            >
                                <div class="flex min-w-0 items-center gap-3">
                                    <div :class="`flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${priorityBadge(task.priority)}`">
                                        <Clock class="h-4 w-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <div class="truncate text-sm font-medium">{{ task.title }}</div>
                                            <span
                                                v-if="task.category"
                                                class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-medium whitespace-nowrap"
                                                :style="{ backgroundColor: task.category.color || '#e9d5ff', color: task.category.color ? '#fff' : '#6b21a8' }"
                                            >
                                                {{ task.category.name }}
                                            </span>
                                        </div>
                                        <div v-if="task.due_date" class="mt-0.5 text-xs text-muted-foreground">Jatuh tempo: {{ formatDate(task.due_date) }}</div>
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

                <!-- Pending count -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Tugas Pending</CardTitle>
                    </CardHeader>
                    <CardContent class="flex flex-col">
                        <p class="text-4xl font-semibold tracking-tight">{{ stats.tasks.pendingCount }}</p>
                        <p class="mt-2 text-sm text-muted-foreground">Tugas yang diberikan kepada Anda belum selesai.</p>
                        <Button class="mt-6 w-full" size="sm" asChild>
                            <Link href="/admin/tasks/create">Buat Tugas Baru</Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent orders + customers -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle class="text-base">Pesanan Terbaru</CardTitle>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href="/admin/orders">Lihat Semua <ArrowUpRight class="ml-1 h-3.5 w-3.5" /></Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentActivities.orders.length === 0" class="py-8 text-center text-sm text-muted-foreground">
                            Tidak ada pesanan terbaru
                        </div>
                        <div v-else class="space-y-2">
                            <div
                                v-for="order in recentActivities.orders"
                                :key="order.id"
                                class="flex items-center justify-between gap-3 rounded-lg border p-3 transition-colors hover:bg-muted/50"
                            >
                                <div class="flex min-w-0 items-center gap-3">
                                    <Avatar class="h-9 w-9 shrink-0">
                                        <AvatarFallback :class="avatarColor(order.customer.id)">
                                            {{ initials(order.customer.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-medium">Pesanan #{{ order.id }}</div>
                                        <div class="truncate text-xs text-muted-foreground">{{ order.customer.name }}</div>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <div class="text-sm font-semibold">{{ formatPrice(order.total_amount) }}</div>
                                    <div class="text-xs text-muted-foreground">{{ formatDate(order.created_at) }}</div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle class="text-base">Pelanggan Baru</CardTitle>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href="/admin/customers">Lihat Semua <ArrowUpRight class="ml-1 h-3.5 w-3.5" /></Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentActivities.customers.length === 0" class="py-8 text-center text-sm text-muted-foreground">
                            Tidak ada pelanggan baru
                        </div>
                        <div v-else class="space-y-2">
                            <div
                                v-for="customer in recentActivities.customers"
                                :key="customer.id"
                                class="flex items-center justify-between gap-3 rounded-lg border p-3 transition-colors hover:bg-muted/50"
                            >
                                <div class="flex min-w-0 items-center gap-3">
                                    <Avatar class="h-9 w-9 shrink-0">
                                        <AvatarFallback :class="avatarColor(customer.id)">
                                            {{ initials(customer.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-medium">{{ customer.name }}</div>
                                        <div class="truncate text-xs text-muted-foreground">{{ customer.email }}</div>
                                    </div>
                                </div>
                                <div class="shrink-0 text-xs text-muted-foreground">{{ formatDate(customer.created_at) }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
