<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Customer {
  id: number;
  name: string;
  email: string;
  phone?: string;
  status: string;
}

interface HostingPlan {
  id: number;
  plan_name: string;
  storage_gb: number;
}

interface Service {
  id: number;
  service_type: string;
  domain_name: string;
  status: string;
  expires_at: string;
  hosting_plan?: HostingPlan;
}

interface OrderItem {
  id: number;
  item_type: string;
  domain_name: string | null;
  price: number;
}

interface Order {
  id: number;
  total_amount: number;
  status: string;
  created_at: string;
  order_items: OrderItem[];
}

interface Invoice {
  id: number;
  invoice_number: string;
  amount: number;
  status: string;
  due_date: string;
}

interface Props {
  customer: Customer;
  services: Service[];
  recentOrders: Order[];
  unpaidInvoices: Invoice[];
}

const props = defineProps<Props>();

const logoutForm = useForm({});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Customer Dashboard', href: '/customer/dashboard' },
];

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price);
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const getStatusColor = (status: string) => {
  switch (status) {
    case 'active': case 'completed': case 'paid':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    case 'pending': case 'processing':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
    case 'suspended': case 'overdue':
      return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
    case 'terminated': case 'cancelled':
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
  }
};

const logout = () => {
  logoutForm.post(route('customer.logout'));
};
</script>

<template>
  <Head title="Customer Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Welcome, {{ customer.name }}</h1>
          <p class="text-muted-foreground">{{ customer.email }}</p>
        </div>
        <Button variant="outline" @click="logout">
          Logout
        </Button>
      </div>

      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active Services</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ services.filter(s => s.status === 'active').length }}</div>
            <p class="text-xs text-muted-foreground">
              {{ services.length }} total services
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Recent Orders</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ recentOrders.length }}</div>
            <p class="text-xs text-muted-foreground">
              Last 5 orders
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Unpaid Invoices</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ unpaidInvoices.length }}</div>
            <p class="text-xs text-muted-foreground">
              Need attention
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Account Status</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold capitalize">{{ customer.status }}</div>
            <span :class="`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium mt-1 ${getStatusColor(customer.status)}`">
              {{ customer.status }}
            </span>
          </CardContent>
        </Card>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <!-- Recent Services -->
        <Card>
          <CardHeader>
            <CardTitle>Recent Services</CardTitle>
            <CardDescription>Your latest hosting and domain services</CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="services.length === 0" class="text-center py-4 text-muted-foreground">
              No services yet. <Link href="/hosting" class="text-primary hover:underline">Browse hosting plans</Link>
            </div>
            <div v-else class="space-y-3">
              <div 
                v-for="service in services" 
                :key="service.id"
                class="flex items-center justify-between py-2 px-3 bg-muted/30 rounded-md"
              >
                <div>
                  <div class="font-medium">{{ service.domain_name }}</div>
                  <div class="text-sm text-muted-foreground">
                    {{ service.service_type }}
                    <span v-if="service.hosting_plan"> - {{ service.hosting_plan.plan_name }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <span :class="`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${getStatusColor(service.status)}`">
                    {{ service.status }}
                  </span>
                  <div class="text-xs text-muted-foreground mt-1">
                    Expires {{ formatDate(service.expires_at) }}
                  </div>
                </div>
              </div>
            </div>
            <div v-if="services.length > 0" class="mt-4">
              <Button variant="outline" size="sm" asChild>
                <Link :href="route('customer.services.index')">View All Services</Link>
              </Button>
            </div>
          </CardContent>
        </Card>

        <!-- Recent Orders -->
        <Card>
          <CardHeader>
            <CardTitle>Recent Orders</CardTitle>
            <CardDescription>Your order history and status</CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="recentOrders.length === 0" class="text-center py-4 text-muted-foreground">
              No orders yet. <Link href="/hosting" class="text-primary hover:underline">Start shopping</Link>
            </div>
            <div v-else class="space-y-3">
              <div 
                v-for="order in recentOrders" 
                :key="order.id"
                class="flex items-center justify-between py-2 px-3 bg-muted/30 rounded-md"
              >
                <div>
                  <div class="font-medium">Order #{{ order.id }}</div>
                  <div class="text-sm text-muted-foreground">
                    {{ formatDate(order.created_at) }}
                  </div>
                </div>
                <div class="text-right">
                  <div class="font-medium">{{ formatPrice(order.total_amount) }}</div>
                  <span :class="`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${getStatusColor(order.status)}`">
                    {{ order.status }}
                  </span>
                </div>
              </div>
            </div>
            <div v-if="recentOrders.length > 0" class="mt-4">
              <Button variant="outline" size="sm" asChild>
                <Link :href="route('customer.orders.index')">View All Orders</Link>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Unpaid Invoices Alert -->
      <Card v-if="unpaidInvoices.length > 0" class="border-orange-200 bg-orange-50 dark:border-orange-800 dark:bg-orange-950">
        <CardHeader>
          <CardTitle class="text-orange-800 dark:text-orange-200">Unpaid Invoices</CardTitle>
          <CardDescription class="text-orange-700 dark:text-orange-300">
            You have {{ unpaidInvoices.length }} unpaid invoice(s) that need your attention
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-2">
            <div 
              v-for="invoice in unpaidInvoices" 
              :key="invoice.id"
              class="flex items-center justify-between py-2 px-3 bg-white/50 dark:bg-black/20 rounded-md"
            >
              <div>
                <Link 
                  :href="`/customer/invoices/${invoice.id}`"
                  class="font-medium hover:underline"
                >
                  {{ invoice.invoice_number }}
                </Link>
                <div class="text-sm text-muted-foreground">Due {{ formatDate(invoice.due_date) }}</div>
              </div>
              <div class="text-right">
                <div class="font-bold text-orange-800 dark:text-orange-200">{{ formatPrice(invoice.amount) }}</div>
                <Link 
                  :href="`/customer/invoices/${invoice.id}/payment`"
                  class="text-xs text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-200"
                >
                  Pay Now
                </Link>
              </div>
            </div>
          </div>
          <div class="mt-4 flex gap-2">
            <Button 
              :as="Link" 
              href="/customer/invoices" 
              variant="outline" 
              size="sm"
            >
              View All Invoices
            </Button>
            <Button 
              v-if="unpaidInvoices.length > 0"
              :as="Link" 
              :href="`/customer/invoices/${unpaidInvoices[0].id}/payment`"
              size="sm"
            >
              Pay Now
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>