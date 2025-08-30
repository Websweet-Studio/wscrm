<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Users, UserCheck, UserX, Clock } from 'lucide-vue-next';
import { ref } from 'vue';

interface Customer {
  id: number;
  name: string;
  email: string;
  phone?: string;
  city?: string;
  status: 'active' | 'inactive' | 'suspended';
  created_at: string;
  orders_count: number;
  services_count: number;
}

interface Props {
  customers?: {
    data: Customer[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: any[];
  };
  filters?: {
    search?: string;
    status?: string;
  };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Customers', href: '/admin/customers' },
];

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const handleSearch = () => {
  router.get('/admin/customers', { 
    search: search.value, 
    status: status.value 
  }, { 
    preserveState: true,
    replace: true 
  });
};

const getStatusClass = (status: string) => {
  switch (status) {
    case 'active': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    case 'suspended': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
    case 'inactive': return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
  }
};
</script>

<template>
  <Head title="Admin - Customers" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Customer Management</h1>
          <p class="text-muted-foreground">Manage customer accounts and information</p>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Customers</CardTitle>
            <Users class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ customers?.total || 0 }}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active</CardTitle>
            <UserCheck class="h-4 w-4 text-green-600" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-green-600">
              {{ customers?.data?.filter(c => c.status === 'active').length || 0 }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Suspended</CardTitle>
            <UserX class="h-4 w-4 text-red-600" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-red-600">
              {{ customers?.data?.filter(c => c.status === 'suspended').length || 0 }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Inactive</CardTitle>
            <Clock class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-muted-foreground">
              {{ customers?.data?.filter(c => c.status === 'inactive').length || 0 }}
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Customer List -->
      <Card>
        <CardHeader>
          <CardTitle>Customers</CardTitle>
          <CardDescription>Manage customer accounts and information</CardDescription>
        </CardHeader>
        <CardContent>
          <!-- Search and Filter -->
          <div class="mb-6 flex flex-col sm:flex-row gap-4">
            <div class="relative flex-1 max-w-sm">
              <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="search"
                placeholder="Search customers..."
                class="pl-8"
                @keyup.enter="handleSearch"
              />
            </div>
            <select 
              v-model="status" 
              class="flex h-9 w-[180px] rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
            >
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="suspended">Suspended</option>
            </select>
            <Button @click="handleSearch">Search</Button>
          </div>

          <!-- Customer Cards -->
          <div v-if="!customers?.data || customers.data.length === 0" class="text-center py-12 text-muted-foreground">
            <Users class="mx-auto h-12 w-12 text-muted-foreground/40" />
            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No customers found</h3>
            <p class="mt-1 text-sm text-muted-foreground">Try adjusting your search criteria.</p>
          </div>

          <div v-else class="space-y-4">
            <div 
              v-for="customer in customers.data" 
              :key="customer.id"
              class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/30 transition-colors"
            >
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                  <h3 class="text-sm font-semibold text-foreground truncate">{{ customer.name }}</h3>
                  <span :class="`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${getStatusClass(customer.status)}`">
                    {{ customer.status }}
                  </span>
                </div>
                <div class="space-y-1 text-xs text-muted-foreground">
                  <div class="flex items-center gap-4">
                    <span>{{ customer.email }}</span>
                    <span v-if="customer.phone">{{ customer.phone }}</span>
                  </div>
                  <div class="flex items-center gap-4">
                    <span>ID: #{{ customer.id }}</span>
                    <span>Joined: {{ formatDate(customer.created_at) }}</span>
                    <span v-if="customer.city">{{ customer.city }}</span>
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-6 ml-4">
                <!-- Statistics -->
                <div class="hidden md:flex items-center gap-4 text-xs text-muted-foreground">
                  <div class="text-center">
                    <div class="text-sm font-medium text-foreground">{{ customer.orders_count }}</div>
                    <div>Orders</div>
                  </div>
                  <div class="text-center">
                    <div class="text-sm font-medium text-foreground">{{ customer.services_count }}</div>
                    <div>Services</div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                  <Button size="sm" variant="outline" asChild>
                    <Link :href="`/admin/customers/${customer.id}`">
                      View Details
                    </Link>
                  </Button>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="customers?.links && customers.links.length > 3" class="flex items-center justify-between pt-6 border-t">
            <div class="text-sm text-muted-foreground">
              Showing {{ ((customers.current_page - 1) * customers.per_page + 1) || 0 }} to 
              {{ Math.min(customers.current_page * customers.per_page, customers.total) || 0 }} of 
              {{ customers.total || 0 }} results
            </div>
            <div class="flex items-center gap-1">
              <template v-for="link in customers.links" :key="link.label">
                <Button 
                  v-if="link.url" 
                  variant="outline" 
                  size="sm"
                  :disabled="!link.url"
                  :class="link.active ? 'bg-primary text-primary-foreground' : ''"
                  @click="router.visit(link.url)"
                  v-html="link.label"
                />
                <span 
                  v-else 
                  class="px-3 py-2 text-sm text-muted-foreground"
                  v-html="link.label"
                />
              </template>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>