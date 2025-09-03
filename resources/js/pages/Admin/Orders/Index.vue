<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Search, Plus, Edit, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Customer {
  id: number;
  name: string;
  email: string;
}

interface HostingPlan {
  id: number;
  plan_name: string;
  selling_price: number;
}

interface DomainPrice {
  id: number;
  extension: string;
  register_price: number;
}

interface ServicePlan {
  id: number;
  name: string;
  category: string;
  price: number;
  description: string;
}

interface OrderItem {
  id: number;
  item_type: string;
  domain_name: string | null;
  quantity: number;
  price: number;
}

interface Order {
  id: number;
  total_amount: number;
  status: 'pending' | 'processing' | 'completed' | 'cancelled';
  billing_cycle: string;
  created_at: string;
  customer: Customer;
  order_items: OrderItem[];
}

interface Props {
  orders: {
    data: Order[];
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
  customers: Customer[];
  hostingPlans: HostingPlan[];
  domainPrices: DomainPrice[];
  servicePlans: ServicePlan[];
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedOrder = ref<Order | null>(null);

const createForm = useForm({
  customer_id: '',
  order_type: 'domain' as 'domain' | 'hosting' | 'domain_hosting' | 'app' | 'web' | 'domain_hosting_app_web' | 'maintenance',
  billing_cycle: 'monthly' as 'monthly' | 'quarterly' | 'semi_annually' | 'annually',
  items: [{
    item_type: 'hosting' as 'hosting' | 'domain' | 'service' | 'app' | 'web' | 'maintenance',
    item_id: '',
    domain_name: '',
    quantity: 1,
  }],
});

const editForm = useForm({
  status: 'pending' as 'pending' | 'processing' | 'completed' | 'cancelled',
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Orders', href: '/admin/orders' },
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
    case 'completed': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    case 'processing': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
    case 'pending': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
    case 'cancelled': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
  }
};

const totalRevenue = props.orders.data
  .filter(order => order.status === 'completed')
  .reduce((sum, order) => sum + order.total_amount, 0);

const handleSearch = () => {
  router.get('/admin/orders', { 
    search: search.value, 
    status: statusFilter.value 
  }, { 
    preserveState: true,
    replace: true 
  });
};

const addItem = () => {
  createForm.items.push({
    item_type: 'hosting',
    item_id: '',
    domain_name: '',
    quantity: 1,
  });
};

const removeItem = (index: number) => {
  createForm.items.splice(index, 1);
};

const submitCreate = () => {
  createForm.post('/admin/orders', {
    onSuccess: () => {
      showCreateModal.value = false;
      createForm.reset();
      createForm.items = [{
        item_type: 'hosting',
        item_id: '',
        domain_name: '',
        quantity: 1,
      }];
    },
  });
};

const openEditModal = (order: Order) => {
  selectedOrder.value = order;
  editForm.reset();
  editForm.status = order.status;
  showEditModal.value = true;
};

const submitEdit = () => {
  if (!selectedOrder.value) return;
  
  editForm.put(`/admin/orders/${selectedOrder.value.id}`, {
    onSuccess: () => {
      showEditModal.value = false;
      editForm.reset();
      selectedOrder.value = null;
    },
  });
};

const deleteOrder = (order: Order) => {
  if (confirm(`Are you sure you want to delete Order #${order.id}?`)) {
    router.delete(`/admin/orders/${order.id}`);
  }
};
</script>

<template>
  <Head title="Admin - Orders" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Order Management</h1>
          <p class="text-muted-foreground">Track and manage customer orders</p>
        </div>
        <Button @click="showCreateModal = true">
          <Plus class="h-4 w-4 mr-2" />
          Add Order
        </Button>
      </div>

      <div class="grid gap-4 md:grid-cols-5">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Orders</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ orders.total }}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Completed</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-green-600">
              {{ orders.data.filter(o => o.status === 'completed').length }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Processing</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-blue-600">
              {{ orders.data.filter(o => o.status === 'processing').length }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Pending</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-yellow-600">
              {{ orders.data.filter(o => o.status === 'pending').length }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Revenue</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-xl font-bold">{{ formatPrice(totalRevenue) }}</div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>All Orders</CardTitle>
          <CardDescription>Complete list of customer orders</CardDescription>
        </CardHeader>
        <CardContent>
          <!-- Search and Filter -->
          <div class="mb-6 flex flex-col sm:flex-row gap-4">
            <div class="relative flex-1 max-w-sm">
              <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="search"
                placeholder="Search orders..."
                class="pl-8"
                @keyup.enter="handleSearch"
              />
            </div>
            <select 
              v-model="statusFilter" 
              class="flex h-9 w-[180px] rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
            >
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="processing">Processing</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <Button @click="handleSearch">Search</Button>
          </div>

          <div class="space-y-4">
            <div v-if="orders.data.length === 0" class="text-center py-8 text-muted-foreground">
              No orders found.
            </div>
            
            <div v-else class="space-y-4">
              <div 
                v-for="order in orders.data" 
                :key="order.id"
                class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/30"
              >
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-2">
                    <h3 class="font-semibold">Order #{{ order.id }}</h3>
                    <Badge :class="getStatusColor(order.status)">
                      {{ order.status }}
                    </Badge>
                  </div>
                  <div class="text-sm text-muted-foreground space-y-1">
                    <div><strong>Customer:</strong> {{ order.customer.name }} ({{ order.customer.email }})</div>
                    <div><strong>Items:</strong> {{ order.order_items.length }} item(s)</div>
                    <div><strong>Billing:</strong> {{ order.billing_cycle.replace('_', ' ') }}</div>
                    <div><strong>Date:</strong> {{ formatDate(order.created_at) }}</div>
                  </div>
                </div>

                <div class="flex items-center gap-4">
                  <div class="text-right">
                    <div class="text-xl font-bold">{{ formatPrice(order.total_amount) }}</div>
                    <div class="text-sm text-muted-foreground">Total</div>
                  </div>
                  <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" asChild>
                      <Link :href="`/admin/orders/${order.id}`">
                        View Details
                      </Link>
                    </Button>
                    <Button size="sm" variant="outline" @click="openEditModal(order)">
                      <Edit class="h-3 w-3" />
                    </Button>
                    <Button 
                      size="sm" 
                      variant="outline" 
                      @click="deleteOrder(order)"
                      :disabled="order.status === 'completed'"
                    >
                      <Trash2 class="h-3 w-3" />
                    </Button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="orders.links && orders.links.length > 3" class="flex items-center justify-between pt-6 border-t">
              <div class="text-sm text-muted-foreground">
                Showing {{ ((orders.current_page - 1) * orders.per_page + 1) || 0 }} to 
                {{ Math.min(orders.current_page * orders.per_page, orders.total) || 0 }} of 
                {{ orders.total || 0 }} results
              </div>
              <div class="flex items-center gap-1">
                <template v-for="link in orders.links" :key="link.label">
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
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Create Order Modal -->
    <Dialog :open="showCreateModal" @update:open="showCreateModal = $event">
      <DialogContent class="sm:max-w-[600px]">
        <DialogHeader>
          <DialogTitle>Create New Order</DialogTitle>
          <DialogDescription>
            Create a new order for a customer with multiple items and services.
          </DialogDescription>
        </DialogHeader>
        <form @submit.prevent="submitCreate" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="create-customer">Customer *</Label>
              <select 
                id="create-customer"
                v-model="createForm.customer_id"
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                required
              >
                <option value="">Select Customer</option>
                <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                  {{ customer.name }} ({{ customer.email }})
                </option>
              </select>
              <p v-if="createForm.errors.customer_id" class="text-xs text-red-500 mt-1">{{ createForm.errors.customer_id }}</p>
            </div>
            <div>
              <Label for="create-order-type">Order Type *</Label>
              <select 
                id="create-order-type"
                v-model="createForm.order_type"
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                required
              >
                <option value="domain">Domain</option>
                <option value="hosting">Hosting</option>
                <option value="domain_hosting">Domain + Hosting</option>
                <option value="app">App</option>
                <option value="web">Web</option>
                <option value="domain_hosting_app_web">Domain + Hosting + App/Web</option>
                <option value="maintenance">Maintenance</option>
              </select>
              <p v-if="createForm.errors.order_type" class="text-xs text-red-500 mt-1">{{ createForm.errors.order_type }}</p>
            </div>
          </div>

          <div>
            <Label for="create-billing-cycle">Billing Cycle *</Label>
            <select 
              id="create-billing-cycle"
              v-model="createForm.billing_cycle"
              class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
              required
            >
              <option value="monthly">Monthly</option>
              <option value="quarterly">Quarterly</option>
              <option value="semi_annually">Semi Annually</option>
              <option value="annually">Annually</option>
            </select>
            <p v-if="createForm.errors.billing_cycle" class="text-xs text-red-500 mt-1">{{ createForm.errors.billing_cycle }}</p>
          </div>

          <!-- Order Items -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <Label>Order Items *</Label>
              <Button type="button" size="sm" @click="addItem">
                <Plus class="h-3 w-3 mr-1" />
                Add Item
              </Button>
            </div>
            
            <div v-for="(item, index) in createForm.items" :key="index" class="border rounded-md p-3 space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium">Item {{ index + 1 }}</span>
                <Button 
                  v-if="createForm.items.length > 1"
                  type="button" 
                  size="sm" 
                  variant="outline" 
                  @click="removeItem(index)"
                >
                  <Trash2 class="h-3 w-3" />
                </Button>
              </div>
              
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <Label>Item Type</Label>
                  <select 
                    v-model="item.item_type"
                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                  >
                    <option value="hosting">Hosting</option>
                    <option value="domain">Domain</option>
                    <option value="service">Service Plan</option>
                    <option value="app">App</option>
                    <option value="web">Web</option>
                    <option value="maintenance">Maintenance</option>
                  </select>
                </div>
                <div>
                  <Label>Item</Label>
                  <select 
                    v-model="item.item_id"
                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                  >
                    <option value="">Select Item</option>
                    <template v-if="item.item_type === 'hosting'">
                      <option v-for="plan in hostingPlans" :key="plan.id" :value="plan.id">
                        {{ plan.plan_name }} - {{ formatPrice(plan.selling_price) }}
                      </option>
                    </template>
                    <template v-if="item.item_type === 'domain'">
                      <option v-for="domain in domainPrices" :key="domain.id" :value="domain.id">
                        .{{ domain.extension }} - {{ formatPrice(domain.register_price) }}
                      </option>
                    </template>
                    <template v-if="item.item_type === 'service'">
                      <option v-for="service in servicePlans" :key="service.id" :value="service.id">
                        {{ service.name }} - {{ service.price > 0 ? formatPrice(service.price) : 'Custom Price' }}
                      </option>
                    </template>
                    <template v-if="item.item_type === 'app'">
                      <option value="1">Mobile App Development - {{ formatPrice(1500000) }}</option>
                      <option value="2">Web App Development - {{ formatPrice(2000000) }}</option>
                      <option value="3">Custom App - {{ formatPrice(2500000) }}</option>
                    </template>
                    <template v-if="item.item_type === 'web'">
                      <option value="1">Basic Website - {{ formatPrice(500000) }}</option>
                      <option value="2">Business Website - {{ formatPrice(1000000) }}</option>
                      <option value="3">E-commerce Website - {{ formatPrice(2000000) }}</option>
                    </template>
                    <template v-if="item.item_type === 'maintenance'">
                      <option value="1">Basic Maintenance - {{ formatPrice(200000) }}/month</option>
                      <option value="2">Advanced Maintenance - {{ formatPrice(500000) }}/month</option>
                      <option value="3">Premium Maintenance - {{ formatPrice(1000000) }}/month</option>
                    </template>
                  </select>
                </div>
              </div>
              
              <div class="grid grid-cols-2 gap-3">
                <div v-if="item.item_type === 'domain'">
                  <Label>Domain Name</Label>
                  <Input
                    v-model="item.domain_name"
                    placeholder="example.com"
                  />
                </div>
                <div>
                  <Label>Quantity</Label>
                  <Input
                    v-model="item.quantity"
                    type="number"
                    min="1"
                  />
                </div>
              </div>
            </div>
            <p v-if="createForm.errors.items" class="text-xs text-red-500 mt-1">{{ createForm.errors.items }}</p>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="showCreateModal = false">
              Cancel
            </Button>
            <Button type="submit" :disabled="createForm.processing">
              {{ createForm.processing ? 'Creating...' : 'Create Order' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Edit Order Modal -->
    <Dialog :open="showEditModal" @update:open="showEditModal = $event">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Edit Order Status</DialogTitle>
          <DialogDescription>
            Update the status of this order to track its progress.
          </DialogDescription>
        </DialogHeader>
        <form @submit.prevent="submitEdit" class="space-y-4">
          <div>
            <Label for="edit-status">Status *</Label>
            <select 
              id="edit-status"
              v-model="editForm.status"
              class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
              required
            >
              <option value="pending">Pending</option>
              <option value="processing">Processing</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <p v-if="editForm.errors.status" class="text-xs text-red-500 mt-1">{{ editForm.errors.status }}</p>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="showEditModal = false">
              Cancel
            </Button>
            <Button type="submit" :disabled="editForm.processing">
              {{ editForm.processing ? 'Updating...' : 'Update Status' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>