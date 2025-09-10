<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, Check, Crown, Globe, Search, ShoppingCart, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface DomainPrice {
    id: number;
    extension: string;
    base_cost: number;
    renewal_cost: number;
    selling_price: number;
    renewal_price_with_tax: number;
    is_active: boolean;
}

interface Props {
    domain: string;
    requestedExtension: string;
    domainPrices: DomainPrice[];
}

const props = defineProps<Props>();

const newSearch = ref(props.domain);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/customer/dashboard' },
    { title: 'Domains', href: '/customer/domains' },
    { title: 'Search Results', href: '#' },
];

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const searchAgain = () => {
    if (newSearch.value.trim()) {
        router.get('/customer/domains/search', { domain: newSearch.value });
    }
};

const isPremium = (extension: string) => {
    return ['com', 'net', 'org'].includes(extension);
};

const isPopular = (extension: string) => {
    return ['com', 'net', 'org', 'id', 'co.id'].includes(extension);
};

const requestedDomain = computed(() => {
    return props.requestedExtension ? `${props.domain}.${props.requestedExtension}` : props.domain;
});

// Simulate domain availability (in real app, this would come from API)
const getDomainStatus = (extension: string) => {
    // Simple simulation: .com domains are often taken, others more likely available
    const isTaken = extension === 'com' && Math.random() > 0.3;
    return {
        available: !isTaken,
        status: isTaken ? 'taken' : 'available',
    };
};

const orderDomain = (domainPriceId: number, extension: string) => {
    const fullDomain = `${props.domain}.${extension}`;
    router.post('/customer/orders', {
        items: [
            {
                item_type: 'domain',
                item_id: domainPriceId,
                domain_name: fullDomain,
                quantity: 1,
            },
        ],
    });
};

const popularDomains = computed(() => {
    return props.domainPrices.filter((domain) => isPopular(domain.extension)).sort((a, b) => a.selling_price - b.selling_price);
});

const otherDomains = computed(() => {
    return props.domainPrices.filter((domain) => !isPopular(domain.extension)).sort((a, b) => a.selling_price - b.selling_price);
});
</script>

