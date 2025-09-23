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
        <div class="space-y-6">
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

            <!-- Hosting Plans Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Available Hosting Plans</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Plan</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Storage</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">CPU</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">RAM</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Bandwidth</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Features</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Price</th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="plan in hostingPlans"
                                    :key="plan.id"
                                    class="border-b border-border hover:bg-muted/30 transition-colors"
                                >
                                    <td class="px-3 py-4 text-sm">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full bg-blue-100 p-2">
                                                <Server class="h-4 w-4 text-blue-600" />
                                            </div>
                                            <div>
                                                <div class="font-medium text-foreground">{{ plan.plan_name }}</div>
                                                <div v-if="plan.discount_percent > 0" class="flex items-center">
                                                    <Badge class="bg-red-500 text-white text-xs">{{ plan.discount_percent }}% OFF</Badge>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-sm">
                                        <div class="flex items-center gap-2">
                                            <HardDrive class="h-4 w-4 text-blue-500" />
                                            <span class="font-medium">{{ plan.storage_gb }}GB</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-sm">
                                        <div class="flex items-center gap-2">
                                            <Cpu class="h-4 w-4 text-green-500" />
                                            <span class="font-medium">{{ plan.cpu_cores }} Core{{ plan.cpu_cores > 1 ? 's' : '' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-sm">
                                        <div class="flex items-center gap-2">
                                            <MemoryStick class="h-4 w-4 text-purple-500" />
                                            <span class="font-medium">{{ plan.ram_gb }}GB</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-sm">
                                        <div class="flex items-center gap-2">
                                            <Wifi class="h-4 w-4 text-orange-500" />
                                            <span class="font-medium">{{ plan.bandwidth }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-sm max-w-xs">
                                        <div v-if="plan.features && plan.features.length > 0" class="space-y-1">
                                            <div v-for="(feature, index) in plan.features.slice(0, 2)" :key="index" class="flex items-center gap-1 text-xs">
                                                <Check class="h-3 w-3 text-green-500 flex-shrink-0" />
                                                <span>{{ feature }}</span>
                                            </div>
                                            <div v-if="plan.features.length > 2" class="text-xs text-blue-600">
                                                +{{ plan.features.length - 2 }} more
                                            </div>
                                        </div>
                                        <span v-else class="text-muted-foreground">-</span>
                                    </td>
                                    <td class="px-3 py-4 text-sm">
                                        <div class="space-y-1">
                                            <div v-if="plan.discount_percent > 0" class="text-xs text-muted-foreground line-through">
                                                {{ formatPrice(plan.selling_price) }}
                                            </div>
                                            <div class="font-bold text-blue-600">
                                                {{ formatPrice(getDiscountedPrice(plan.selling_price, plan.discount_percent)) }}
                                            </div>
                                            <div class="text-xs text-muted-foreground">per year</div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4">
                                        <div class="flex items-center justify-center gap-1">
                                            <Button @click="orderPlan(plan)" size="sm" title="Order Now">
                                                <ShoppingCart class="h-3 w-3" />
                                            </Button>
                                            <Button variant="outline" size="sm" asChild title="View Details">
                                                <Link :href="`/customer/hosting/${plan.id}`">
                                                    <Server class="h-3 w-3" />
                                                </Link>
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

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
