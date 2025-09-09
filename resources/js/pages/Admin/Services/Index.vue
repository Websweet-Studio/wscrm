<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Search, Plus, Edit, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface Customer {
  id: number;
  name: string;
  email: string;
}

interface HostingPlan {
  id: number;
  plan_name: string;
  storage_gb: number;
  cpu_cores: number;
  ram_gb: number;
}

interface Service {
  id: number;
  service_type: 'hosting' | 'domain';
  domain_name: string;
  status: 'active' | 'suspended' | 'terminated' | 'pending';
  expires_at: string;
  auto_renew: boolean;
  created_at: string;
  customer: Customer;
  hosting_plan?: HostingPlan;
}

interface Props {
  services: {
    data: Service[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: any[];
  };
  filters?: {
    search?: string;
    status?: string;
    service_type?: string;
  };
  customers: Customer[];
  hostingPlans: HostingPlan[];
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const serviceTypeFilter = ref(props.filters?.service_type || '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedService = ref<Service | null>(null);
const serviceToDelete = ref<Service | null>(null);

const createForm = useForm({
  customer_id: '',
  service_type: 'hosting' as 'hosting' | 'domain',
  plan_id: '',
  domain_name: '',
  expires_at: '',
  auto_renew: false,
});

const editForm = useForm({
  status: 'active' as 'active' | 'suspended' | 'terminated' | 'pending',
  domain_name: '',
  expires_at: '',
  auto_renew: false,
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Layanan', href: '/admin/services' },
];

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const getStatusColor = (status: string) => {
  switch (status) {
    case 'active': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    case 'pending': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
    case 'suspended': return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
    case 'terminated': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
  }
};

const getStatusText = (status: string) => {
  switch (status) {
    case 'active': return 'Aktif';
    case 'pending': return 'Menunggu';
    case 'suspended': return 'Ditangguhkan';
    case 'terminated': return 'Dihentikan';
    default: return status;
  }
};

const getServiceTypeText = (serviceType: string) => {
  switch (serviceType) {
    case 'hosting': return 'Hosting';
    case 'domain': return 'Domain';
    default: return serviceType;
  }
};

const isExpiringSoon = (expiresAt: string) => {
  const expiryDate = new Date(expiresAt);
  const thirtyDaysFromNow = new Date();
  thirtyDaysFromNow.setDate(thirtyDaysFromNow.getDate() + 30);
  return expiryDate <= thirtyDaysFromNow;
};

const expiringSoon = props.services.data.filter(s => 
  s.status === 'active' && isExpiringSoon(s.expires_at)
).length;

const handleSearch = () => {
  router.get('/admin/services', { 
    search: search.value, 
    status: statusFilter.value,
    service_type: serviceTypeFilter.value 
  }, { 
    preserveState: true,
    replace: true 
  });
};

const submitCreate = () => {
  createForm.post('/admin/services', {
    onSuccess: () => {
      showCreateModal.value = false;
      createForm.reset();
    },
  });
};

const openEditModal = (service: Service) => {
  selectedService.value = service;
  editForm.reset();
  editForm.status = service.status;
  editForm.domain_name = service.domain_name;
  editForm.expires_at = service.expires_at;
  editForm.auto_renew = service.auto_renew;
  showEditModal.value = true;
};

const submitEdit = () => {
  if (!selectedService.value) return;
  
  editForm.put(`/admin/services/${selectedService.value.id}`, {
    onSuccess: () => {
      showEditModal.value = false;
      editForm.reset();
      selectedService.value = null;
    },
  });
};

const openDeleteModal = (service: Service) => {
  serviceToDelete.value = service;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  if (!serviceToDelete.value) return;
  
  router.delete(`/admin/services/${serviceToDelete.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      serviceToDelete.value = null;
    },
    onError: (errors) => {
      console.error('Delete service error:', errors);
    },
  });
};
</script>

<template>
  <Head title="Admin - Kelola Layanan" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Kelola Layanan</h1>
          <p class="text-muted-foreground">Kelola layanan hosting dan domain pelanggan</p>
        </div>
        <Button @click="showCreateModal = true" class="cursor-pointer">
          <Plus class="h-4 w-4 mr-2" />
          Tambah Layanan
        </Button>
      </div>

      <div class="grid gap-4 md:grid-cols-5">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Layanan</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ services.total }}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Aktif</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-green-600">
              {{ services.data.filter(s => s.status === 'active').length }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Ditangguhkan</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-orange-600">
              {{ services.data.filter(s => s.status === 'suspended').length }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Segera Berakhir</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-red-600">{{ expiringSoon }}</div>
            <div class="text-xs text-muted-foreground">30 hari ke depan</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Hosting</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-blue-600">
              {{ services.data.filter(s => s.service_type === 'hosting').length }}
            </div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Semua Layanan</CardTitle>
          <CardDescription>Daftar lengkap layanan pelanggan</CardDescription>
        </CardHeader>
        <CardContent>
          <!-- Search and Filter -->
          <div class="mb-6 flex flex-col sm:flex-row gap-4">
            <div class="relative flex-1 max-w-sm">
              <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="search"
                placeholder="Cari layanan..."
                class="pl-8"
                @keyup.enter="handleSearch"
              />
            </div>
            <select 
              v-model="statusFilter" 
              class="flex h-9 w-[180px] rounded-md border border-input bg-background dark:bg-gray-800 px-3 py-1 text-sm shadow-sm transition-colors text-foreground dark:text-white placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
            >
              <option value="">Semua Status</option>
              <option value="active">Aktif</option>
              <option value="pending">Menunggu</option>
              <option value="suspended">Ditangguhkan</option>
              <option value="terminated">Dihentikan</option>
            </select>
            <select 
              v-model="serviceTypeFilter" 
              class="flex h-9 w-[180px] rounded-md border border-input bg-background dark:bg-gray-800 px-3 py-1 text-sm shadow-sm transition-colors text-foreground dark:text-white placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
            >
              <option value="">Semua Tipe</option>
              <option value="hosting">Hosting</option>
              <option value="domain">Domain</option>
            </select>
            <Button @click="handleSearch" class="cursor-pointer">Cari</Button>
          </div>

          <div class="space-y-4">
            <div v-if="services.data.length === 0" class="text-center py-8 text-muted-foreground">
              Layanan tidak ditemukan.
            </div>
            
            <div v-else class="space-y-4">
              <div 
                v-for="service in services.data" 
                :key="service.id"
                class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/30"
              >
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-2">
                    <h3 class="font-semibold">{{ service.domain_name }}</h3>
                    <span :class="`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${
                      service.service_type === 'hosting' 
                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
                        : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'
                    }`">
                      {{ getServiceTypeText(service.service_type) }}
                    </span>
                    <Badge :class="getStatusColor(service.status)">
                      {{ getStatusText(service.status) }}
                    </Badge>
                    <span v-if="isExpiringSoon(service.expires_at) && service.status === 'active'" 
                          class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                      Segera Berakhir
                    </span>
                  </div>
                  <div class="text-sm text-muted-foreground space-y-1">
                    <div><strong>Pelanggan:</strong> {{ service.customer.name }} ({{ service.customer.email }})</div>
                    <div v-if="service.hosting_plan">
                      <strong>Paket:</strong> {{ service.hosting_plan.plan_name }} 
                      ({{ service.hosting_plan.storage_gb }}GB, {{ service.hosting_plan.cpu_cores }} CPU, {{ service.hosting_plan.ram_gb }}GB RAM)
                    </div>
                    <div><strong>Dibuat:</strong> {{ formatDate(service.created_at) }}</div>
                    <div><strong>Berakhir:</strong> {{ formatDate(service.expires_at) }}</div>
                    <div><strong>Perpanjang Otomatis:</strong> {{ service.auto_renew ? 'Ya' : 'Tidak' }}</div>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <Button variant="outline" size="sm" asChild>
                    <Link :href="`/admin/services/${service.id}`">
                      Lihat Detail
                    </Link>
                  </Button>
                  <Button size="sm" variant="outline" @click="openEditModal(service)" class="cursor-pointer">
                    <Edit class="h-3 w-3" />
                  </Button>
                  <Button 
                    size="sm" 
                    variant="outline" 
                    @click="openDeleteModal(service)"
                    :disabled="service.status === 'active'"
                  >
                    <Trash2 class="h-3 w-3" />
                  </Button>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="services.links && services.links.length > 3" class="flex items-center justify-between pt-6 border-t">
              <div class="text-sm text-muted-foreground">
                Showing {{ ((services.current_page - 1) * services.per_page + 1) || 0 }} to 
                {{ Math.min(services.current_page * services.per_page, services.total) || 0 }} of 
                {{ services.total || 0 }} results
              </div>
              <div class="flex items-center gap-1">
                <template v-for="link in services.links" :key="link.label">
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
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Create Service Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-semibold">Tambah Layanan Baru</h2>
            <p class="text-sm text-muted-foreground">Buat layanan hosting atau domain baru untuk pelanggan</p>
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
                class="flex h-9 w-full rounded-md border border-input bg-background dark:bg-gray-800 px-3 py-1 text-sm shadow-sm transition-colors text-foreground dark:text-white placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
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
              <Label for="create-service-type">Tipe Layanan *</Label>
              <select 
                id="create-service-type"
                v-model="createForm.service_type"
                class="flex h-9 w-full rounded-md border border-input bg-background dark:bg-gray-800 px-3 py-1 text-sm shadow-sm transition-colors text-foreground dark:text-white placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                required
              >
                <option value="hosting">Hosting</option>
                <option value="domain">Domain</option>
              </select>
              <p v-if="createForm.errors.service_type" class="text-xs text-red-500 mt-1">{{ createForm.errors.service_type }}</p>
            </div>
          </div>

          <div v-if="createForm.service_type === 'hosting'">
            <Label for="create-plan">Paket Hosting *</Label>
            <select 
              id="create-plan"
              v-model="createForm.plan_id"
              class="flex h-9 w-full rounded-md border border-input bg-background dark:bg-gray-800 px-3 py-1 text-sm shadow-sm transition-colors text-foreground dark:text-white placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
              required
            >
              <option value="">Pilih Paket Hosting</option>
              <option v-for="plan in hostingPlans" :key="plan.id" :value="plan.id">
                {{ plan.plan_name }} ({{ plan.storage_gb }}GB, {{ plan.cpu_cores }} CPU, {{ plan.ram_gb }}GB RAM)
              </option>
            </select>
            <p v-if="createForm.errors.plan_id" class="text-xs text-red-500 mt-1">{{ createForm.errors.plan_id }}</p>
          </div>

          <div>
            <Label for="create-domain">Nama Domain *</Label>
            <Input
              id="create-domain"
              v-model="createForm.domain_name"
              placeholder="example.com"
              :class="{ 'border-red-500': createForm.errors.domain_name }"
              required
            />
            <p v-if="createForm.errors.domain_name" class="text-xs text-red-500 mt-1">{{ createForm.errors.domain_name }}</p>
          </div>

          <div>
            <Label for="create-expires">Berakhir Pada *</Label>
            <Input
              id="create-expires"
              type="date"
              v-model="createForm.expires_at"
              :class="{ 'border-red-500': createForm.errors.expires_at }"
              required
            />
            <p v-if="createForm.errors.expires_at" class="text-xs text-red-500 mt-1">{{ createForm.errors.expires_at }}</p>
          </div>

          <div class="flex items-center space-x-2">
            <input
              id="create-auto-renew"
              type="checkbox"
              v-model="createForm.auto_renew"
              class="rounded border border-input"
            />
            <Label for="create-auto-renew">Perpanjang Otomatis</Label>
          </div>

          <!-- Footer -->
          <div class="flex justify-end gap-2 mt-6">
            <Button type="button" variant="outline" @click="showCreateModal = false" class="cursor-pointer">
              Batal
            </Button>
            <Button type="submit" :disabled="createForm.processing">
              {{ createForm.processing ? 'Membuat...' : 'Buat Layanan' }}
            </Button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Service Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-semibold">Edit Layanan</h2>
            <p class="text-sm text-muted-foreground">Perbarui konfigurasi dan pengaturan layanan</p>
          </div>
          <button @click="showEditModal = false" class="text-gray-500 hover:text-gray-700 cursor-pointer">
            <X class="h-4 w-4" />
          </button>
        </div>
        <form @submit.prevent="submitEdit" class="space-y-4">
          <div>
            <Label for="edit-domain">Nama Domain *</Label>
            <Input
              id="edit-domain"
              v-model="editForm.domain_name"
              placeholder="example.com"
              :class="{ 'border-red-500': editForm.errors.domain_name }"
              required
            />
            <p v-if="editForm.errors.domain_name" class="text-xs text-red-500 mt-1">{{ editForm.errors.domain_name }}</p>
          </div>

          <div>
            <Label for="edit-status">Status *</Label>
            <select 
              id="edit-status"
              v-model="editForm.status"
              class="flex h-9 w-full rounded-md border border-input bg-background dark:bg-gray-800 px-3 py-1 text-sm shadow-sm transition-colors text-foreground dark:text-white placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
              required
            >
              <option value="active">Aktif</option>
              <option value="pending">Menunggu</option>
              <option value="suspended">Ditangguhkan</option>
              <option value="terminated">Dihentikan</option>
            </select>
            <p v-if="editForm.errors.status" class="text-xs text-red-500 mt-1">{{ editForm.errors.status }}</p>
          </div>

          <div>
            <Label for="edit-expires">Berakhir Pada *</Label>
            <Input
              id="edit-expires"
              type="date"
              v-model="editForm.expires_at"
              :class="{ 'border-red-500': editForm.errors.expires_at }"
              required
            />
            <p v-if="editForm.errors.expires_at" class="text-xs text-red-500 mt-1">{{ editForm.errors.expires_at }}</p>
          </div>

          <div class="flex items-center space-x-2">
            <input
              id="edit-auto-renew"
              type="checkbox"
              v-model="editForm.auto_renew"
              class="rounded border border-input"
            />
            <Label for="edit-auto-renew">Perpanjang Otomatis</Label>
          </div>

          <!-- Footer -->
          <div class="flex justify-end gap-2 mt-6">
            <Button type="button" variant="outline" @click="showEditModal = false" class="cursor-pointer">
              Batal
            </Button>
            <Button type="submit" :disabled="editForm.processing">
              {{ editForm.processing ? 'Memperbarui...' : 'Perbarui Layanan' }}
            </Button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Service Modal -->
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
                  <p>Anda akan menghapus secara permanen layanan <strong>{{ serviceToDelete?.domain_name }}</strong>.</p>
                  <div class="mt-3 space-y-1">
                    <p><strong>Ini juga akan menghapus:</strong></p>
                    <ul class="list-disc list-inside space-y-1 ml-2">
                      <li>Semua konfigurasi layanan</li>
                      <li>Semua data terkait secara permanen</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
            <p class="text-sm text-gray-600 dark:text-gray-400">
              <strong>Layanan:</strong> {{ serviceToDelete?.domain_name }}<br>
              <strong>Tipe:</strong> {{ serviceToDelete ? getServiceTypeText(serviceToDelete.service_type) : '' }}<br>
              <strong>Pelanggan:</strong> {{ serviceToDelete?.customer?.name }}<br>
              <strong>Status:</strong> {{ serviceToDelete ? getStatusText(serviceToDelete.status) : '' }}
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
            Ya, Hapus Layanan
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>