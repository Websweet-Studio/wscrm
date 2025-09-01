<script setup lang="ts">
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Server, HardDrive, Cpu, MemoryStick, Wifi, ShoppingCart, Check, Star, Shield, Zap } from 'lucide-vue-next';

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
  hostingPlan: HostingPlan;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/customer/dashboard' },
  { title: 'Hosting Plans', href: '/customer/hosting' },
  { title: props.hostingPlan.plan_name, href: `/customer/hosting/${props.hostingPlan.id}` },
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

const orderPlan = () => {
  router.post('/customer/orders', {
    order_type: 'hosting',
    billing_cycle: 'annual',
    items: [{
      item_type: 'hosting',
      item_id: props.hostingPlan.id,
      quantity: 1
    }]
  });
};
</script>

<template>
  <Head :title="`${hostingPlan.plan_name} - Hosting Plan`" />

  <CustomerLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6 max-w-6xl mx-auto">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Link href="/customer/hosting">
            <Button variant="outline" size="sm">
              <ArrowLeft class="h-4 w-4 mr-2" />
              Back to Plans
            </Button>
          </Link>
          <div>
            <h1 class="text-3xl font-bold tracking-tight flex items-center">
              <Server class="h-8 w-8 mr-3 text-blue-500" />
              {{ hostingPlan.plan_name }}
            </h1>
            <p class="text-muted-foreground">Professional hosting solution</p>
          </div>
        </div>
        <div v-if="hostingPlan.discount_percent > 0">
          <Badge class="bg-red-500 text-white text-lg px-3 py-1">{{ hostingPlan.discount_percent }}% OFF</Badge>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Plan Overview -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center">
                <Star class="h-5 w-5 mr-2 text-yellow-500" />
                Plan Overview
              </CardTitle>
              <CardDescription>
                Everything you need for a powerful hosting experience
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div class="flex items-center justify-between p-4 bg-muted rounded-lg">
                    <div class="flex items-center space-x-3">
                      <HardDrive class="h-6 w-6 text-blue-500" />
                      <div>
                        <h3 class="font-semibold">Storage</h3>
                        <p class="text-sm text-muted-foreground">SSD Storage</p>
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="text-xl font-bold">{{ hostingPlan.storage_gb }}GB</div>
                    </div>
                  </div>

                  <div class="flex items-center justify-between p-4 bg-muted rounded-lg">
                    <div class="flex items-center space-x-3">
                      <Cpu class="h-6 w-6 text-green-500" />
                      <div>
                        <h3 class="font-semibold">CPU Cores</h3>
                        <p class="text-sm text-muted-foreground">Processing Power</p>
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="text-xl font-bold">{{ hostingPlan.cpu_cores }}</div>
                    </div>
                  </div>
                </div>

                <div class="space-y-4">
                  <div class="flex items-center justify-between p-4 bg-muted rounded-lg">
                    <div class="flex items-center space-x-3">
                      <MemoryStick class="h-6 w-6 text-purple-500" />
                      <div>
                        <h3 class="font-semibold">RAM Memory</h3>
                        <p class="text-sm text-muted-foreground">System Memory</p>
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="text-xl font-bold">{{ hostingPlan.ram_gb }}GB</div>
                    </div>
                  </div>

                  <div class="flex items-center justify-between p-4 bg-muted rounded-lg">
                    <div class="flex items-center space-x-3">
                      <Wifi class="h-6 w-6 text-orange-500" />
                      <div>
                        <h3 class="font-semibold">Bandwidth</h3>
                        <p class="text-sm text-muted-foreground">Data Transfer</p>
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="text-lg font-bold">{{ hostingPlan.bandwidth }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Features -->
          <Card v-if="hostingPlan.features && hostingPlan.features.length > 0">
            <CardHeader>
              <CardTitle class="flex items-center">
                <Check class="h-5 w-5 mr-2 text-green-500" />
                Features Included
              </CardTitle>
              <CardDescription>
                All the features you need for a successful website
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div 
                  v-for="(feature, index) in hostingPlan.features" 
                  :key="index"
                  class="flex items-center space-x-2 p-3 bg-muted rounded-lg"
                >
                  <Check class="h-4 w-4 text-green-500 flex-shrink-0" />
                  <span>{{ feature }}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Why Choose This Plan -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center">
                <Shield class="h-5 w-5 mr-2 text-blue-500" />
                Why Choose {{ hostingPlan.plan_name }}?
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center space-y-2">
                  <div class="p-3 bg-blue-100 rounded-full w-fit mx-auto">
                    <Zap class="h-6 w-6 text-blue-600" />
                  </div>
                  <h3 class="font-semibold">Lightning Fast</h3>
                  <p class="text-sm text-muted-foreground">
                    Optimized servers with SSD storage for maximum speed
                  </p>
                </div>
                <div class="text-center space-y-2">
                  <div class="p-3 bg-green-100 rounded-full w-fit mx-auto">
                    <Shield class="h-6 w-6 text-green-600" />
                  </div>
                  <h3 class="font-semibold">99.9% Uptime</h3>
                  <p class="text-sm text-muted-foreground">
                    Reliable infrastructure with guaranteed uptime
                  </p>
                </div>
                <div class="text-center space-y-2">
                  <div class="p-3 bg-purple-100 rounded-full w-fit mx-auto">
                    <Server class="h-6 w-6 text-purple-600" />
                  </div>
                  <h3 class="font-semibold">24/7 Support</h3>
                  <p class="text-sm text-muted-foreground">
                    Expert technical support whenever you need it
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar - Order Card -->
        <div class="space-y-6">
          <Card class="sticky top-6">
            <CardHeader class="text-center">
              <CardTitle class="text-2xl">{{ hostingPlan.plan_name }}</CardTitle>
              <div class="space-y-2">
                <div v-if="hostingPlan.discount_percent > 0" class="text-lg text-muted-foreground line-through">
                  {{ formatPrice(hostingPlan.selling_price) }}
                </div>
                <div class="text-3xl font-bold text-blue-600">
                  {{ formatPrice(getDiscountedPrice(hostingPlan.selling_price, hostingPlan.discount_percent)) }}
                </div>
                <div class="text-sm text-muted-foreground">per year</div>
                <div v-if="hostingPlan.discount_percent > 0" class="text-sm text-green-600 font-semibold">
                  Save {{ formatPrice(hostingPlan.selling_price - getDiscountedPrice(hostingPlan.selling_price, hostingPlan.discount_percent)) }}/year
                </div>
              </div>
            </CardHeader>
            <CardContent class="space-y-4">
              <Button @click="orderPlan" class="w-full" size="lg">
                <ShoppingCart class="h-5 w-5 mr-2" />
                Order Now
              </Button>
              
              <div class="text-center space-y-2 pt-4 border-t">
                <div class="text-sm text-muted-foreground">30-day money back guarantee</div>
                <div class="text-sm text-muted-foreground">Free migration assistance</div>
                <div class="text-sm text-muted-foreground">No setup fees</div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </CustomerLayout>
</template>