<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Server, HardDrive, Cpu, MemoryStick, Wifi, ShoppingCart, Check, ArrowRight, Filter } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';

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
  hostingPlans: HostingPlan[];
  filters: {
    search?: string;
  };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const activeTab = ref('basic');

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
  router.get('/hosting', { search: search.value }, { 
    preserveState: true,
    replace: true 
  });
};

const filteredPlans = computed(() => {
  let plans = props.hostingPlans;
  
  // Filter by search
  if (search.value) {
    plans = plans.filter(plan => 
      plan.plan_name.toLowerCase().includes(search.value.toLowerCase())
    );
  }
  
  // Filter by tab (category)
  if (activeTab.value === 'basic') {
    plans = plans.filter(plan => 
      plan.plan_name.toLowerCase().includes('basic') || 
      plan.plan_name.toLowerCase().includes('starter') ||
      plan.plan_name.toLowerCase().includes('standard')
    );
  } else if (activeTab.value === 'lite') {
    plans = plans.filter(plan => 
      plan.plan_name.toLowerCase().includes('lite') || 
      plan.plan_name.toLowerCase().includes('light') ||
      plan.plan_name.toLowerCase().includes('minimal')
    );
  } else if (activeTab.value === 'premium') {
    plans = plans.filter(plan => 
      plan.plan_name.toLowerCase().includes('premium') || 
      plan.plan_name.toLowerCase().includes('pro') ||
      plan.plan_name.toLowerCase().includes('advanced')
    );
  }
  // If activeTab is 'all', no filtering by category
  
  return plans.sort((a, b) => a.selling_price - b.selling_price);
});
</script>

