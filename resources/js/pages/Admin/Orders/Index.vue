<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Search, Plus, Edit, Trash2, X, ShoppingCart, Package, Clock, CheckCircle } from 'lucide-vue-next';
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
  storage_gb: number;
  cpu_cores: number;
  ram_gb: number;
}

interface DomainPrice {
  id: number;
  extension: string;
  selling_price: number;
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
const showDeleteModal = ref(false);
const selectedOrder = ref<Order | null>(null);
const orderToDelete = ref<Order | null>(null);

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
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Pesanan', href: '/admin/orders' },
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

const getStatusText = (status: string) => {
  switch (status) {
    case 'completed': return 'Selesai';
    case 'processing': return 'Diproses';
    case 'pending': return 'Menunggu';
    case 'cancelled': return 'Dibatalkan';
    default: return status;
  }
};

const totalRevenue = props.orders.data
  .filter(order => order.status === 'completed')
  .reduce((sum, order) => sum + (parseFloat(order.total_amount) || 0), 0);

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
  console.log('Submitting create form...', createForm.data());
  
  // Get fresh CSRF token
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  console.log('Using CSRF token:', csrfToken);
  
  createForm.transform((data) => ({
    ...data,
    _token: csrfToken
  })).post('/admin/orders', {
    headers: {
      'X-CSRF-TOKEN': csrfToken
    },
    onSuccess: () => {
      console.log('Order created successfully');
      showCreateModal.value = false;
      createForm.reset();
      createForm.items = [{
        item_type: 'hosting',
        item_id: '',
        domain_name: '',
        quantity: 1,
      }];
    },
    onError: (errors) => {
      console.error('Create order error:', errors);
      // If CSRF error, reload page
      if (errors[419] || Object.values(errors).some(e => String(e).includes('419'))) {
        console.warn('CSRF error detected, reloading...');
        window.location.reload();
      }
    },
    onFinish: () => {
      console.log('Create request finished');
    }
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
    preserveState: false,
    preserveScroll: true,
    onSuccess: () => {
      showEditModal.value = false;
      editForm.reset();
      selectedOrder.value = null;
    },
    onError: (errors) => {
      console.error('Update order error:', errors);
    },
  });
};

