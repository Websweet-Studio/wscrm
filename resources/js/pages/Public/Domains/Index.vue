<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Head, Link, router } from '@inertiajs/vue3';
import { Crown, Globe, Search, ShoppingCart, Star, TrendingUp } from 'lucide-vue-next';
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

interface Props {
    domainPrices: DomainPrice[];
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const domainSearch = ref('');

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const handleSearch = () => {
    router.get(
        '/domains',
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const searchDomain = () => {
    if (domainSearch.value.trim()) {
        router.get('/domains/search', { domain: domainSearch.value });
    }
};

const getPopularExtensions = () => {
    return props.domainPrices
        .filter((domain) => {
            const cleanExt = domain.extension.replace('.', '');
            return ['com', 'net', 'org', 'info', 'id'].includes(cleanExt);
        })
        .sort((a, b) => a.selling_price - b.selling_price);
};

const isPremium = (extension: string) => {
    const cleanExt = extension.replace('.', '');
    return ['com', 'net', 'org'].includes(cleanExt);
};

const isPopular = (extension: string) => {
    const cleanExt = extension.replace('.', '');
    return ['com', 'net', 'org', 'id', 'co.id'].includes(cleanExt);
};
</script>

<template>
    <Head title="Domain Registration - Find Your Perfect Domain" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
        <!-- Navigation -->
        <nav class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <Link href="/" class="flex items-center space-x-2">
                    <img src="/1.png" alt="WebSweetStudio" class="h-8 w-8 object-contain" />
                    <span class="text-xl font-bold">Ws.</span>
                </Link>
                <div class="flex items-center space-x-4">
                    <Button variant="ghost" asChild>
                        <Link href="/hosting">Hosting</Link>
                    </Button>
                    <Button variant="ghost" asChild>
                        <Link href="/domains">Domains</Link>
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href="/customer/login">Login</Link>
                    </Button>
                    <Button asChild>
                        <Link href="/customer/register">Get Started</Link>
                    </Button>
                </div>
            </div>
        </nav>

        <div class="container mx-auto px-6 py-8">
            <!-- Hero Section -->
            <div
                class="mb-16 space-y-8 rounded-3xl border border-blue-100 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-20 text-center"
            >
                <div class="space-y-6">
                    <div class="inline-flex items-center rounded-full border border-blue-200 bg-white/80 px-4 py-2 text-sm font-medium text-blue-700">
                        <Star class="mr-2 h-4 w-4 text-yellow-500" />
                        Start your online journey today
                    </div>
                    <h1
                        class="bg-gradient-to-br from-blue-900 via-blue-700 to-purple-700 bg-clip-text text-5xl font-bold tracking-tight text-transparent"
                    >
                        Find Your Perfect Domain
                    </h1>
                    <p class="mx-auto max-w-3xl text-xl leading-relaxed text-muted-foreground">
                        Search for available domains and register them instantly. Your online presence starts here with the perfect domain name.
                    </p>
                </div>

                <!-- Enhanced Domain Search -->
                <Card class="mx-auto max-w-3xl border-0 bg-white/90 shadow-xl backdrop-blur-sm">
                    <CardContent class="pt-8 pb-8">
                        <div class="flex flex-col items-center gap-3 sm:flex-row">
                            <div class="relative w-full flex-1">
                                <Globe class="absolute top-1/2 left-4 h-6 w-6 -translate-y-1/2 transform text-blue-500" />
                                <Input
                                    v-model="domainSearch"
                                    placeholder="Enter your domain name (e.g., myawesomesite.com)"
                                    class="h-14 rounded-xl border-2 border-blue-200 pr-4 pl-12 text-lg focus:border-blue-500"
                                    @keyup.enter="searchDomain"
                                />
                            </div>
                            <Button
                                @click="searchDomain"
                                size="lg"
                                class="h-14 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-8 font-semibold text-white shadow-lg transition-all hover:from-blue-700 hover:to-blue-800 hover:shadow-xl"
                            >
                                <Search class="mr-2 h-5 w-5" />
                                Search Domain
                            </Button>
                        </div>
                        <div class="mt-4 flex flex-wrap justify-center gap-2 text-sm text-muted-foreground">
                            <span>Popular searches:</span>
                            <button class="font-medium text-blue-600 hover:text-blue-800">.com</button>
                            <span>•</span>
                            <button class="font-medium text-blue-600 hover:text-blue-800">.id</button>
                            <span>•</span>
                            <button class="font-medium text-blue-600 hover:text-blue-800">.org</button>
                            <span>•</span>
                            <button class="font-medium text-blue-600 hover:text-blue-800">.net</button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Popular Extensions -->
            <div class="mb-16 space-y-8">
                <div class="space-y-3 text-center">
                    <h2 class="text-3xl font-bold">Popular Domain Extensions</h2>
                    <p class="mx-auto max-w-2xl text-muted-foreground">
                        Start your online presence with these most trusted and widely used domain extensions
                    </p>
                </div>

                <div class="mx-auto grid max-w-7xl gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <Card
                        v-for="domain in getPopularExtensions()"
                        :key="domain.id"
                        class="group relative cursor-pointer overflow-hidden border-2 transition-all hover:scale-105 hover:border-blue-300 hover:shadow-xl"
                    >
                        <!-- Premium Badge -->
                        <div v-if="isPremium(domain.extension)" class="absolute top-3 right-3 z-10">
                            <Badge class="bg-gradient-to-r from-yellow-400 to-orange-500 text-xs font-semibold text-white">
                                <Crown class="mr-1 h-3 w-3" />
                                Premium
                            </Badge>
                        </div>

                        <CardContent class="space-y-6 pt-8 pb-6 text-center">
                            <!-- Extension Name -->
                            <div class="space-y-2">
                                <div class="text-4xl font-bold tracking-tight text-blue-600">.{{ domain.extension }}</div>
                                <div class="text-xs tracking-wider text-muted-foreground uppercase">Domain Extension</div>
                            </div>

                            <!-- Pricing -->
                            <div class="space-y-3 rounded-lg bg-gradient-to-br from-blue-50 to-indigo-50 p-4">
                                <div class="space-y-1">
                                    <div class="text-sm font-medium text-muted-foreground">First Year</div>
                                    <div class="text-3xl font-bold text-blue-600">{{ formatPrice(domain.selling_price) }}</div>
                                </div>

                                <div class="space-y-1 border-t pt-2">
                                    <div class="text-xs text-muted-foreground">Renewal: {{ formatPrice(domain.renewal_price_with_tax) }}/year</div>
                                </div>
                            </div>

                            <!-- CTA Button -->
                            <Button
                                asChild
                                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 py-3 font-semibold text-white transition-all group-hover:shadow-lg hover:from-blue-700 hover:to-blue-800"
                            >
                                <Link href="/customer/register">
                                    <ShoppingCart class="mr-2 h-4 w-4" />
                                    Get Started
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- All Extensions -->
            <div class="space-y-8">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                    <div class="space-y-1">
                        <h2 class="text-2xl font-bold">Complete Domain Pricing</h2>
                        <p class="text-muted-foreground">Compare prices and find the perfect extension for your needs</p>
                    </div>

                    <!-- Extension Search -->
                    <Card class="w-full lg:w-80">
                        <CardContent class="pt-4">
                            <div class="flex items-center space-x-2">
                                <div class="relative flex-1">
                                    <Search class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        v-model="search"
                                        placeholder="Search extensions (e.g., com, id, org)..."
                                        class="pl-8"
                                        @keyup.enter="handleSearch"
                                    />
                                </div>
                                <Button @click="handleSearch" size="sm" class="shrink-0">Filter</Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Table View for Better Comparison -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Globe class="mr-2 h-5 w-5 text-blue-600" />
                            Domain Extension Pricing
                        </CardTitle>
                        <CardDescription>
                            All prices are shown per year. Registration is for the first year, renewal applies from the second year onwards.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="border-b bg-muted/50">
                                    <tr>
                                        <th class="px-6 py-4 text-left font-semibold">Extension</th>
                                        <th class="px-6 py-4 text-right font-semibold">Registration</th>
                                        <th class="px-6 py-4 text-right font-semibold">Renewal</th>
                                        <th class="px-6 py-4 text-center font-semibold">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="domain in domainPrices" :key="domain.id" class="border-b transition-colors hover:bg-muted/20">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="text-xl font-bold text-blue-600">.{{ domain.extension }}</div>
                                                <Badge
                                                    v-if="isPremium(domain.extension)"
                                                    class="bg-gradient-to-r from-yellow-400 to-orange-500 text-xs text-white"
                                                >
                                                    <Crown class="mr-1 h-3 w-3" />
                                                    Premium
                                                </Badge>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="space-y-1">
                                                <div class="text-lg font-bold text-green-600">{{ formatPrice(domain.selling_price) }}</div>
                                                <div class="text-xs text-muted-foreground">first year</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="space-y-1">
                                                <div class="text-lg font-semibold">{{ formatPrice(domain.renewal_price_with_tax) }}</div>
                                                <div class="text-xs text-muted-foreground">per year</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <Button asChild size="sm" class="bg-blue-600 text-white hover:bg-blue-700">
                                                <Link href="/customer/register">
                                                    <ShoppingCart class="mr-1 h-4 w-4" />
                                                    Register
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Why Choose Our Domains -->
            <Card class="mt-16 bg-gradient-to-br from-blue-50 to-indigo-100">
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

                        <div class="flex justify-center space-x-4 pt-6">
                            <Button asChild size="lg">
                                <Link href="/customer/register">Get Started Today</Link>
                            </Button>
                            <Button variant="outline" asChild size="lg">
                                <Link href="/hosting">View Hosting Plans</Link>
                            </Button>
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
        </div>

        <!-- Footer -->
        <footer class="mt-16 bg-gray-900 py-8 text-gray-300">
            <div class="container mx-auto px-6">
                <div class="flex flex-col items-center justify-between md:flex-row">
                    <Link href="/" class="mb-4 flex items-center space-x-2 md:mb-0">
                        <img src="/1.png" alt="WebSweetStudio" class="h-8 w-8 object-contain" />
                        <span class="text-xl font-bold text-white">Ws.</span>
                    </Link>
                    <div class="flex space-x-6 text-sm">
                        <Link href="/hosting" class="hover:text-white">Hosting</Link>
                        <Link href="/domains" class="hover:text-white">Domains</Link>
                        <Link href="/customer/login" class="hover:text-white">Customer Login</Link>
                        <Link href="/login" class="hover:text-white">Admin Login</Link>
                    </div>
                </div>
                <div class="mt-6 border-t border-gray-700 pt-6 text-center text-sm">
                    <p>&copy; 2024 WebSweetStudio. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
