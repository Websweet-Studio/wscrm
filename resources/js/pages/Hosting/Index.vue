<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

interface HostingPlan {
    id: number;
    plan_name: string;
    storage_gb: number;
    cpu_cores: number;
    ram_gb: number;
    selling_price: number;
    discount_percent: number;
    features: {
        email_accounts: string | number;
        databases: string | number;
        ssl_certificate: boolean;
        backup: string;
    };
}

interface Props {
    hostingPlans: Record<string, HostingPlan[]>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Hosting Plans', href: '/hosting' }];

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const getBadgeColor = (planName: string) => {
    switch (planName) {
        case 'Lite':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'Basic':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'VPS':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};
</script>

<template>
    <Head title="Hosting Plans" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-6">
            <div class="text-center">
                <h1 class="text-3xl font-bold tracking-tight">Choose Your Hosting Plan</h1>
                <p class="mt-2 text-lg text-muted-foreground">Select the perfect hosting solution for your website</p>
            </div>

            <div v-for="(plans, planName) in hostingPlans" :key="planName" class="space-y-4">
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-semibold">{{ planName }} Plans</h2>
                    <span :class="`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${getBadgeColor(planName)}`">
                        {{ plans.length }} options
                    </span>
                </div>

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <Card v-for="plan in plans" :key="plan.id" class="relative">
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-lg">{{ plan.storage_gb }}GB</CardTitle>
                                <span
                                    v-if="plan.discount_percent !== 0"
                                    :class="`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${
                                        plan.discount_percent > 0
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                                    }`"
                                >
                                    {{ plan.discount_percent > 0 ? '+' : '' }}{{ plan.discount_percent }}%
                                </span>
                            </div>
                            <CardDescription> {{ plan.cpu_cores }} CPU Cores • {{ plan.ram_gb }}GB RAM </CardDescription>
                        </CardHeader>

                        <CardContent class="space-y-4">
                            <div class="text-center">
                                <div class="text-3xl font-bold">{{ formatPrice(plan.selling_price) }}</div>
                                <div class="text-sm text-muted-foreground">per year</div>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Storage</span>
                                    <span class="font-medium">{{ plan.storage_gb }}GB</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Email Accounts</span>
                                    <span class="font-medium">{{ plan.features.email_accounts }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Databases</span>
                                    <span class="font-medium">{{ plan.features.databases }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>SSL Certificate</span>
                                    <span class="font-medium">{{ plan.features.ssl_certificate ? '✓' : '✗' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Backup</span>
                                    <span class="font-medium capitalize">{{ plan.features.backup }}</span>
                                </div>
                            </div>
                        </CardContent>

                        <CardFooter>
                            <Button class="w-full" asChild>
                                <Link :href="`/hosting/${plan.id}`"> Choose Plan </Link>
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
