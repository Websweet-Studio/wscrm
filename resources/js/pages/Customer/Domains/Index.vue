<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import HostingOrderModal from '@/components/HostingOrderModal.vue';
import DomainBundleModal from '@/components/DomainBundleModal.vue';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import customer from '@/routes/customer';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Crown, Globe, Search, Server, ShoppingCart, Star, TrendingUp } from 'lucide-vue-next';
import { ref } from 'vue';

interface DomainPrice {
    id: number;
    extension: string;
    base_cost: number;
    renewal_cost: number;
    selling_price: number;
    renewal_price_with_tax: number;
    is_active: boolean;
}

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

interface Props {
    domainPrices: DomainPrice[];
    hostingPlans: HostingPlan[];
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const domainSearch = ref('');
const showHostingModal = ref(false);
const showBundleModal = ref(false);
const selectedHostingPlan = ref<HostingPlan | null>(null);
const selectedDomainPrice = ref<DomainPrice | null>(null);
const selectedDomainId = ref<number | null>(null);
const selectedDomainExtension = ref<string>('');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: customer.dashboard().url },
    { title: 'Domains', href: customer.domains.index().url },
];

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const handleSearch = () => {
    router.get(
        customer.domains.index().url,
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const searchDomain = () => {
    if (domainSearch.value.trim()) {
        router.get(customer.domains.search().url, { domain: domainSearch.value });
    }
};

const getPopularExtensions = () => {
    return props.domainPrices
        .filter((domain) => ['com', 'net', 'org', 'info', 'id'].includes(domain.extension))
        .sort((a, b) => a.selling_price - b.selling_price);
};

const getAffordableExtensions = () => {
    return props.domainPrices.sort((a, b) => a.selling_price - b.selling_price).slice(0, 6);
};

const isPremium = (extension: string) => {
    return ['com', 'net', 'org'].includes(extension);
};

const showHostingOfferModal = (hostingPlan: HostingPlan) => {
    selectedHostingPlan.value = hostingPlan;
    showHostingModal.value = true;
};

// Show bundle selection popup when registering domain
const orderDomain = (domainPriceId: number) => {
    selectedDomainId.value = domainPriceId;
    
    // Find the selected domain
    const selectedDomain = props.domainPrices.find(d => d.id === domainPriceId);
    if (selectedDomain) {
        selectedDomainPrice.value = selectedDomain;
        selectedDomainExtension.value = selectedDomain.extension;
        
        // If there are hosting plans available, show bundle selection modal
        if (props.hostingPlans.length > 0) {
            selectedHostingPlan.value = props.hostingPlans[0];
            showBundleModal.value = true;
        } else {
            // Fallback to direct domain order if no hosting plans
            orderDomainOnly(domainPriceId);
        }
    }
};

// Handle bundle selection
const handleSelectBundle = (hostingPlan: HostingPlan, domainPrice: DomainPrice) => {
    selectedHostingPlan.value = hostingPlan;
    selectedDomainPrice.value = domainPrice;
    showBundleModal.value = false;
    showHostingModal.value = true;
};

// Handle domain only selection
const handleSelectDomainOnly = (domainPrice: DomainPrice) => {
    orderDomainOnly(domainPrice.id);
};

const orderDomainOnly = (domainPriceId: number) => {
    router.post(customer.orders.store().url, {
        items: [
            {
                item_type: 'domain',
                item_id: domainPriceId,
                domain_name: domainSearch.value || 'example',
                quantity: 1,
            },
        ],
        billing_cycle: 'annually', // Domain orders are typically annual
    });
};
</script>

<template>
    <Head title="Domain Registration" />

    <CustomerLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-6">
            <!-- Hero Section -->
            <div class="space-y-6 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-100 py-12 text-center">
                <div class="space-y-4">
                    <h1 class="text-4xl font-bold tracking-tight">Find Your Perfect Domain</h1>
                    <p class="mx-auto max-w-3xl text-xl text-muted-foreground">
                        Search for available domains and register them instantly. Your online presence starts here.
                    </p>
                </div>

                <!-- Domain Search -->
                <Card class="mx-auto max-w-2xl">
                    <CardContent class="pt-6">
                        <div class="flex items-center space-x-2">
                            <div class="relative flex-1">
                                <Globe class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-muted-foreground" />
                                <Input
                                    v-model="domainSearch"
                                    placeholder="Enter your domain name (e.g., mywebsite.com)"
                                    class="h-12 pl-11 text-lg"
                                    @keyup.enter="searchDomain"
                                />
                            </div>
                            <Button @click="searchDomain" size="lg" class="px-8">
                                <Search class="mr-2 h-5 w-5" />
                                Search
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Popular Extensions -->
            <div class="space-y-6">
                <div class="space-y-2 text-center">
                    <h2 class="text-3xl font-bold">Popular Domain Extensions</h2>
                    <p class="text-muted-foreground">Most trusted and widely used domain extensions</p>
                </div>

                <div class="mx-auto grid max-w-6xl gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    <Card
                        v-for="domain in getPopularExtensions()"
                        :key="domain.id"
                        class="group relative cursor-pointer overflow-hidden transition-all hover:shadow-lg"
                        @click="orderDomain(domain.id)"
                    >
                        <div v-if="isPremium(domain.extension)" class="absolute top-2 right-2">
                            <Crown class="h-4 w-4 text-yellow-500" />
                        </div>

                        <CardContent class="space-y-4 pt-6 text-center">
                            <div class="text-3xl font-bold text-blue-600">.{{ domain.extension }}</div>

                            <div class="space-y-1">
                                <div class="text-2xl font-bold">{{ formatPrice(domain.selling_price) }}</div>
                                <div class="text-sm text-muted-foreground">per year</div>
                            </div>

                            <Button class="w-full transition-colors group-hover:bg-blue-600">
                                <ShoppingCart class="mr-2 h-4 w-4" />
                                Register
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- All Extensions -->
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold">All Domain Extensions</h2>
                        <p class="text-muted-foreground">Browse all available domain extensions</p>
                    </div>

                    <!-- Extension Search -->
                    <Card class="w-80">
                        <CardContent class="pt-4">
                            <div class="flex items-center space-x-2">
                                <div class="relative flex-1">
                                    <Search class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                                    <Input v-model="search" placeholder="Filter extensions..." class="pl-8" @keyup.enter="handleSearch" />
                                </div>
                                <Button @click="handleSearch" size="sm">Search</Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <Card
                        v-for="domain in domainPrices"
                        :key="domain.id"
                        class="group cursor-pointer transition-all hover:shadow-md"
                        @click="orderDomain(domain.id)"
                    >
                        <CardContent class="pt-6">
                            <div class="mb-4 flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="text-2xl font-bold text-blue-600">.{{ domain.extension }}</div>
                                    <Badge v-if="isPremium(domain.extension)" variant="secondary" class="text-xs">
                                        <Crown class="mr-1 h-3 w-3" />
                                        Premium
                                    </Badge>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Registration</span>
                                    <span class="font-semibold">{{ formatPrice(domain.selling_price) }}</span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Renewal</span>
                                    <span class="font-semibold">{{ formatPrice(domain.renewal_price_with_tax) }}</span>
                                </div>

                                <Button class="mt-4 w-full transition-colors group-hover:bg-blue-600">
                                    <ShoppingCart class="mr-2 h-4 w-4" />
                                    Register
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Why Choose Our Domains -->
            <Card class="bg-gradient-to-br from-blue-50 to-indigo-100">
                <CardContent class="pt-8 pb-8">
                    <div class="space-y-6 text-center">
                        <h2 class="text-2xl font-bold">Why Choose Our Domain Registration?</h2>

                        <div class="mx-auto grid max-w-4xl gap-6 md:grid-cols-3">
                            <div class="space-y-3 text-center">
                                <div class="mx-auto w-fit rounded-full bg-blue-100 p-3">
                                    <Globe class="h-6 w-6 text-blue-600" />
                                </div>
                                <h3 class="font-semibold">Easy Management</h3>
                                <p class="text-sm text-muted-foreground">Intuitive control panel to manage all your domains in one place</p>
                            </div>

                            <div class="space-y-3 text-center">
                                <div class="mx-auto w-fit rounded-full bg-green-100 p-3">
                                    <TrendingUp class="h-6 w-6 text-green-600" />
                                </div>
                                <h3 class="font-semibold">Competitive Pricing</h3>
                                <p class="text-sm text-muted-foreground">Best prices in the market with transparent renewal rates</p>
                            </div>

                            <div class="space-y-3 text-center">
                                <div class="mx-auto w-fit rounded-full bg-purple-100 p-3">
                                    <Star class="h-6 w-6 text-purple-600" />
                                </div>
                                <h3 class="font-semibold">24/7 Support</h3>
                                <p class="text-sm text-muted-foreground">Expert support team ready to help with domain issues</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Empty State -->
            <div v-if="domainPrices.length === 0" class="py-12 text-center">
                <Globe class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                <h3 class="mb-2 text-lg font-semibold">No domain extensions found</h3>
                <p class="text-muted-foreground">
                    {{ search ? 'Try adjusting your search criteria.' : 'No domain extensions are currently available.' }}
                </p>
            </div>

            <!-- Hosting Offer Section -->
            <div v-if="hostingPlans.length > 0" class="space-y-6">
                <div class="space-y-2 text-center">
                    <h2 class="text-3xl font-bold">Complete Your Setup with Hosting</h2>
                    <p class="text-muted-foreground">Get your domain and hosting together for the best experience</p>
                </div>

                <div class="mx-auto grid max-w-6xl gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="plan in hostingPlans.slice(0, 3)"
                        :key="plan.id"
                        class="group relative cursor-pointer overflow-hidden transition-all hover:shadow-lg"
                        @click="showHostingOfferModal(plan)"
                    >
                        <div v-if="plan.discount_percent > 0" class="absolute top-2 right-2">
                            <Badge class="bg-red-500 text-white">{{ plan.discount_percent }}% OFF</Badge>
                        </div>

                        <CardContent class="space-y-4 pt-6 text-center">
                            <div class="mx-auto w-fit rounded-full bg-blue-100 p-3">
                                <Server class="h-8 w-8 text-blue-600" />
                            </div>
                            
                            <div class="text-xl font-bold">{{ plan.plan_name }}</div>

                            <div class="space-y-2 text-sm text-muted-foreground">
                                <div class="flex items-center justify-center space-x-2">
                                    <Server class="h-4 w-4" />
                                    <span>{{ plan.storage_gb }}GB Storage</span>
                                </div>
                                <div class="flex items-center justify-center space-x-2">
                                    <Server class="h-4 w-4" />
                                    <span>{{ plan.cpu_cores }} CPU Cores</span>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <div v-if="plan.discount_percent > 0" class="text-sm text-muted-foreground line-through">
                                    {{ formatPrice(plan.selling_price) }}
                                </div>
                                <div class="text-2xl font-bold text-blue-600">
                                    {{ formatPrice(plan.selling_price * (1 - plan.discount_percent / 100)) }}
                                </div>
                                <div class="text-sm text-muted-foreground">per month</div>
                            </div>

                            <Button class="w-full transition-colors group-hover:bg-blue-600">
                                <ShoppingCart class="mr-2 h-4 w-4" />
                                Get Hosting + Domain
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Domain Bundle Selection Modal -->
        <DomainBundleModal
            v-if="selectedDomainPrice"
            :open="showBundleModal"
            @update:open="showBundleModal = $event"
            :domain-price="selectedDomainPrice"
            :hosting-plan="selectedHostingPlan"
            @select-bundle="handleSelectBundle"
            @select-domain-only="handleSelectDomainOnly"
        />

        <!-- Hosting Order Modal -->
        <HostingOrderModal
            :open="showHostingModal"
            @update:open="showHostingModal = $event"
            :hosting-plan="selectedHostingPlan"
            :domain-prices="domainPrices"
            :existing-domains="[]"
        />
    </CustomerLayout>
</template>
