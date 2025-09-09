<script setup lang="ts">
interface MonthlyStats {
    month: string;
    month_short: string;
    orders: number;
    revenue: number;
    customers: number;
}

interface Props {
    data: MonthlyStats[];
}

const props = defineProps<Props>();

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
};

const formatCompactPrice = (price: number) => {
    if (price >= 1000000) {
        return (price / 1000000).toFixed(1) + 'M';
    }
    if (price >= 1000) {
        return (price / 1000).toFixed(0) + 'K';
    }
    return price.toString();
};
</script>

<template>
    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
        <div
            v-for="(month, index) in data"
            :key="month.month"
            class="relative overflow-hidden rounded-lg border bg-card p-4 transition-shadow hover:shadow-md"
            :class="{
                'border-primary bg-primary/5': index === data.length - 1,
            }"
        >
            <!-- Month header -->
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold" :class="{ 'text-primary': index === data.length - 1 }">
                    {{ month.month }}
                </h3>
                <div v-if="index === data.length - 1" class="rounded-full bg-primary/10 px-2 py-1 text-xs font-medium text-primary">Current</div>
            </div>

            <!-- Stats grid -->
            <div class="space-y-3">
                <!-- Orders -->
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2 text-xs text-muted-foreground">
                        <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                        Orders
                    </span>
                    <span class="text-sm font-bold">{{ month.orders }}</span>
                </div>

                <!-- Revenue -->
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2 text-xs text-muted-foreground">
                        <div class="h-2 w-2 rounded-full bg-green-500"></div>
                        Revenue
                    </span>
                    <span class="text-sm font-bold" :title="formatPrice(month.revenue)">
                        {{ formatCompactPrice(month.revenue) }}
                    </span>
                </div>

                <!-- Customers -->
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2 text-xs text-muted-foreground">
                        <div class="h-2 w-2 rounded-full bg-purple-500"></div>
                        New Customers
                    </span>
                    <span class="text-sm font-bold">{{ month.customers }}</span>
                </div>
            </div>

            <!-- Background decoration -->
            <div
                class="absolute -right-4 -bottom-4 h-16 w-16 rotate-12 transform opacity-5"
                :class="{ 'text-primary': index === data.length - 1, 'text-muted-foreground': index !== data.length - 1 }"
            >
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M7 4V2a1 1 0 00-2 0v2H3a1 1 0 000 2h2v2a1 1 0 002 0V6h2a1 1 0 000-2H7zM17 12V10a1 1 0 00-2 0v2h-2a1 1 0 000 2h2v2a1 1 0 002 0v-2h2a1 1 0 000-2h-2zM7 14v-2a1 1 0 00-2 0v2H3a1 1 0 000 2h2v2a1 1 0 002 0v-2h2a1 1 0 000-2H7z"
                    />
                </svg>
            </div>
        </div>
    </div>
</template>
