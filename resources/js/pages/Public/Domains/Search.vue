<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Globe, ShoppingCart, Check, X, Search, Crown, AlertCircle } from 'lucide-vue-next';
import { ref, computed } from 'vue';
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
  domain: string;
  requestedDomain?: string;
  requestedExtension: string;
  domainPrices: DomainPrice[];
  availabilityResults: Record<string, any>;
}

const props = defineProps<Props>();

const newSearch = ref(props.domain);

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price);
};

const searchAgain = () => {
  if (newSearch.value.trim()) {
    router.get('/domains/search', { domain: newSearch.value });
  }
};

const isPremium = (extension: string) => {
  // Remove dot if present for comparison
  const cleanExt = extension.replace('.', '');
  return ['com', 'net', 'org'].includes(cleanExt);
};


const requestedDomain = computed(() => {
  return props.requestedExtension ? 
    `${props.domain}.${props.requestedExtension}` : 
    props.domain;
});

// Get domain availability from RNA API results
const getDomainStatus = (extension: string) => {
  const fullDomain = `${props.domain}.${extension}`;
  const apiResult = props.availabilityResults[fullDomain];
  
  if (apiResult && apiResult.success) {
    return {
      available: apiResult.available,
      status: apiResult.available ? 'available' : 'taken',
      loading: false
    };
  }
  
  // Fallback if API data not available
  return {
    available: false,
    status: 'checking',
    loading: true
  };
};


const getExtensionPrice = (extension: string): number => {
  // Try with dot prefix first
  let domain = props.domainPrices.find(d => d.extension === '.' + extension);
  
  // If not found, try without dot (in case extension already has dot)
  if (!domain) {
    domain = props.domainPrices.find(d => d.extension === extension);
  }
  
  return domain?.selling_price || 0;
};

const getExtensionRenewalPrice = (extension: string): number => {
  // Try with dot prefix first
  let domain = props.domainPrices.find(d => d.extension === '.' + extension);
  
  // If not found, try without dot (in case extension already has dot)
  if (!domain) {
    domain = props.domainPrices.find(d => d.extension === extension);
  }
  
  return domain?.renewal_price_with_tax || 0;
};
</script>

<template>
  <Head :title="`Search Results for ${requestedDomain}`" />

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

    <div class="container mx-auto px-6 py-8 max-w-6xl">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center space-x-4">
          <Link href="/domains">
            <Button variant="outline" size="sm">
              <ArrowLeft class="h-4 w-4 mr-2" />
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
      <Card class="mb-8">
        <CardContent class="pt-6">
          <div class="flex items-center space-x-2">
            <div class="relative flex-1 max-w-md">
              <Globe class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="newSearch"
                placeholder="Try another domain..."
                class="pl-10"
                @keyup.enter="searchAgain"
              />
            </div>
            <Button @click="searchAgain">
              <Search class="h-4 w-4 mr-2" />
              Search Again
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Requested Domain (if specific extension was searched) -->
      <div v-if="requestedExtension" class="mb-8">
        <h2 class="text-2xl font-bold mb-4">Your Search</h2>
        <Card class="border-2 border-blue-200">
          <CardContent class="pt-6">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="text-2xl font-bold">{{ requestedDomain }}</div>
                <Badge v-if="isPremium(requestedExtension)" variant="secondary">
                  <Crown class="h-3 w-3 mr-1" />
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
                    {{ formatPrice(getExtensionPrice(requestedExtension)) }}
                  </div>
                  <Button asChild size="lg">
                    <Link href="/customer/register">
                      <ShoppingCart class="h-4 w-4 mr-2" />
                      Register Now
                    </Link>
                  </Button>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>


      <!-- Help Section -->
      <Card class="bg-blue-50">
        <CardContent class="pt-6">
          <div class="text-center space-y-4">
            <div class="flex justify-center">
              <div class="p-3 bg-blue-100 rounded-full">
                <AlertCircle class="h-6 w-6 text-blue-600" />
              </div>
            </div>
            <div>
              <h3 class="text-lg font-semibold mb-2">Need Help Choosing a Domain?</h3>
              <p class="text-muted-foreground mb-4">
                Consider alternatives like adding words, using synonyms, or trying different extensions.
              </p>
              <div class="flex justify-center space-x-4">
                <Link href="/domains">
                  <Button variant="outline">
                    Browse All Extensions
                  </Button>
                </Link>
                <Link href="/hosting">
                  <Button>
                    View Hosting Plans
                  </Button>
                </Link>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
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