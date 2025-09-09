<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle, Cpu, DollarSign, HardDrive, MemoryStick, Server, Settings, Star, Wifi } from 'lucide-vue-next';

interface HostingPlan {
    id: number;
    plan_name: string;
    storage_gb: number;
    cpu_cores: number;
    ram_gb: number;
    bandwidth: string;
    modal_cost: number;
    maintenance_cost: number;
    discount_percent: number;
    selling_price: number;
    features: string[];
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    hostingPlan: HostingPlan;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Hosting Plans', href: '/admin/hosting-plans' },
    { title: props.hostingPlan.plan_name, href: `/admin/hosting-plans/${props.hostingPlan.id}` },
];

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const formatDate = (date: string) => {
    return new Intl.DateTimeFormat('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(date));
};

const calculateProfit = () => {
    const costs = props.hostingPlan.modal_cost + props.hostingPlan.maintenance_cost;
    const discountedPrice = props.hostingPlan.selling_price * (1 - props.hostingPlan.discount_percent / 100);
    return discountedPrice - costs;
};

const calculateProfitMargin = () => {
    const profit = calculateProfit();
    const discountedPrice = props.hostingPlan.selling_price * (1 - props.hostingPlan.discount_percent / 100);
    return discountedPrice > 0 ? (profit / discountedPrice) * 100 : 0;
};
</script>

<template>
    <Head :title="`Hosting Plan - ${hostingPlan.plan_name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link href="/admin/hosting-plans">
                        <Button variant="outline" size="sm">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Hosting Plans
                        </Button>
                    </Link>
                    <div>
                        <h1 class="flex items-center text-3xl font-bold tracking-tight">
                            <Server class="mr-3 h-8 w-8 text-blue-500" />
                            {{ hostingPlan.plan_name }}
                        </h1>
                        <p class="text-muted-foreground">Hosting plan specifications and pricing</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <Badge :variant="hostingPlan.is_active ? 'default' : 'secondary'" class="px-3 py-1 text-sm">
                        {{ hostingPlan.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                    <Link :href="`/admin/hosting-plans/${hostingPlan.id}/edit`">
                        <Button>
                            <Settings class="mr-2 h-4 w-4" />
                            Edit Plan
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Plan Specifications -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Server class="mr-2 h-5 w-5" />
                            Plan Specifications
                        </CardTitle>
                        <CardDescription> Technical specifications for {{ hostingPlan.plan_name }} </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div class="flex items-center justify-between rounded-lg bg-muted p-4">
                                <div class="flex items-center space-x-3">
                                    <HardDrive class="h-8 w-8 text-blue-500" />
                                    <div>
                                        <h3 class="font-semibold">Storage</h3>
                                        <p class="text-sm text-muted-foreground">SSD Storage Space</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold">{{ hostingPlan.storage_gb }}GB</div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between rounded-lg bg-muted p-4">
                                <div class="flex items-center space-x-3">
                                    <Cpu class="h-8 w-8 text-green-500" />
                                    <div>
                                        <h3 class="font-semibold">CPU Cores</h3>
                                        <p class="text-sm text-muted-foreground">Processing Power</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold">{{ hostingPlan.cpu_cores }}</div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between rounded-lg bg-muted p-4">
                                <div class="flex items-center space-x-3">
                                    <MemoryStick class="h-8 w-8 text-purple-500" />
                                    <div>
                                        <h3 class="font-semibold">RAM Memory</h3>
                                        <p class="text-sm text-muted-foreground">System Memory</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold">{{ hostingPlan.ram_gb }}GB</div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between rounded-lg bg-muted p-4">
                                <div class="flex items-center space-x-3">
                                    <Wifi class="h-8 w-8 text-orange-500" />
                                    <div>
                                        <h3 class="font-semibold">Bandwidth</h3>
                                        <p class="text-sm text-muted-foreground">Data Transfer</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xl font-bold">{{ hostingPlan.bandwidth }}</div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Pricing Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <DollarSign class="mr-2 h-5 w-5" />
                            Pricing & Profitability
                        </CardTitle>
                        <CardDescription> Cost structure and pricing analysis </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between rounded-lg border border-red-200 bg-red-50 p-3">
                                <span class="font-medium text-red-800">Modal Cost:</span>
                                <span class="font-bold text-red-600">{{ formatPrice(hostingPlan.modal_cost) }}</span>
                            </div>

                            <div class="flex items-center justify-between rounded-lg border border-orange-200 bg-orange-50 p-3">
                                <span class="font-medium text-orange-800">Maintenance Cost:</span>
                                <span class="font-bold text-orange-600">{{ formatPrice(hostingPlan.maintenance_cost) }}</span>
                            </div>

                            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <span class="font-medium">Total Costs:</span>
                                <span class="font-bold">{{ formatPrice(hostingPlan.modal_cost + hostingPlan.maintenance_cost) }}</span>
                            </div>

                            <div class="flex items-center justify-between rounded-lg border border-blue-200 bg-blue-50 p-3">
                                <span class="font-medium text-blue-800">Selling Price:</span>
                                <span class="font-bold text-blue-600">{{ formatPrice(hostingPlan.selling_price) }}</span>
                            </div>

                            <div
                                v-if="hostingPlan.discount_percent > 0"
                                class="flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-3"
                            >
                                <span class="font-medium text-green-800">Discount ({{ hostingPlan.discount_percent }}%):</span>
                                <span class="font-bold text-green-600"
                                    >-{{ formatPrice((hostingPlan.selling_price * hostingPlan.discount_percent) / 100) }}</span
                                >
                            </div>

                            <div class="flex items-center justify-between rounded-lg border border-green-300 bg-green-100 p-3">
                                <span class="font-medium text-green-900">Net Profit:</span>
                                <span class="font-bold text-green-700">{{ formatPrice(calculateProfit()) }}</span>
                            </div>

                            <div class="flex items-center justify-between rounded-lg border border-purple-200 bg-purple-50 p-3">
                                <span class="font-medium text-purple-800">Profit Margin:</span>
                                <span class="font-bold text-purple-600">{{ calculateProfitMargin().toFixed(1) }}%</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Features -->
            <Card v-if="hostingPlan.features && hostingPlan.features.length > 0">
                <CardHeader>
                    <CardTitle class="flex items-center">
                        <Star class="mr-2 h-5 w-5" />
                        Plan Features
                    </CardTitle>
                    <CardDescription> Features included in {{ hostingPlan.plan_name }} </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div
                            v-for="(feature, index) in hostingPlan.features"
                            :key="index"
                            class="flex items-center space-x-2 rounded-lg bg-muted p-3"
                        >
                            <CheckCircle class="h-5 w-5 flex-shrink-0 text-green-500" />
                            <span>{{ feature }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Plan Information -->
            <Card>
                <CardHeader>
                    <CardTitle>Plan Information</CardTitle>
                    <CardDescription> General information about this hosting plan </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="font-medium">Plan ID:</span>
                                <span class="font-mono">#{{ hostingPlan.id }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="font-medium">Status:</span>
                                <Badge :variant="hostingPlan.is_active ? 'default' : 'secondary'">
                                    {{ hostingPlan.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="font-medium">Created:</span>
                                <span class="text-sm">{{ formatDate(hostingPlan.created_at) }}</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="font-medium">Plan Name:</span>
                                <span class="font-semibold">{{ hostingPlan.plan_name }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="font-medium">Features Count:</span>
                                <span>{{ hostingPlan.features ? hostingPlan.features.length : 0 }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="font-medium">Last Updated:</span>
                                <span class="text-sm">{{ formatDate(hostingPlan.updated_at) }}</span>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
