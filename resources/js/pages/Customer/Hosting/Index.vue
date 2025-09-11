<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import HostingOrderModal from '@/components/HostingOrderModal.vue';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, Cpu, HardDrive, MemoryStick, Search, Server, ShoppingCart, Wifi } from 'lucide-vue-next';
import { ref } from 'vue';

interface HostingPlan {
    id: number;
    plan_name: string;
    storage_gb: number;
    cpu_cores: number;
    ram_gb: number;
    bandwidth: string;
    selling_price: number;
    discount_percent: number;
    features: string[];
    is_active: boolean;
}

interface DomainPrice {
    id: number;
    extension: string;
    selling_price: number;
}

interface ExistingDomain {
    id: number;
    domain_name: string;
    status: string;
}

interface Props {
    hostingPlans: HostingPlan[];
    domainPrices: DomainPrice[];
    existingDomains: ExistingDomain[];
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const showOrderModal = ref(false);
const selectedPlan = ref<HostingPlan | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/customer/dashboard' },
    { title: 'Hosting Plans', href: '/customer/hosting' },
];

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const getDiscountedPrice = (price: number, discount: number) => {
    return price * (1 - discount / 100);
};

const handleSearch = () => {
    router.get(
        '/customer/hosting',
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const orderPlan = (plan: HostingPlan) => {
    selectedPlan.value = plan;
    showOrderModal.value = true;
};
</script>

<template>
    <Head title="Hosting Plans" />

    <CustomerLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="space-y-4 text-center">
                <h1 class="text-4xl font-bold tracking-tight">Choose Your Hosting Plan</h1>
                <p class="mx-auto max-w-3xl text-xl text-muted-foreground">
                    Reliable, fast, and secure hosting solutions for your websites and applications.
                </p>
            </div>

            <!-- Search -->
            <Card class="mx-auto max-w-2xl">
                <CardContent class="pt-6">
                    <div class="flex items-center space-x-2">
                        <div class="relative flex-1">
                            <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                            <Input v-model="search" placeholder="Search hosting plans..." class="pl-10" @keyup.enter="handleSearch" />
                        </div>
                        <Button @click="handleSearch">Search</Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Hosting Plans Grid -->
            <div class="mx-auto grid max-w-7xl gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="plan in hostingPlans" :key="plan.id" class="relative overflow-hidden transition-all hover:shadow-lg">
                    <div v-if="plan.discount_percent > 0" class="absolute top-4 right-4 z-10">
                        <Badge class="bg-red-500 text-white">{{ plan.discount_percent }}% OFF</Badge>
                    </div>

                    <CardHeader class="pb-4 text-center">
                        <div class="mb-4 flex justify-center">
                            <div class="rounded-full bg-blue-100 p-3">
                                <Server class="h-8 w-8 text-blue-600" />
                            </div>
                        </div>
                        <CardTitle class="text-2xl">{{ plan.plan_name }}</CardTitle>
                        <div class="space-y-1">
                            <div v-if="plan.discount_percent > 0" class="text-sm text-muted-foreground line-through">
                                {{ formatPrice(plan.selling_price) }}
                            </div>
                            <div class="text-3xl font-bold text-blue-600">
                                {{ formatPrice(getDiscountedPrice(plan.selling_price, plan.discount_percent)) }}
                            </div>
                            <div class="text-sm text-muted-foreground">per year</div>
                        </div>
                    </CardHeader>

                    <CardContent class="space-y-6">
                        <!-- Specifications -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between rounded bg-muted p-2">
                                <div class="flex items-center space-x-2">
                                    <HardDrive class="h-4 w-4 text-blue-500" />
                                    <span class="text-sm">Storage</span>
                                </div>
                                <span class="font-semibold">{{ plan.storage_gb }}GB</span>
                            </div>

                            <div class="flex items-center justify-between rounded bg-muted p-2">
                                <div class="flex items-center space-x-2">
                                    <Cpu class="h-4 w-4 text-green-500" />
                                    <span class="text-sm">CPU Cores</span>
                                </div>
                                <span class="font-semibold">{{ plan.cpu_cores }}</span>
                            </div>

                            <div class="flex items-center justify-between rounded bg-muted p-2">
                                <div class="flex items-center space-x-2">
                                    <MemoryStick class="h-4 w-4 text-purple-500" />
                                    <span class="text-sm">RAM</span>
                                </div>
                                <span class="font-semibold">{{ plan.ram_gb }}GB</span>
                            </div>

                            <div class="flex items-center justify-between rounded bg-muted p-2">
                                <div class="flex items-center space-x-2">
                                    <Wifi class="h-4 w-4 text-orange-500" />
                                    <span class="text-sm">Bandwidth</span>
                                </div>
                                <span class="font-semibold">{{ plan.bandwidth }}</span>
                            </div>
                        </div>

                        <!-- Features -->
                        <div v-if="plan.features && plan.features.length > 0" class="space-y-2">
                            <h4 class="text-sm font-semibold">Features Included:</h4>
                            <ul class="space-y-1">
                                <li v-for="(feature, index) in plan.features.slice(0, 4)" :key="index" class="flex items-center space-x-2 text-sm">
                                    <Check class="h-3 w-3 flex-shrink-0 text-green-500" />
                                    <span>{{ feature }}</span>
                                </li>
                                <li v-if="plan.features.length > 4" class="pl-5 text-xs text-muted-foreground">
                                    and {{ plan.features.length - 4 }} more features...
                                </li>
                            </ul>
                        </div>

                        <!-- Actions -->
                        <div class="space-y-2 pt-4">
                            <Button @click="orderPlan(plan)" class="w-full" size="lg">
                                <ShoppingCart class="mr-2 h-4 w-4" />
                                Order Now
                            </Button>
                            <Link :href="`/customer/hosting/${plan.id}`">
                                <Button variant="outline" class="w-full" size="lg"> View Details </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-if="hostingPlans.length === 0" class="py-12 text-center">
                <Server class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                <h3 class="mb-2 text-lg font-semibold">No hosting plans found</h3>
                <p class="text-muted-foreground">
                    {{ search ? 'Try adjusting your search criteria.' : 'No hosting plans are currently available.' }}
                </p>
            </div>
        </div>

        <!-- Order Modal -->
        <HostingOrderModal
            v-if="selectedPlan"
            :open="showOrderModal"
            :hosting-plan="selectedPlan"
            :domain-prices="domainPrices"
            :existing-domains="existingDomains"
            @update:open="showOrderModal = $event"
        />
    </CustomerLayout>
</template>
