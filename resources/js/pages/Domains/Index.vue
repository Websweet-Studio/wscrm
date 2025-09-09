<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface DomainPrice {
    id: number;
    extension: string;
    selling_price: number;
    renewal_price_with_tax: number;
}

interface Props {
    domainPrices: DomainPrice[];
}

const props = defineProps<Props>();

const searchDomain = ref('');

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Domain Search', href: '/domains' }];

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const searchDomainAvailability = () => {
    if (!searchDomain.value.trim()) return;

    router.get('/domains/search', {
        domain: searchDomain.value.trim(),
        extension: '.com',
    });
};

const getExtensionColor = (extension: string) => {
    if (extension.includes('.id')) return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
    if (['.com', '.net', '.org'].includes(extension)) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
    if (['.co', '.online', '.site'].includes(extension)) return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
    return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
};

const popularExtensions = props.domainPrices.filter((domain) => ['.com', '.net', '.org', '.id', '.co.id'].includes(domain.extension));

const idExtensions = props.domainPrices.filter((domain) => domain.extension.includes('.id'));

const otherExtensions = props.domainPrices.filter(
    (domain) => !domain.extension.includes('.id') && !['.com', '.net', '.org'].includes(domain.extension),
);
</script>

<template>
    <Head title="Domain Search" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-6">
            <div class="space-y-4 text-center">
                <h1 class="text-3xl font-bold tracking-tight">Find Your Perfect Domain</h1>
                <p class="text-lg text-muted-foreground">Search and register your domain name today</p>

                <div class="mx-auto flex max-w-md gap-2">
                    <Input v-model="searchDomain" placeholder="Enter domain name..." class="flex-1" @keyup.enter="searchDomainAvailability" />
                    <Button @click="searchDomainAvailability" :disabled="!searchDomain.trim()"> Search </Button>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Popular Extensions -->
                <div class="space-y-4">
                    <h2 class="text-2xl font-semibold">Popular Domains</h2>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Card v-for="domain in popularExtensions" :key="domain.id">
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <CardTitle class="text-xl">{{ domain.extension }}</CardTitle>
                                    <span
                                        :class="`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${getExtensionColor(domain.extension)}`"
                                    >
                                        Popular
                                    </span>
                                </div>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Registration</span>
                                    <span class="text-lg font-bold">{{ formatPrice(domain.selling_price) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Renewal</span>
                                    <span class="font-medium">{{ formatPrice(domain.renewal_price_with_tax) }}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Indonesian Domains -->
                <div class="space-y-4">
                    <h2 class="text-2xl font-semibold">Indonesian Domains</h2>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <Card v-for="domain in idExtensions" :key="domain.id" class="text-center">
                            <CardHeader>
                                <CardTitle class="text-lg">{{ domain.extension }}</CardTitle>
                                <CardDescription>{{ formatPrice(domain.selling_price) }}/year</CardDescription>
                            </CardHeader>
                        </Card>
                    </div>
                </div>

                <!-- Other Extensions -->
                <div class="space-y-4">
                    <h2 class="text-2xl font-semibold">Other Extensions</h2>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <Card v-for="domain in otherExtensions" :key="domain.id" class="text-center">
                            <CardHeader>
                                <CardTitle class="text-lg">{{ domain.extension }}</CardTitle>
                                <CardDescription>{{ formatPrice(domain.selling_price) }}/year</CardDescription>
                            </CardHeader>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
