<script setup lang="ts">
import { computed } from 'vue';

interface ChartDataPoint {
    date: string;
    day: number;
    orders: number;
}

interface Props {
    data: ChartDataPoint[];
    height?: number;
}

const props = withDefaults(defineProps<Props>(), {
    height: 200,
});

const chartData = computed(() => {
    const maxOrders = Math.max(...props.data.map((d) => d.orders), 1);
    const chartWidth = 600;
    const chartHeight = props.height - 40; // Account for padding

    return props.data.map((point, index) => {
        const x = (index / (props.data.length - 1)) * chartWidth;
        const y = chartHeight - (point.orders / maxOrders) * chartHeight;

        return {
            ...point,
            x,
            y,
            barHeight: (point.orders / maxOrders) * chartHeight,
        };
    });
});

const pathD = computed(() => {
    if (chartData.value.length === 0) return '';

    const points = chartData.value.map((d) => `${d.x},${d.y}`).join(' L');
    return `M${points}`;
});

const maxOrders = computed(() => Math.max(...props.data.map((d) => d.orders), 1));
</script>

<template>
    <div class="w-full">
        <div class="relative">
            <svg :height="height" viewBox="0 0 600 200" class="w-full" preserveAspectRatio="xMidYMid meet">
                <!-- Grid lines -->
                <defs>
                    <pattern id="grid" width="30" height="20" patternUnits="userSpaceOnUse">
                        <path d="M 30 0 L 0 0 0 20" fill="none" stroke="currentColor" stroke-width="0.5" class="text-border opacity-20" />
                    </pattern>
                </defs>
                <rect width="600" height="160" fill="url(#grid)" />

                <!-- Area under curve -->
                <path
                    v-if="chartData.length > 1"
                    :d="`${pathD} L${chartData[chartData.length - 1].x},160 L${chartData[0].x},160 Z`"
                    fill="url(#gradient)"
                    class="opacity-20"
                />

                <!-- Line chart -->
                <path v-if="chartData.length > 1" :d="pathD" fill="none" stroke="hsl(var(--primary))" stroke-width="2" class="drop-shadow-sm" />

                <!-- Data points -->
                <circle
                    v-for="point in chartData"
                    :key="point.date"
                    :cx="point.x"
                    :cy="point.y"
                    r="3"
                    fill="hsl(var(--primary))"
                    class="hover:r-4 cursor-pointer drop-shadow-sm transition-all"
                >
                    <title>Day {{ point.day }}: {{ point.orders }} orders</title>
                </circle>

                <!-- Gradient definition -->
                <defs>
                    <linearGradient id="gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="hsl(var(--primary))" stop-opacity="0.3" />
                        <stop offset="100%" stop-color="hsl(var(--primary))" stop-opacity="0.05" />
                    </linearGradient>
                </defs>

                <!-- Y-axis labels -->
                <text x="5" y="15" class="fill-muted-foreground text-xs font-medium">
                    {{ maxOrders }}
                </text>
                <text x="5" y="85" class="fill-muted-foreground text-xs font-medium">
                    {{ Math.round(maxOrders / 2) }}
                </text>
                <text x="5" y="155" class="fill-muted-foreground text-xs font-medium">0</text>
            </svg>

            <!-- X-axis labels -->
            <div class="mt-2 flex justify-between px-2">
                <span class="text-xs text-muted-foreground">1</span>
                <span class="text-xs text-muted-foreground">{{ Math.round(data.length / 2) }}</span>
                <span class="text-xs text-muted-foreground">{{ data.length }}</span>
            </div>
        </div>
    </div>
</template>