<template>
    <Head :title="`Search Results for ${requestedDomain}`" />

    <CustomerLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-6xl space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link href="/customer/domains">
                        <Button variant="outline" size="sm">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Domains
                        </Button>
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Domain Search Results</h1>
                        <p class="text-muted-foreground">Results for "{{ requestedDomain }}"</p>
                    </div>
                </div>
            </div>

            <!-- New Search -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex items-center space-x-2">
                        <div class="relative max-w-md flex-1">
                            <Globe class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                            <Input v-model="newSearch" placeholder="Try another domain..." class="pl-10" @keyup.enter="searchAgain" />
                        </div>
                        <Button @click="searchAgain">
                            <Search class="mr-2 h-4 w-4" />
                            Search Again
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Requested Domain (if specific extension was searched) -->
            <div v-if="requestedExtension">
                <h2 class="mb-4 text-2xl font-bold">Your Search</h2>
                <Card class="border-2 border-blue-200">
                    <CardContent class="pt-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="text-2xl font-bold">{{ requestedDomain }}</div>
                                <Badge v-if="isPremium(requestedExtension)" variant="secondary">
                                    <Crown class="mr-1 h-3 w-3" />
                                    Premium
                                </Badge>
                            </div>

                            <div class="flex items-center space-x-4">
                                <div v-if="getDomainStatus(requestedExtension).available" class="flex items-center space-x-2 text-green-600">
                                    <Check class="h-5 w-5" />
                                    <span class="font-semibold">Available</span>
                                </div>
                                <div v-else class="flex items-center space-x-2 text-red-600">
                                    <X class="h-5 w-5" />
                                    <span class="font-semibold">Taken</span>
                                </div>

                                <div v-if="getDomainStatus(requestedExtension).available" class="text-right">
                                    <div class="text-2xl font-bold text-blue-600">
                                        {{ formatPrice(domainPrices.find((d) => d.extension === requestedExtension)?.selling_price || 0) }}
                                    </div>
                                    <Button
                                        @click="
                                            orderDomain(domainPrices.find((d) => d.extension === requestedExtension)?.id || 0, requestedExtension)
                                        "
                                        size="lg"
                                    >
                                        <ShoppingCart class="mr-2 h-4 w-4" />
                                        Register Now
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Popular Extensions -->
            <div>
                <h2 class="mb-4 text-2xl font-bold">Popular Extensions</h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="domainPrice in popularDomains"
                        :key="domainPrice.id"
                        :class="[
                            'transition-all hover:shadow-md',
                            getDomainStatus(domainPrice.extension).available ? 'cursor-pointer hover:border-green-300' : 'opacity-75',
                        ]"
                    >
                        <CardContent class="pt-6">
                            <div class="mb-4 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="text-xl font-bold">{{ domain }}.{{ domainPrice.extension }}</div>
                                    <Badge v-if="isPremium(domainPrice.extension)" variant="secondary" class="text-xs">
                                        <Crown class="mr-1 h-3 w-3" />
                                        Premium
                                    </Badge>
                                </div>

                                <div v-if="getDomainStatus(domainPrice.extension).available" class="text-green-600">
                                    <Check class="h-5 w-5" />
                                </div>
                                <div v-else class="text-red-600">
                                    <X class="h-5 w-5" />
                                </div>
                            </div>

                            <div v-if="getDomainStatus(domainPrice.extension).available" class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">First year</span>
                                    <span class="text-lg font-bold text-blue-600">{{ formatPrice(domainPrice.selling_price) }}</span>
                                </div>

                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Renewal</span>
                                    <span>{{ formatPrice(domainPrice.renewal_price_with_tax) }}/year</span>
                                </div>

                                <Button @click="orderDomain(domainPrice.id, domainPrice.extension)" class="w-full">
                                    <ShoppingCart class="mr-2 h-4 w-4" />
                                    Register
                                </Button>
                            </div>

                            <div v-else class="space-y-3">
                                <div class="py-4 text-center text-muted-foreground">
                                    <AlertCircle class="mx-auto mb-2 h-6 w-6" />
                                    <div class="text-sm">Domain not available</div>
                                </div>
                                <Button variant="outline" class="w-full" disabled> Not Available </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Other Extensions -->
            <div>
                <h2 class="mb-4 text-2xl font-bold">Other Extensions</h2>
                <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                    <Card
                        v-for="domainPrice in otherDomains"
                        :key="domainPrice.id"
                        :class="[
                            'transition-all hover:shadow-sm',
                            getDomainStatus(domainPrice.extension).available ? 'cursor-pointer hover:border-green-200' : 'opacity-60',
                        ]"
                    >
                        <CardContent class="pt-4 pb-4">
                            <div class="mb-3 flex items-center justify-between">
                                <div class="font-semibold">{{ domain }}.{{ domainPrice.extension }}</div>
                                <div v-if="getDomainStatus(domainPrice.extension).available" class="text-green-500">
                                    <Check class="h-4 w-4" />
                                </div>
                                <div v-else class="text-red-500">
                                    <X class="h-4 w-4" />
                                </div>
                            </div>

                            <div v-if="getDomainStatus(domainPrice.extension).available" class="space-y-2">
                                <div class="text-center">
                                    <div class="font-bold text-blue-600">{{ formatPrice(domainPrice.selling_price) }}</div>
                                    <div class="text-xs text-muted-foreground">first year</div>
                                </div>

                                <Button @click="orderDomain(domainPrice.id, domainPrice.extension)" size="sm" class="w-full"> Register </Button>
                            </div>

                            <div v-else class="text-center">
                                <div class="mb-2 text-sm text-muted-foreground">Not Available</div>
                                <Button variant="outline" size="sm" class="w-full" disabled> Taken </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Help Section -->
            <Card class="bg-blue-50">
                <CardContent class="pt-6">
                    <div class="space-y-4 text-center">
                        <div class="flex justify-center">
                            <div class="rounded-full bg-blue-100 p-3">
                                <AlertCircle class="h-6 w-6 text-blue-600" />
                            </div>
                        </div>
                        <div>
                            <h3 class="mb-2 text-lg font-semibold">Need Help Choosing a Domain?</h3>
                            <p class="mb-4 text-muted-foreground">
                                Consider alternatives like adding words, using synonyms, or trying different extensions.
                            </p>
                            <div class="flex justify-center space-x-4">
                                <Link href="/customer/domains">
                                    <Button variant="outline"> Browse All Extensions </Button>
                                </Link>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </CustomerLayout>
</template>
