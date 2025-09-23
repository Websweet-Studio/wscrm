<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, DollarSign, Globe, Settings } from 'lucide-vue-next';

interface DomainPrice {
    id: number;
    extension: string;
    base_cost: number;
    renewal_cost: number;
    selling_price: number;
    renewal_price_with_tax: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    domainPrice: DomainPrice;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Domain Prices', href: '/admin/domain-prices' },
    { title: `.${props.domainPrice.extension}`, href: `/admin/domain-prices/${props.domainPrice.id}` },
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
</script>

<template>
    <Head :title="`Domain Price - .${domainPrice.extension}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link href="/admin/domain-prices">
                        <Button variant="outline" size="sm">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Domain Prices
                        </Button>
                    </Link>
                    <div>
                        <h1 class="flex items-center text-3xl font-bold tracking-tight">
                            <Globe class="mr-3 h-8 w-8 text-blue-500" />
                            .{{ domainPrice.extension }}
                        </h1>
                        <p class="text-muted-foreground">Domain extension pricing details</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <Badge :variant="domainPrice.is_active ? 'default' : 'secondary'" class="px-3 py-1 text-sm">
                        {{ domainPrice.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                    <Link :href="`/admin/domain-prices/${domainPrice.id}/edit`">
                        <Button>
                            <Settings class="mr-2 h-4 w-4" />
                            Edit Price
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Pricing Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <DollarSign class="mr-2 h-5 w-5" />
                            Pricing Information
                        </CardTitle>
                        <CardDescription> Current pricing structure for .{{ domainPrice.extension }} domain </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div class="flex items-center justify-between rounded-lg bg-muted p-4">
                                <div>
                                    <h3 class="text-lg font-semibold">Base Cost</h3>
                                    <p class="text-sm text-muted-foreground">Internal cost for domain</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-red-600">{{ formatPrice(domainPrice.base_cost) }}</div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between rounded-lg bg-muted p-4">
                                <div>
                                    <h3 class="text-lg font-semibold">Renewal Cost</h3>
                                    <p class="text-sm text-muted-foreground">Internal renewal cost</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-orange-600">{{ formatPrice(domainPrice.renewal_cost) }}</div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between rounded-lg bg-muted p-4">
                                <div>
                                    <h3 class="text-lg font-semibold">Selling Price</h3>
                                    <p class="text-sm text-muted-foreground">Customer selling price</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-green-600">{{ formatPrice(domainPrice.selling_price) }}</div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between rounded-lg bg-muted p-4">
                                <div>
                                    <h3 class="text-lg font-semibold">Renewal w/ Tax</h3>
                                    <p class="text-sm text-muted-foreground">Renewal price including tax</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-blue-600">{{ formatPrice(domainPrice.renewal_price_with_tax) }}</div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Domain Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Domain Information</CardTitle>
                        <CardDescription> General information about this domain extension </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="font-medium">Extension:</span>
                                <span class="font-mono text-lg">.{{ domainPrice.extension }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="font-medium">Status:</span>
                                <Badge :variant="domainPrice.is_active ? 'default' : 'secondary'">
                                    {{ domainPrice.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="font-medium">ID:</span>
                                <span class="font-mono">#{{ domainPrice.id }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="font-medium">Created:</span>
                                <span class="text-sm">{{ formatDate(domainPrice.created_at) }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="font-medium">Last Updated:</span>
                                <span class="text-sm">{{ formatDate(domainPrice.updated_at) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Price Comparison Card -->
            <Card>
                <CardHeader>
                    <CardTitle>Price Comparison</CardTitle>
                    <CardDescription> Compare different pricing options for .{{ domainPrice.extension }} </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-4 gap-4">
                        <div class="rounded-lg border p-4 text-center">
                            <div class="mb-1 text-sm text-muted-foreground">Base Cost</div>
                            <div class="text-xl font-bold text-red-600">{{ formatPrice(domainPrice.base_cost) }}</div>
                            <div class="mt-1 text-xs text-muted-foreground">Internal</div>
                        </div>
                        <div class="rounded-lg border p-4 text-center">
                            <div class="mb-1 text-sm text-muted-foreground">Renewal Cost</div>
                            <div class="text-xl font-bold text-orange-600">{{ formatPrice(domainPrice.renewal_cost) }}</div>
                            <div class="mt-1 text-xs text-muted-foreground">Internal</div>
                        </div>
                        <div class="rounded-lg border p-4 text-center">
                            <div class="mb-1 text-sm text-muted-foreground">Selling Price</div>
                            <div class="text-xl font-bold text-green-600">{{ formatPrice(domainPrice.selling_price) }}</div>
                            <div class="mt-1 text-xs text-muted-foreground">Customer</div>
                        </div>
                        <div class="rounded-lg border p-4 text-center">
                            <div class="mb-1 text-sm text-muted-foreground">Renewal w/ Tax</div>
                            <div class="text-xl font-bold text-blue-600">{{ formatPrice(domainPrice.renewal_price_with_tax) }}</div>
                            <div class="mt-1 text-xs text-muted-foreground">Customer</div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