const openDeleteModal = (order: Order) => {
  orderToDelete.value = order;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  if (!orderToDelete.value) return;
  
  router.delete(`/admin/orders/${orderToDelete.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      orderToDelete.value = null;
    },
    onError: (errors) => {
      console.error('Delete order error:', errors);
    },
  });
};
</script>

<template>
  <Head title="Admin - Kelola Pesanan" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Kelola Pesanan</h1>
          <p class="text-muted-foreground">Lacak dan kelola pesanan pelanggan</p>
        </div>
        <Button @click="showCreateModal = true" class="cursor-pointer">
          <Plus class="h-4 w-4 mr-2" />
          Tambah Pesanan
        </Button>
      </div>

      <!-- Statistics Cards -->
      <div class="grid gap-4 md:grid-cols-5">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Pesanan</CardTitle>
            <ShoppingCart class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ orders.total }}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Selesai</CardTitle>
            <CheckCircle class="h-4 w-4 text-green-600" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-green-600">
              {{ orders.data.filter(o => o.status === 'completed').length }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Diproses</CardTitle>
            <Package class="h-4 w-4 text-blue-600" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-blue-600">
              {{ orders.data.filter(o => o.status === 'processing').length }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Menunggu</CardTitle>
            <Clock class="h-4 w-4 text-yellow-600" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-yellow-600">
              {{ orders.data.filter(o => o.status === 'pending').length }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Pendapatan</CardTitle>
            <Package class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-xl font-bold">{{ formatPrice(totalRevenue) }}</div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Semua Pesanan</CardTitle>
          <CardDescription>Daftar lengkap pesanan pelanggan</CardDescription>
        </CardHeader>
        <CardContent>
          <!-- Search and Filter -->
          <div class="mb-6 flex flex-col sm:flex-row gap-4">
            <div class="relative flex-1 max-w-sm">
              <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="search"
                placeholder="Cari pesanan..."
                class="pl-8"
                @keyup.enter="handleSearch"
              />
            </div>
            <select 
              v-model="statusFilter" 
              class="flex h-9 w-[180px] rounded-md border border-input bg-background dark:bg-gray-800 px-3 py-1 text-sm shadow-sm transition-colors text-foreground dark:text-white placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring cursor-pointer"
            >
              <option value="">Semua Status</option>
              <option value="pending">Menunggu</option>
              <option value="processing">Diproses</option>
              <option value="completed">Selesai</option>
              <option value="cancelled">Dibatalkan</option>
            </select>
            <Button @click="handleSearch" class="cursor-pointer">Cari</Button>
          </div>

          <!-- Order Cards -->
          <div v-if="!orders?.data || orders.data.length === 0" class="text-center py-12 text-muted-foreground">
            <ShoppingCart class="mx-auto h-12 w-12 text-muted-foreground/40" />
            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">Pesanan tidak ditemukan</h3>
            <p class="mt-1 text-sm text-muted-foreground">Coba sesuaikan kriteria pencarian Anda.</p>
          </div>

          <div v-else class="space-y-4">
            <div 
              v-for="order in orders.data" 
              :key="order.id"
              class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/30 transition-colors"
            >
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                  <h3 class="text-sm font-semibold text-foreground truncate">Pesanan #{{ order.id }}</h3>
                  <span :class="`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${getStatusColor(order.status)}`">
                    {{ getStatusText(order.status) }}
                  </span>
                </div>
                <div class="space-y-1 text-xs text-muted-foreground">
                  <div class="flex items-center gap-4">
                    <span>{{ order.customer.name }}</span>
                    <span>{{ order.customer.email }}</span>
                  </div>
                  <div class="flex items-center gap-4">
                    <span>Item: {{ order.order_items.length }}</span>
                    <span>Siklus: {{ order.billing_cycle.replace('_', ' ') }}</span>
                    <span>Tanggal: {{ formatDate(order.created_at) }}</span>
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-6 ml-4">
                <!-- Amount -->
                <div class="hidden md:flex text-right">
                  <div class="text-sm font-medium text-foreground">{{ formatPrice(order.total_amount) }}</div>
                  <div class="text-xs text-muted-foreground">Total</div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                  <Button size="sm" variant="outline" asChild>
                    <Link :href="`/admin/orders/${order.id}`" class="cursor-pointer">
                      Lihat Detail
                    </Link>
                  </Button>
                  <Button size="sm" variant="outline" @click="openEditModal(order)" class="cursor-pointer">
                    <Edit class="h-3 w-3" />
                  </Button>
                  <Button 
                    size="sm" 
                    variant="outline" 
                    @click="openDeleteModal(order)"
                    :disabled="order.status === 'completed'"
                    class="cursor-pointer"
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
                  :class="link.active ? 'bg-primary text-primary-foreground cursor-pointer' : 'cursor-pointer'"
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

    <!-- Create Order Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-2xl mx-4 p-6 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-semibold">Buat Pesanan Baru</h2>
            <p class="text-sm text-muted-foreground">Buat pesanan baru untuk pelanggan dengan berbagai item dan layanan.</p>
          </div>
          <button @click="showCreateModal = false" class="text-gray-500 hover:text-gray-700 cursor-pointer">
            <X class="h-4 w-4" />
          </button>
        </div>
        <form @submit.prevent="submitCreate" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="create-customer">Pelanggan *</Label>
              <select 
                id="create-customer"
                v-model="createForm.customer_id"
                class="flex h-9 w-full rounded-md border border-input bg-background dark:bg-gray-800 px-3 py-1 text-sm shadow-sm transition-colors text-foreground dark:text-white placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring cursor-pointer"
                required
              >
                <option value="">Pilih Pelanggan</option>
                <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                  {{ customer.name }} ({{ customer.email }})
                </option>
              </select>
              <p v-if="createForm.errors.customer_id" class="text-xs text-red-500 mt-1">{{ createForm.errors.customer_id }}</p>
            </div>
            <div>
              <Label for="create-order-type">Tipe Pesanan *</Label>
              <select 
                id="create-order-type"
                v-model="createForm.order_type"
                class="flex h-9 w-full rounded-md border border-input bg-background dark:bg-gray-800 px-3 py-1 text-sm shadow-sm transition-colors text-foreground dark:text-white placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring cursor-pointer"
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
            <Label for="create-billing-cycle">Siklus Pembayaran *</Label>
            <select 
              id="create-billing-cycle"
              v-model="createForm.billing_cycle"
              class="flex h-9 w-full rounded-md border border-input bg-background dark:bg-gray-800 px-3 py-1 text-sm shadow-sm transition-colors text-foreground dark:text-white placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring cursor-pointer"
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
              <Label>Item Pesanan *</Label>
              <Button type="button" size="sm" @click="addItem" class="cursor-pointer">
                <Plus class="h-3 w-3 mr-1" />
                Tambah Item
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
                  class="cursor-pointer"
                >
                  <Trash2 class="h-3 w-3" />
                </Button>
              </div>
              
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <Label>Tipe Item</Label>
                  <select 
                    v-model="item.item_type"
                    class="flex h-9 w-full rounded-md border border-input bg-background dark:bg-gray-800 px-3 py-1 text-sm shadow-sm transition-colors text-foreground dark:text-white placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring cursor-pointer"
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
                    class="flex h-9 w-full rounded-md border border-input bg-background dark:bg-gray-800 px-3 py-1 text-sm shadow-sm transition-colors text-foreground dark:text-white placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring cursor-pointer"
                  >
                    <option value="">Pilih Item</option>
                    <template v-if="item.item_type === 'hosting'">
                      <option v-for="plan in hostingPlans" :key="plan.id" :value="plan.id">
                        {{ plan.plan_name }} - {{ plan.storage_gb }}GB Storage, {{ plan.cpu_cores }} CPU, {{ plan.ram_gb }}GB RAM - {{ formatPrice(plan.selling_price) }}
                      </option>
                    </template>
                    <template v-if="item.item_type === 'domain'">
                      <option v-for="domain in domainPrices" :key="domain.id" :value="domain.id">
                        .{{ domain.extension }} - {{ formatPrice(domain.selling_price) }}
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
                  <Label>Nama Domain</Label>
                  <Input
                    v-model="item.domain_name"
                    placeholder="example.com"
                  />
                </div>
                <div>
                  <Label>Jumlah</Label>
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

          <!-- Footer -->
          <div class="flex justify-end gap-2 mt-6">
            <Button type="button" variant="outline" @click="showCreateModal = false" class="cursor-pointer">
              Batal
            </Button>
            <Button type="submit" :disabled="createForm.processing" class="cursor-pointer">
              {{ createForm.processing ? 'Membuat...' : 'Buat Pesanan' }}
            </Button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Order Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-semibold">Edit Status Pesanan</h2>
            <p class="text-sm text-muted-foreground">Perbarui status pesanan ini untuk melacak progresnya.</p>
          </div>
          <button @click="showEditModal = false" class="text-gray-500 hover:text-gray-700 cursor-pointer">
            <X class="h-4 w-4" />
          </button>
        </div>
        <form @submit.prevent="submitEdit" class="space-y-4">
          <div>
            <Label for="edit-status">Status *</Label>
            <select 
              id="edit-status"
              v-model="editForm.status"
              class="flex h-9 w-full rounded-md border border-input bg-background dark:bg-gray-800 px-3 py-1 text-sm shadow-sm transition-colors text-foreground dark:text-white placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring cursor-pointer"
              required
            >
              <option value="pending">Menunggu</option>
              <option value="processing">Diproses</option>
              <option value="completed">Selesai</option>
              <option value="cancelled">Dibatalkan</option>
            </select>
            <p v-if="editForm.errors.status" class="text-xs text-red-500 mt-1">{{ editForm.errors.status }}</p>
          </div>

          <!-- Footer -->
          <div class="flex justify-end gap-2 mt-6">
            <Button type="button" variant="outline" @click="showEditModal = false" class="cursor-pointer">
              Batal
            </Button>
            <Button type="submit" :disabled="editForm.processing" class="cursor-pointer">
              {{ editForm.processing ? 'Memperbarui...' : 'Perbarui Status' }}
            </Button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Order Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-red-600">Konfirmasi Penghapusan</h2>
          <button @click="showDeleteModal = false" class="text-gray-500 hover:text-gray-700 cursor-pointer">
            <X class="h-4 w-4" />
          </button>
        </div>
        
        <div class="space-y-4">
          <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="min-w-0 flex-1">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                  Peringatan: Tindakan ini tidak dapat dibatalkan
                </h3>
                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                  <p>Anda akan menghapus secara permanen <strong>Pesanan #{{ orderToDelete?.id }}</strong>.</p>
                  <div class="mt-3 space-y-1">
                    <p><strong>Ini juga akan menghapus:</strong></p>
                    <ul class="list-disc list-inside space-y-1 ml-2">
                      <li>{{ orderToDelete?.order_items?.length || 0 }} item pesanan</li>
                      <li>Semua data terkait secara permanen</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
            <p class="text-sm text-gray-600 dark:text-gray-400">
              <strong>Pesanan:</strong> #{{ orderToDelete?.id }}<br>
              <strong>Pelanggan:</strong> {{ orderToDelete?.customer?.name }}<br>
              <strong>Total:</strong> {{ formatPrice(orderToDelete?.total_amount || 0) }}<br>
              <strong>Status:</strong> {{ orderToDelete?.status }}
            </p>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-2 mt-6">
          <Button type="button" variant="outline" @click="showDeleteModal = false" class="cursor-pointer">
            Batal
          </Button>
          <Button 
            type="button" 
            class="bg-red-600 hover:bg-red-700 text-white cursor-pointer" 
            @click="confirmDelete"
          >
            Ya, Hapus Pesanan
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>