<template>
  <Head title="Professional Web Hosting Plans" />

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

    <!-- Hero Section -->
    <section class="container mx-auto px-6 py-16">
      <div class="text-center space-y-6 mb-12">
        <h1 class="text-4xl font-bold tracking-tight">Professional Web Hosting Plans</h1>
        <p class="text-xl text-muted-foreground max-w-3xl mx-auto">
          Choose the perfect hosting plan for your website. Fast, reliable, and secure hosting with 24/7 support.
        </p>
      </div>

      <!-- Search -->
      <Card class="max-w-2xl mx-auto mb-12">
        <CardContent class="pt-6">
          <div class="flex items-center space-x-2">
            <div class="relative flex-1">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="search"
                placeholder="Search hosting plans..."
                class="pl-10"
                @keyup.enter="handleSearch"
              />
            </div>
            <Button @click="handleSearch">Search</Button>
          </div>
        </CardContent>
      </Card>

      <!-- Plan Category Tabs -->
      <div class="max-w-6xl mx-auto mb-8">
        <Card>
          <CardContent class="pt-6">
            <div class="flex items-center justify-center">
              <div class="flex items-center space-x-1 bg-muted p-1 rounded-lg">
                <Button 
                  @click="activeTab = 'basic'"
                  :variant="activeTab === 'basic' ? 'default' : 'ghost'"
                  size="sm"
                  class="px-6"
                >
                  <Filter class="h-4 w-4 mr-2" />
                  Basic Plans
                </Button>
                <Button 
                  @click="activeTab = 'lite'"
                  :variant="activeTab === 'lite' ? 'default' : 'ghost'"
                  size="sm"
                  class="px-6"
                >
                  <Server class="h-4 w-4 mr-2" />
                  Lite Plans
                </Button>
                <Button 
                  @click="activeTab = 'premium'"
                  :variant="activeTab === 'premium' ? 'default' : 'ghost'"
                  size="sm"
                  class="px-6"
                >
                  <Check class="h-4 w-4 mr-2" />
                  Premium Plans
                </Button>
                <Button 
                  @click="activeTab = 'all'"
                  :variant="activeTab === 'all' ? 'default' : 'ghost'"
                  size="sm"
                  class="px-6"
                >
                  All Plans
                </Button>
              </div>
            </div>
            
            <!-- Active Filter Info -->
            <div class="text-center mt-4">
              <p class="text-sm text-muted-foreground">
                <span v-if="activeTab === 'basic'">Showing Basic & Standard hosting plans - perfect for personal websites</span>
                <span v-else-if="activeTab === 'lite'">Showing Lite & Minimal hosting plans - great for small projects</span>
                <span v-else-if="activeTab === 'premium'">Showing Premium & Pro hosting plans - ideal for business websites</span>
                <span v-else>Showing all available hosting plans</span>
              </p>
              <p class="text-xs text-muted-foreground mt-1">{{ filteredPlans.length }} plan(s) available</p>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Hosting Plans List -->
      <div class="max-w-6xl mx-auto">
        <Card>
          <CardHeader>
            <CardTitle class="text-center">Compare Hosting Plans</CardTitle>
            <CardDescription class="text-center">Choose the perfect plan for your needs</CardDescription>
          </CardHeader>
          <CardContent>
            <!-- Table Header -->
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b">
                    <th class="text-left py-4 px-4 font-semibold">Plan Details</th>
                    <th class="text-center py-4 px-4 font-semibold">
                      <HardDrive class="h-4 w-4 mx-auto mb-1" />
                      Storage
                    </th>
                    <th class="text-center py-4 px-4 font-semibold">
                      <Cpu class="h-4 w-4 mx-auto mb-1" />
                      CPU Cores
                    </th>
                    <th class="text-center py-4 px-4 font-semibold">
                      <MemoryStick class="h-4 w-4 mx-auto mb-1" />
                      RAM
                    </th>
                    <th class="text-center py-4 px-4 font-semibold">
                      <Wifi class="h-4 w-4 mx-auto mb-1" />
                      Bandwidth
                    </th>
                    <th class="text-center py-4 px-4 font-semibold">Pricing</th>
                    <th class="text-center py-4 px-4 font-semibold">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="plan in filteredPlans" :key="plan.id" class="border-b hover:bg-muted/50 transition-colors">
                    <!-- Plan Details -->
                    <td class="py-6 px-4">
                      <div class="space-y-2">
                        <div class="flex items-center space-x-2">
                          <div class="p-2 bg-blue-100 rounded">
                            <Server class="h-4 w-4 text-blue-600" />
                          </div>
                          <div>
                            <h3 class="font-semibold text-lg">{{ plan.plan_name }}</h3>
                            <div v-if="plan.discount_percent > 0" class="flex items-center space-x-2">
                              <Badge class="bg-red-500 text-white text-xs">{{ plan.discount_percent }}% OFF</Badge>
                            </div>
                          </div>
                        </div>
                        
                        <!-- Features Preview -->
                        <div v-if="plan.features && plan.features.length > 0" class="text-xs text-muted-foreground">
                          <div class="flex flex-wrap gap-1">
                            <span v-for="(feature, index) in plan.features.slice(0, 2)" :key="index" 
                                  class="inline-flex items-center">
                              <Check class="h-3 w-3 text-green-500 mr-1" />
                              {{ feature }}
                              <span v-if="index < 1 && plan.features.length > 1" class="mx-1">•</span>
                            </span>
                            <span v-if="plan.features.length > 2" class="text-blue-600">
                              +{{ plan.features.length - 2 }} more
                            </span>
                          </div>
                        </div>
                      </div>
                    </td>

                    <!-- Storage -->
                    <td class="py-6 px-4 text-center">
                      <div class="font-semibold text-lg">{{ plan.storage_gb }}GB</div>
                      <div class="text-xs text-muted-foreground">SSD Storage</div>
                    </td>

                    <!-- CPU -->
                    <td class="py-6 px-4 text-center">
                      <div class="font-semibold text-lg">{{ plan.cpu_cores }}</div>
                      <div class="text-xs text-muted-foreground">vCPU Cores</div>
                    </td>

                    <!-- RAM -->
                    <td class="py-6 px-4 text-center">
                      <div class="font-semibold text-lg">{{ plan.ram_gb }}GB</div>
                      <div class="text-xs text-muted-foreground">Memory</div>
                    </td>

                    <!-- Bandwidth -->
                    <td class="py-6 px-4 text-center">
                      <div class="font-semibold text-lg">{{ plan.bandwidth }}</div>
                      <div class="text-xs text-muted-foreground">Transfer</div>
                    </td>

                    <!-- Pricing -->
                    <td class="py-6 px-4 text-center">
                      <div class="space-y-1">
                        <div v-if="plan.discount_percent > 0" class="text-sm text-muted-foreground line-through">
                          {{ formatPrice(plan.selling_price) }}
                        </div>
                        <div class="text-2xl font-bold text-blue-600">
                          {{ formatPrice(getDiscountedPrice(plan.selling_price, plan.discount_percent)) }}
                        </div>
                        <div class="text-xs text-muted-foreground">per year</div>
                        <div v-if="plan.discount_percent > 0" class="text-xs text-green-600 font-semibold">
                          Save {{ formatPrice(plan.selling_price - getDiscountedPrice(plan.selling_price, plan.discount_percent)) }}/year
                        </div>
                      </div>
                    </td>

                    <!-- Action -->
                    <td class="py-6 px-4 text-center">
                      <div class="space-y-2">
                        <Button asChild size="sm" class="w-full">
                          <Link href="/customer/register">
                            <ShoppingCart class="h-4 w-4 mr-2" />
                            Get Started
                          </Link>
                        </Button>
                        <Button variant="outline" asChild size="sm" class="w-full">
                          <Link href="/customer/login">
                            Order Now
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
      </div>

      <!-- Empty State -->
      <div v-if="filteredPlans.length === 0" class="text-center py-12">
        <Server class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
        <h3 class="text-lg font-semibold mb-2">No hosting plans found</h3>
        <p class="text-muted-foreground">
          {{ search ? 'Try adjusting your search criteria or switch to another category.' : `No ${activeTab === 'all' ? '' : activeTab + ' '}hosting plans are currently available.` }}
        </p>
        <div class="mt-4">
          <Button @click="activeTab = 'all'" variant="outline" v-if="activeTab !== 'all'">
            View All Plans
          </Button>
        </div>
      </div>

      <!-- CTA Section -->
      <div class="text-center pt-24 mt-16">
        <h2 class="text-3xl font-bold mb-6">Ready to Get Started?</h2>
        <p class="text-muted-foreground mb-8 max-w-2xl mx-auto">
          Join thousands of satisfied customers who trust us with their hosting needs.
        </p>
        <div class="flex justify-center space-x-4">
          <Button asChild size="lg">
            <Link href="/customer/register">Create Account</Link>
          </Button>
          <Button variant="outline" asChild size="lg">
            <Link href="/domains">Browse Domains</Link>
          </Button>
        </div>
      </div>
    </section>

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