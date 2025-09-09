<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Search, Plus, Edit, Trash2, Eye, CheckCircle, XCircle, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface ServicePlan {
  id: number;
  name: string;
  category: string;
  description: string;
  price: number;
  features: Record<string, any>;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

interface Props {
  servicePlans: {
    data: ServicePlan[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: any[];
  };
  categories: Record<string, string>;
  filters?: {
    search?: string;
    category?: string;
  };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const categoryFilter = ref(props.filters?.category || '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedServicePlan = ref<ServicePlan | null>(null);
const servicePlanToDelete = ref<ServicePlan | null>(null);

const createForm = useForm({
  name: '',
  category: 'web_package' as keyof typeof props.categories,
  description: '',
  price: 0,
  features: {} as Record<string, any>,
  is_active: true,
});

const editForm = useForm({
  name: '',
  category: 'web_package' as keyof typeof props.categories,
  description: '',
  price: 0,
  features: {} as Record<string, any>,
  is_active: true,
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Paket Layanan', href: '/admin/service-plans' },
];

const formatPrice = (price: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price);
};

const getCategoryColor = (category: string) => {
  switch (category) {
    case 'web_package': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
    case 'addon': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    case 'license': return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
    case 'custom_system': return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
  }
};

const handleSearch = () => {
  router.get('/admin/service-plans', { 
    search: search.value, 
    category: categoryFilter.value
  }, { 
    preserveState: true,
    replace: true 
  });
};

const submitCreate = () => {
  createForm.post('/admin/service-plans', {
    onSuccess: () => {
      showCreateModal.value = false;
      createForm.reset();
    },
  });
};

const openEditModal = (servicePlan: ServicePlan) => {
  selectedServicePlan.value = servicePlan;
  editForm.reset();
  editForm.name = servicePlan.name;
  editForm.category = servicePlan.category as keyof typeof props.categories;
  editForm.description = servicePlan.description;
  editForm.price = servicePlan.price;
  editForm.features = servicePlan.features || {};
  editForm.is_active = servicePlan.is_active;
  showEditModal.value = true;
};

const submitEdit = () => {
  if (!selectedServicePlan.value) return;
  
  editForm.put(`/admin/service-plans/${selectedServicePlan.value.id}`, {
    onSuccess: () => {
      showEditModal.value = false;
      editForm.reset();
      selectedServicePlan.value = null;
    },
  });
};

const openDeleteModal = (servicePlan: ServicePlan) => {
  servicePlanToDelete.value = servicePlan;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  if (!servicePlanToDelete.value) return;
  
  router.delete(`/admin/service-plans/${servicePlanToDelete.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      servicePlanToDelete.value = null;
    },
    onError: (errors) => {
      console.error('Delete service plan error:', errors);
    },
  });
};

// Helper to add/remove features
const addFeature = (form: any) => {
  const featureName = prompt('Masukkan nama fitur:');
  if (featureName) {
    form.features[featureName] = true;
  }
};

const removeFeature = (form: any, featureName: string) => {
  delete form.features[featureName];
};
</script>

<template>
  <Head title="Admin - Paket Layanan" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Kelola Paket Layanan</h1>
          <p class="text-muted-foreground">Kelola paket layanan dan harga</p>
        </div>
        <Button @click="showCreateModal = true">
          <Plus class="h-4 w-4 mr-2" />
          Tambah Paket Layanan
        </Button>
      </div>

      <!-- Stats Cards -->
      <div class="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Paket</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ servicePlans.total }}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Aktif</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-green-600">
              {{ servicePlans.data.filter(p => p.is_active).length }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Nonaktif</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-red-600">
              {{ servicePlans.data.filter(p => !p.is_active).length }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Kategori</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ Object.keys(categories).length }}</div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Semua Paket Layanan</CardTitle>
          <CardDescription>Kelola penawaran layanan Anda</CardDescription>
        </CardHeader>
        <CardContent>
          <!-- Search and Filter -->
          <div class="mb-6 flex flex-col sm:flex-row gap-4">
            <div class="relative flex-1 max-w-sm">
              <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="search"
                placeholder="Cari paket layanan..."
                class="pl-8"
                @keyup.enter="handleSearch"
              />
            </div>
            <select 
              v-model="categoryFilter" 
              class="flex h-9 w-[200px] rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
            >
              <option value="">Semua Kategori</option>
              <option v-for="(label, value) in categories" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
            <Button @click="handleSearch">Cari</Button>
          </div>

          <div class="space-y-4">
            <div v-if="servicePlans.data.length === 0" class="text-center py-8 text-muted-foreground">
              Paket layanan tidak ditemukan.
            </div>
            
            <div v-else class="space-y-4">
              <div 
                v-for="plan in servicePlans.data" 
                :key="plan.id"
                class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/30"
              >
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-2">
                    <h3 class="font-semibold">{{ plan.name }}</h3>
                    <Badge :class="getCategoryColor(plan.category)" class="text-xs">
                      {{ categories[plan.category] || plan.category }}
                    </Badge>
                    <span class="flex items-center gap-1 text-xs">
                      <CheckCircle v-if="plan.is_active" class="h-3 w-3 text-green-500" />
                      <XCircle v-else class="h-3 w-3 text-red-500" />
                      {{ plan.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </div>
                  <div class="text-sm text-muted-foreground space-y-1">
                    <div><strong>Harga:</strong> {{ formatPrice(plan.price) }}</div>
                    <div v-if="plan.description"><strong>Deskripsi:</strong> {{ plan.description }}</div>
                    <div v-if="plan.features && Object.keys(plan.features).length > 0">
                      <strong>Fitur:</strong> {{ Object.keys(plan.features).join(', ') }}
                    </div>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <Button variant="outline" size="sm" asChild>
                    <Link :href="`/admin/service-plans/${plan.id}`">
                      <Eye class="h-3 w-3 mr-1" />
                      Lihat
                    </Link>
                  </Button>
                  <Button size="sm" variant="outline" @click="openEditModal(plan)">
                    <Edit class="h-3 w-3 mr-1" />
                    Edit
                  </Button>
                  <Button 
                    size="sm" 
                    variant="outline" 
                    @click="openDeleteModal(plan)"
                  >
                    <Trash2 class="h-3 w-3 mr-1" />
                    Hapus
                  </Button>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="servicePlans.links && servicePlans.links.length > 3" class="flex items-center justify-between pt-6 border-t">
              <div class="text-sm text-muted-foreground">
                Showing {{ ((servicePlans.current_page - 1) * servicePlans.per_page + 1) || 0 }} to 
                {{ Math.min(servicePlans.current_page * servicePlans.per_page, servicePlans.total) || 0 }} of 
                {{ servicePlans.total || 0 }} results
              </div>
              <div class="flex items-center gap-1">
                <template v-for="link in servicePlans.links" :key="link.label">
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

    <!-- Create Service Plan Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-2xl mx-4 p-6 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-semibold">Tambah Paket Layanan Baru</h2>
            <p class="text-sm text-muted-foreground">Buat paket layanan baru dengan harga dan fitur</p>
          </div>
          <button @click="showCreateModal = false" class="text-gray-500 hover:text-gray-700">
            <X class="h-4 w-4" />
          </button>
        </div>
        
        <form @submit.prevent="submitCreate" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="create-name">Nama *</Label>
              <Input
                id="create-name"
                v-model="createForm.name"
                placeholder="Nama paket layanan"
                :class="{ 'border-red-500': createForm.errors.name }"
                required
              />
              <p v-if="createForm.errors.name" class="text-xs text-red-500 mt-1">{{ createForm.errors.name }}</p>
            </div>
            <div>
              <Label for="create-category">Kategori *</Label>
              <select 
                id="create-category"
                v-model="createForm.category"
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                required
              >
                <option v-for="(label, value) in categories" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
              <p v-if="createForm.errors.category" class="text-xs text-red-500 mt-1">{{ createForm.errors.category }}</p>
            </div>
          </div>

          <div>
            <Label for="create-price">Harga *</Label>
            <Input
              id="create-price"
              type="number"
              step="0.01"
              min="0"
              v-model="createForm.price"
              placeholder="0"
              :class="{ 'border-red-500': createForm.errors.price }"
              required
            />
            <p v-if="createForm.errors.price" class="text-xs text-red-500 mt-1">{{ createForm.errors.price }}</p>
          </div>

          <div>
            <Label for="create-description">Deskripsi</Label>
            <textarea
              id="create-description"
              v-model="createForm.description"
              placeholder="Deskripsi paket layanan"
              class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              :class="{ 'border-red-500': createForm.errors.description }"
            />
            <p v-if="createForm.errors.description" class="text-xs text-red-500 mt-1">{{ createForm.errors.description }}</p>
          </div>

          <div>
            <div class="flex items-center justify-between">
              <Label>Fitur</Label>
              <Button type="button" variant="outline" size="sm" @click="addFeature(createForm)">
                <Plus class="h-3 w-3 mr-1" />
                Tambah Fitur
              </Button>
            </div>
            <div v-if="Object.keys(createForm.features).length > 0" class="space-y-2 mt-2">
              <div 
                v-for="(value, feature) in createForm.features" 
                :key="feature"
                class="flex items-center justify-between p-2 border rounded"
              >
                <span class="text-sm">{{ feature }}</span>
                <Button 
                  type="button" 
                  variant="outline" 
                  size="sm" 
                  @click="removeFeature(createForm, feature)"
                >
                  <Trash2 class="h-3 w-3" />
                </Button>
              </div>
            </div>
          </div>

          <div class="flex items-center space-x-2">
            <input
              id="create-active"
              type="checkbox"
              v-model="createForm.is_active"
              class="rounded border border-input"
            />
            <Label for="create-active">Aktif</Label>
          </div>

          <!-- Footer -->
          <div class="flex justify-end gap-2 mt-6">
            <Button type="button" variant="outline" @click="showCreateModal = false">
              Batal
            </Button>
            <Button type="submit" :disabled="createForm.processing">
              {{ createForm.processing ? 'Membuat...' : 'Buat Paket Layanan' }}
            </Button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Service Plan Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-2xl mx-4 p-6 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-semibold">Edit Paket Layanan</h2>
            <p class="text-sm text-muted-foreground">Perbarui detail dan konfigurasi paket layanan</p>
          </div>
          <button @click="showEditModal = false" class="text-gray-500 hover:text-gray-700">
            <X class="h-4 w-4" />
          </button>
        </div>
        
        <form @submit.prevent="submitEdit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="edit-name">Nama *</Label>
              <Input
                id="edit-name"
                v-model="editForm.name"
                placeholder="Nama paket layanan"
                :class="{ 'border-red-500': editForm.errors.name }"
                required
              />
              <p v-if="editForm.errors.name" class="text-xs text-red-500 mt-1">{{ editForm.errors.name }}</p>
            </div>
            <div>
              <Label for="edit-category">Kategori *</Label>
              <select 
                id="edit-category"
                v-model="editForm.category"
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                required
              >
                <option v-for="(label, value) in categories" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
              <p v-if="editForm.errors.category" class="text-xs text-red-500 mt-1">{{ editForm.errors.category }}</p>
            </div>
          </div>

          <div>
            <Label for="edit-price">Harga *</Label>
            <Input
              id="edit-price"
              type="number"
              step="0.01"
              min="0"
              v-model="editForm.price"
              placeholder="0"
              :class="{ 'border-red-500': editForm.errors.price }"
              required
            />
            <p v-if="editForm.errors.price" class="text-xs text-red-500 mt-1">{{ editForm.errors.price }}</p>
          </div>

          <div>
            <Label for="edit-description">Deskripsi</Label>
            <textarea
              id="edit-description"
              v-model="editForm.description"
              placeholder="Deskripsi paket layanan"
              class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              :class="{ 'border-red-500': editForm.errors.description }"
            />
            <p v-if="editForm.errors.description" class="text-xs text-red-500 mt-1">{{ editForm.errors.description }}</p>
          </div>

          <div>
            <div class="flex items-center justify-between">
              <Label>Fitur</Label>
              <Button type="button" variant="outline" size="sm" @click="addFeature(editForm)">
                <Plus class="h-3 w-3 mr-1" />
                Tambah Fitur
              </Button>
            </div>
            <div v-if="Object.keys(editForm.features).length > 0" class="space-y-2 mt-2">
              <div 
                v-for="(value, feature) in editForm.features" 
                :key="feature"
                class="flex items-center justify-between p-2 border rounded"
              >
                <span class="text-sm">{{ feature }}</span>
                <Button 
                  type="button" 
                  variant="outline" 
                  size="sm" 
                  @click="removeFeature(editForm, feature)"
                >
                  <Trash2 class="h-3 w-3" />
                </Button>
              </div>
            </div>
          </div>

          <div class="flex items-center space-x-2">
            <input
              id="edit-active"
              type="checkbox"
              v-model="editForm.is_active"
              class="rounded border border-input"
            />
            <Label for="edit-active">Aktif</Label>
          </div>

          <!-- Footer -->
          <div class="flex justify-end gap-2 mt-6">
            <Button type="button" variant="outline" @click="showEditModal = false">
              Batal
            </Button>
            <Button type="submit" :disabled="editForm.processing">
              {{ editForm.processing ? 'Memperbarui...' : 'Perbarui Paket Layanan' }}
            </Button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Service Plan Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-red-600">Konfirmasi Penghapusan</h2>
          <button @click="showDeleteModal = false" class="text-gray-500 hover:text-gray-700">
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
                  <p>Anda akan menghapus secara permanen paket layanan <strong>{{ servicePlanToDelete?.name }}</strong>.</p>
                  <div class="mt-3 space-y-1">
                    <p><strong>Ini juga akan menghapus:</strong></p>
                    <ul class="list-disc list-inside space-y-1 ml-2">
                      <li>Semua konfigurasi paket</li>
                      <li>Semua data terkait secara permanen</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
            <p class="text-sm text-gray-600 dark:text-gray-400">
              <strong>Paket:</strong> {{ servicePlanToDelete?.name }}<br>
              <strong>Kategori:</strong> {{ servicePlanToDelete ? categories[servicePlanToDelete.category] : '' }}<br>
              <strong>Harga:</strong> {{ servicePlanToDelete ? formatPrice(servicePlanToDelete.price) : '' }}<br>
              <strong>Status:</strong> {{ servicePlanToDelete?.is_active ? 'Aktif' : 'Nonaktif' }}
            </p>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-2 mt-6">
          <Button type="button" variant="outline" @click="showDeleteModal = false">
            Batal
          </Button>
          <Button 
            type="button" 
            class="bg-red-600 hover:bg-red-700 text-white" 
            @click="confirmDelete"
          >
            Ya, Hapus Paket Layanan
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>