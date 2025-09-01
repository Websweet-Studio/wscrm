<script setup lang="ts">
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Server, HardDrive, Cpu, MemoryStick, Wifi, ShoppingCart, Check } from 'lucide-vue-next';
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

interface Props {
  hostingPlans: HostingPlan[];
  filters: {
    search?: string;
  };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');

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
  router.get('/customer/hosting', { search: search.value }, { 
    preserveState: true,
    replace: true 
  });
};

const orderPlan = (planId: number) => {
  router.post('/customer/orders', {
    order_type: 'hosting',
    billing_cycle: 'annual',
    items: [{
      item_type: 'hosting',
      item_id: planId,
      quantity: 1
    }]
  });
};
</script>

<template>
  <Head title="Hosting Plans" />

  <CustomerLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <div class="text-center space-y-4">
        <h1 class="text-4xl font-bold tracking-tight">Choose Your Hosting Plan</h1>
        <p class="text-xl text-muted-foreground max-w-3xl mx-auto">
          Reliable, fast, and secure hosting solutions for your websites and applications.
        </p>
      </div>

      <!-- Search -->
      <Card class="max-w-2xl mx-auto">
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

      <!-- Hosting Plans Grid -->
      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 max-w-7xl mx-auto">
        <Card v-for="plan in hostingPlans" :key="plan.id" class="relative overflow-hidden transition-all hover:shadow-lg">
          <div v-if="plan.discount_percent > 0" class="absolute top-4 right-4 z-10">
            <Badge class="bg-red-500 text-white">{{ plan.discount_percent }}% OFF</Badge>
          </div>
          
          <CardHeader class="text-center pb-4">
            <div class="flex justify-center mb-4">
              <div class="p-3 bg-blue-100 rounded-full">
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
              <div class="flex items-center justify-between p-2 bg-muted rounded">
                <div class="flex items-center space-x-2">
                  <HardDrive class="h-4 w-4 text-blue-500" />
                  <span class="text-sm">Storage</span>
                </div>
                <span class="font-semibold">{{ plan.storage_gb }}GB</span>
              </div>

              <div class="flex items-center justify-between p-2 bg-muted rounded">
                <div class="flex items-center space-x-2">
                  <Cpu class="h-4 w-4 text-green-500" />
                  <span class="text-sm">CPU Cores</span>
                </div>
                <span class="font-semibold">{{ plan.cpu_cores }}</span>
              </div>

              <div class="flex items-center justify-between p-2 bg-muted rounded">
                <div class="flex items-center space-x-2">
                  <MemoryStick class="h-4 w-4 text-purple-500" />
                  <span class="text-sm">RAM</span>
                </div>
                <span class="font-semibold">{{ plan.ram_gb }}GB</span>
              </div>

              <div class="flex items-center justify-between p-2 bg-muted rounded">
                <div class="flex items-center space-x-2">
                  <Wifi class="h-4 w-4 text-orange-500" />
                  <span class="text-sm">Bandwidth</span>
                </div>
                <span class="font-semibold">{{ plan.bandwidth }}</span>
              </div>
            </div>

            <!-- Features -->
            <div v-if="plan.features && plan.features.length > 0" class="space-y-2">
              <h4 class="font-semibold text-sm">Features Included:</h4>
              <ul class="space-y-1">
                <li v-for="(feature, index) in plan.features.slice(0, 4)" :key="index" class="flex items-center space-x-2 text-sm">
                  <Check class="h-3 w-3 text-green-500 flex-shrink-0" />
                  <span>{{ feature }}</span>
                </li>
                <li v-if="plan.features.length > 4" class="text-xs text-muted-foreground pl-5">
                  and {{ plan.features.length - 4 }} more features...
                </li>
              </ul>
            </div>

            <!-- Actions -->
            <div class="space-y-2 pt-4">
              <Button @click="orderPlan(plan.id)" class="w-full" size="lg">
                <ShoppingCart class="h-4 w-4 mr-2" />
                Order Now
              </Button>
              <Link :href="`/customer/hosting/${plan.id}`">
                <Button variant="outline" class="w-full" size="lg">
                  View Details
                </Button>
              </Link>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Empty State -->
      <div v-if="hostingPlans.length === 0" class="text-center py-12">
        <Server class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
        <h3 class="text-lg font-semibold mb-2">No hosting plans found</h3>
        <p class="text-muted-foreground">
          {{ search ? 'Try adjusting your search criteria.' : 'No hosting plans are currently available.' }}
        </p>
      </div>
    </div>
  </CustomerLayout>
</template>