<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Globe, ShoppingCart, TrendingUp, Crown, Star, ArrowRight } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';

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
  router.get('/domains', { search: search.value }, { 
    preserveState: true,
    replace: true 
  });
};

const searchDomain = () => {
  if (domainSearch.value.trim()) {
    router.get('/domains/search', { domain: domainSearch.value });
  }
};

const getPopularExtensions = () => {
  return props.domainPrices
    .filter(domain => ['com', 'net', 'org', 'info', 'id'].includes(domain.extension))
    .sort((a, b) => a.selling_price - b.selling_price);
};

const isPremium = (extension: string) => {
  return ['com', 'net', 'org'].includes(extension);
};

const isPopular = (extension: string) => {
  return ['com', 'net', 'org', 'id', 'co.id'].includes(extension);
};
</script>

<template>
  <Head title="Domain Registration - Find Your Perfect Domain" />

  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
    <!-- Navigation -->
    <nav class="container mx-auto px-6 py-4">
      <div class="flex items-center justify-between">
        <Link href="/" class="flex items-center space-x-2">
          <img src="/1.png" alt="WebSweetStudio" class="w-8 h-8 object-contain" />
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
      <div class="text-center space-y-8 py-20 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 rounded-3xl mb-16 border border-blue-100">
        <div class="space-y-6">
          <div class="inline-flex items-center px-4 py-2 bg-white/80 rounded-full border border-blue-200 text-sm font-medium text-blue-700">
            <Star class="h-4 w-4 mr-2 text-yellow-500" />
            Start your online journey today
          </div>
          <h1 class="text-5xl font-bold tracking-tight bg-gradient-to-br from-blue-900 via-blue-700 to-purple-700 bg-clip-text text-transparent">
            Find Your Perfect Domain
          </h1>
          <p class="text-xl text-muted-foreground max-w-3xl mx-auto leading-relaxed">
            Search for available domains and register them instantly. Your online presence starts here with the perfect domain name.
          </p>
        </div>

        <!-- Enhanced Domain Search -->
        <Card class="max-w-3xl mx-auto shadow-xl border-0 bg-white/90 backdrop-blur-sm">
          <CardContent class="pt-8 pb-8">
            <div class="flex flex-col sm:flex-row items-center gap-3">
              <div class="relative flex-1 w-full">
                <Globe class="absolute left-4 top-1/2 transform -translate-y-1/2 h-6 w-6 text-blue-500" />
                <Input
                  v-model="domainSearch"
                  placeholder="Enter your domain name (e.g., myawesomesite.com)"
                  class="pl-12 pr-4 h-14 text-lg border-2 border-blue-200 focus:border-blue-500 rounded-xl"
                  @keyup.enter="searchDomain"
                />
              </div>
              <Button @click="searchDomain" size="lg" class="px-8 h-14 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all">
                <Search class="h-5 w-5 mr-2" />
                Search Domain
              </Button>
            </div>
            <div class="mt-4 flex flex-wrap justify-center gap-2 text-sm text-muted-foreground">
              <span>Popular searches:</span>
              <button class="text-blue-600 hover:text-blue-800 font-medium">.com</button>
              <span>•</span>
              <button class="text-blue-600 hover:text-blue-800 font-medium">.id</button>
              <span>•</span>
              <button class="text-blue-600 hover:text-blue-800 font-medium">.org</button>
              <span>•</span>
              <button class="text-blue-600 hover:text-blue-800 font-medium">.net</button>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Popular Extensions -->
      <div class="space-y-8 mb-16">
        <div class="text-center space-y-3">
          <h2 class="text-3xl font-bold">Popular Domain Extensions</h2>
          <p class="text-muted-foreground max-w-2xl mx-auto">Start your online presence with these most trusted and widely used domain extensions</p>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 max-w-7xl mx-auto">
          <Card v-for="domain in getPopularExtensions()" :key="domain.id" 
                class="relative overflow-hidden hover:shadow-xl transition-all cursor-pointer group border-2 hover:border-blue-300 hover:scale-105">
            
            <!-- Premium Badge -->
            <div v-if="isPremium(domain.extension)" class="absolute top-3 right-3 z-10">
              <Badge class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-semibold">
                <Crown class="h-3 w-3 mr-1" />
                Premium
              </Badge>
            </div>
            
            <CardContent class="pt-8 pb-6 text-center space-y-6">
              <!-- Extension Name -->
              <div class="space-y-2">
                <div class="text-4xl font-bold text-blue-600 tracking-tight">.{{ domain.extension }}</div>
                <div class="text-xs text-muted-foreground uppercase tracking-wider">Domain Extension</div>
              </div>
              
              <!-- Pricing -->
              <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-4 rounded-lg space-y-3">
                <div class="space-y-1">
                  <div class="text-sm text-muted-foreground font-medium">First Year</div>
                  <div class="text-3xl font-bold text-blue-600">{{ formatPrice(domain.selling_price) }}</div>
                </div>
                
                <div class="border-t pt-2 space-y-1">
                  <div class="text-xs text-muted-foreground">Renewal: {{ formatPrice(domain.renewal_price_with_tax) }}/year</div>
                </div>
              </div>

              <!-- CTA Button -->
              <Button asChild class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 group-hover:shadow-lg transition-all">
                <Link href="/customer/register">
                  <ShoppingCart class="h-4 w-4 mr-2" />
                  Get Started
                </Link>
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- All Extensions -->
      <div class="space-y-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <div class="space-y-1">
            <h2 class="text-2xl font-bold">Complete Domain Pricing</h2>
            <p class="text-muted-foreground">Compare prices and find the perfect extension for your needs</p>
          </div>
          
          <!-- Extension Search -->
          <Card class="w-full lg:w-80">
            <CardContent class="pt-4">
              <div class="flex items-center space-x-2">
                <div class="relative flex-1">
                  <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
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
              <Globe class="h-5 w-5 mr-2 text-blue-600" />
              Domain Extension Pricing
            </CardTitle>
            <CardDescription>
              All prices are shown per year. Registration is for the first year, renewal applies from the second year onwards.
            </CardDescription>
          </CardHeader>
          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-muted/50 border-b">
                  <tr>
                    <th class="text-left py-4 px-6 font-semibold">Extension</th>
                    <th class="text-right py-4 px-6 font-semibold">Registration</th>
                    <th class="text-right py-4 px-6 font-semibold">Renewal</th>
                    <th class="text-center py-4 px-6 font-semibold">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="domain in domainPrices" :key="domain.id" 
                      class="border-b hover:bg-muted/20 transition-colors">
                    <td class="py-4 px-6">
                      <div class="flex items-center space-x-3">
                        <div class="text-xl font-bold text-blue-600">.{{ domain.extension }}</div>
                        <Badge v-if="isPremium(domain.extension)" 
                               class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs">
                          <Crown class="h-3 w-3 mr-1" />
                          Premium
                        </Badge>
                      </div>
                    </td>
                    <td class="py-4 px-6 text-right">
                      <div class="space-y-1">
                        <div class="text-lg font-bold text-green-600">{{ formatPrice(domain.selling_price) }}</div>
                        <div class="text-xs text-muted-foreground">first year</div>
                      </div>
                    </td>
                    <td class="py-4 px-6 text-right">
                      <div class="space-y-1">
                        <div class="text-lg font-semibold">{{ formatPrice(domain.renewal_price_with_tax) }}</div>
                        <div class="text-xs text-muted-foreground">per year</div>
                      </div>
                    </td>
                    <td class="py-4 px-6 text-center">
                      <Button asChild size="sm" class="bg-blue-600 hover:bg-blue-700 text-white">
                        <Link href="/customer/register">
                          <ShoppingCart class="h-4 w-4 mr-1" />
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
      <Card class="bg-gradient-to-br from-blue-50 to-indigo-100 mt-16">
        <CardContent class="pt-8 pb-8">
          <div class="text-center space-y-6">
            <h2 class="text-2xl font-bold">Why Choose Our Domain Registration?</h2>
            
            <div class="grid gap-6 md:grid-cols-3 max-w-4xl mx-auto">
              <div class="text-center space-y-3">
                <div class="p-3 bg-blue-100 rounded-full w-fit mx-auto">
                  <Globe class="h-6 w-6 text-blue-600" />
                </div>
                <h3 class="font-semibold">Easy Management</h3>
                <p class="text-sm text-muted-foreground">
                  Intuitive control panel to manage all your domains in one place
                </p>
              </div>
              
              <div class="text-center space-y-3">
                <div class="p-3 bg-green-100 rounded-full w-fit mx-auto">
                  <TrendingUp class="h-6 w-6 text-green-600" />
                </div>
                <h3 class="font-semibold">Competitive Pricing</h3>
                <p class="text-sm text-muted-foreground">
                  Best prices in the market with transparent renewal rates
                </p>
              </div>
              
              <div class="text-center space-y-3">
                <div class="p-3 bg-purple-100 rounded-full w-fit mx-auto">
                  <Star class="h-6 w-6 text-purple-600" />
                </div>
                <h3 class="font-semibold">24/7 Support</h3>
                <p class="text-sm text-muted-foreground">
                  Expert support team ready to help with domain issues
                </p>
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
      <div v-if="domainPrices.length === 0" class="text-center py-12">
        <Globe class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
        <h3 class="text-lg font-semibold mb-2">No domain extensions found</h3>
        <p class="text-muted-foreground">
          {{ search ? 'Try adjusting your search criteria.' : 'No domain extensions are currently available.' }}
        </p>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-8 mt-16">
      <div class="container mx-auto px-6">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <Link href="/" class="flex items-center space-x-2 mb-4 md:mb-0">
            <img src="/1.png" alt="WebSweetStudio" class="w-8 h-8 object-contain" />
            <span class="text-xl font-bold text-white">Ws.</span>
          </Link>
          <div class="flex space-x-6 text-sm">
            <Link href="/hosting" class="hover:text-white">Hosting</Link>
            <Link href="/domains" class="hover:text-white">Domains</Link>
            <Link href="/customer/login" class="hover:text-white">Customer Login</Link>
            <Link href="/login" class="hover:text-white">Admin Login</Link>
          </div>
        </div>
        <div class="border-t border-gray-700 mt-6 pt-6 text-center text-sm">
          <p>&copy; 2024 WebSweetStudio. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>