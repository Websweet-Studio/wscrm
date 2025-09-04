<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CheckCircle, Zap, Shield, Palette, ShoppingCart, Settings, Star, Plus, Edit, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface ServicePlan {
  id: number;
  name: string;
  category: string;
  description: string;
  price: number;
  features: Record<string, any>;
  is_active: boolean;
}

interface Props {
  servicePlans: Record<string, ServicePlan[]>;
  categories: Record<string, string>;
}

const props = defineProps<Props>();

// Modal state
const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedServicePlan = ref<ServicePlan | null>(null);

// Forms
const createForm = useForm({
  name: '',
  category: '',
  description: '',
  price: 0,
  features: {} as Record<string, any>,
  is_active: true,
});

const editForm = useForm({
  name: '',
  category: '',
  description: '',
  price: 0,
  features: {} as Record<string, any>,
  is_active: true,
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Layanan Kami', href: '/services' },
];

const formatPrice = (price: number): string => {
  if (price === 0) return 'Hubungi Kami';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price);
};

const getCategoryIcon = (category: string) => {
  switch (category) {
    case 'web_package': return Palette;
    case 'addon': return ShoppingCart;
    case 'license': return Shield;
    case 'custom_system': return Settings;
    default: return Star;
  }
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

// CRUD Functions
const submitCreate = () => {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  
  createForm.transform((data) => ({
    ...data,
    _token: csrfToken
  })).post('/admin/service-plans', {
    headers: {
      'X-CSRF-TOKEN': csrfToken
    },
    onSuccess: () => {
      showCreateModal.value = false;
      createForm.reset();
    },
    onError: (errors) => {
      console.error('Create service plan error:', errors);
      if (errors[419] || Object.values(errors).some(e => String(e).includes('419'))) {
        window.location.reload();
      }
    },
  });
};

const openEditModal = (servicePlan: ServicePlan) => {
  selectedServicePlan.value = servicePlan;
  editForm.reset();
  editForm.name = servicePlan.name;
  editForm.category = servicePlan.category;
  editForm.description = servicePlan.description;
  editForm.price = servicePlan.price;
  editForm.features = servicePlan.features;
  editForm.is_active = servicePlan.is_active;
  showEditModal.value = true;
};

const submitEdit = () => {
  if (!selectedServicePlan.value) return;
  
  editForm.put(`/admin/service-plans/${selectedServicePlan.value.id}`, {
    preserveState: false,
    preserveScroll: true,
    onSuccess: () => {
      showEditModal.value = false;
      editForm.reset();
      selectedServicePlan.value = null;
    },
    onError: (errors) => {
      console.error('Update service plan error:', errors);
    },
  });
};

const deleteServicePlan = (servicePlan: ServicePlan) => {
  if (confirm('Are you sure you want to delete this service plan?')) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    fetch(`/admin/service-plans/${servicePlan.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken || '',
        'Content-Type': 'application/json',
      },
    }).then(() => {
      window.location.reload();
    }).catch((error) => {
      console.error('Delete service plan error:', error);
    });
  }
};

// Available options
const availableCategories = [
  { value: 'web_package', label: 'Web Package' },
  { value: 'addon', label: 'Add-on' },
  { value: 'license', label: 'License' },
  { value: 'custom_system', label: 'Custom System' },
];

const availableFeatures = [
  'responsive_design',
  'seo_optimized',
  'ssl_certificate',
  'backup_included',
  'support_24_7',
  'custom_domain',
  'analytics',
  'social_media_integration',
];

const toggleFeature = (feature: string, form: any) => {
  if (form.features[feature]) {
    delete form.features[feature];
  } else {
    form.features[feature] = true;
  }
};

</script>

<template>
  <Head title="Layanan Kami - WebSweetStudio" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-8 p-6">
      <!-- Admin Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold">Service Plans Management</h1>
          <p class="text-muted-foreground">Manage service plans and packages</p>
        </div>
        <Button @click="showCreateModal = true">
          <Plus class="h-4 w-4 mr-2" />
          Add Service Plan
        </Button>
      </div>

      <!-- Hero Section -->
      <div class="text-center space-y-4">
        <h1 class="text-4xl font-bold tracking-tight">Layanan Kami</h1>
        <p class="text-xl text-muted-foreground max-w-2xl mx-auto">
          Pilihan lengkap layanan pengembangan website dan sistem untuk kebutuhan bisnis Anda
        </p>
      </div>

      <!-- Service Categories -->
      <div class="space-y-12">
        <template v-for="(plans, category) in servicePlans" :key="category">
          <div class="space-y-6">
            <div class="flex items-center gap-3">
              <component 
                :is="getCategoryIcon(category)" 
                class="h-8 w-8 text-primary" 
              />
              <div>
                <h2 class="text-2xl font-bold">{{ categories[category] || category }}</h2>
                <p class="text-muted-foreground">
                  {{ category === 'web_package' ? 'Paket website siap pakai dengan berbagai fitur' :
                     category === 'addon' ? 'Tambahan fitur untuk meningkatkan website Anda' :
                     category === 'license' ? 'Lisensi premium untuk tools dan plugin terbaik' :
                     'Solusi custom sesuai kebutuhan spesifik Anda' }}
                </p>
              </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
              <Card 
                v-for="plan in plans" 
                :key="plan.id"
                class="relative overflow-hidden hover:shadow-lg transition-shadow duration-200"
              >
                <CardHeader class="pb-4">
                  <div class="flex items-start justify-between">
                    <div class="space-y-2">
                      <CardTitle class="text-xl">{{ plan.name }}</CardTitle>
                      <Badge :class="getCategoryColor(plan.category)" class="text-xs">
                        {{ categories[plan.category] || plan.category }}
                      </Badge>
                    </div>
                    <div class="flex flex-col items-end space-y-2">
                      <div class="text-right">
                        <div class="text-2xl font-bold text-primary">
                          {{ formatPrice(plan.price) }}
                        </div>
                        <div v-if="plan.price > 0" class="text-sm text-muted-foreground">
                          per project
                        </div>
                      </div>
                      <!-- Admin Actions -->
                      <div class="flex gap-1">
                        <Button 
                          size="sm" 
                          variant="outline" 
                          @click="openEditModal(plan)"
                          class="h-8 w-8 p-0"
                        >
                          <Edit class="h-3 w-3" />
                        </Button>
                        <Button 
                          size="sm" 
                          variant="outline" 
                          @click="deleteServicePlan(plan)"
                          class="h-8 w-8 p-0 text-red-600 hover:text-red-700"
                        >
                          <Trash2 class="h-3 w-3" />
                        </Button>
                      </div>
                    </div>
                  </div>
                </CardHeader>

                <CardContent class="space-y-4">
                  <CardDescription class="text-sm leading-relaxed">
                    {{ plan.description }}
                  </CardDescription>

                  <!-- Features -->
                  <div v-if="plan.features && Object.keys(plan.features).length > 0" class="space-y-2">
                    <h4 class="text-sm font-medium">Fitur Unggulan:</h4>
                    <ul class="space-y-1">
                      <li 
                        v-for="(value, feature) in plan.features" 
                        :key="feature"
                        v-if="value"
                        class="flex items-center gap-2 text-sm text-muted-foreground"
                      >
                        <CheckCircle class="h-4 w-4 text-green-500 flex-shrink-0" />
                        <span>{{ 
                          feature === 'responsive_design' ? 'Desain Responsif' :
                          feature === 'seo_optimized' ? 'SEO Optimized' :
                          feature === 'contact_form' ? 'Form Kontak' :
                          feature === 'admin_panel' ? 'Panel Admin' :
                          feature === 'instant_setup' ? 'Setup Instan' :
                          feature === 'pre_built_templates' ? 'Template Siap Pakai' :
                          feature === 'custom_design' ? 'Desain Custom' :
                          feature === 'unlimited_revisions' ? 'Revisi Unlimited' :
                          feature === 'advanced_seo' ? 'SEO Advanced' :
                          feature === 'shopping_cart' ? 'Keranjang Belanja' :
                          feature === 'payment_integration' ? 'Integrasi Payment' :
                          feature === 'inventory_management' ? 'Manajemen Stok' :
                          feature === 'order_tracking' ? 'Tracking Pesanan' :
                          feature === 'custom_development' ? 'Pengembangan Custom' :
                          feature === 'consultation' ? 'Konsultasi' :
                          feature === 'maintenance' ? 'Maintenance' :
                          feature === 'documentation' ? 'Dokumentasi' :
                          feature === 'elementor_pro' ? 'Elementor Pro' :
                          feature === 'wp_rocket' ? 'WP Rocket' :
                          feature === 'crocoblock_suite' ? 'Crocoblock Suite' :
                          feature === 'jetengine' ? 'JetEngine' :
                          feature === 'jetwoobuilder' ? 'JetWooBuilder' :
                          feature === '1_year_license' ? 'Lisensi 1 Tahun' :
                          feature === 'support_included' ? 'Support Included' :
                          feature === 'gpl_license' ? 'Lisensi GPL' :
                          feature === 'regular_updates' ? 'Update Berkala' :
                          feature === 'basic_support' ? 'Support Dasar' :
                          feature.replace(/_/g, ' ')
                        }}</span>
                      </li>
                    </ul>
                  </div>

                  <div class="pt-4 space-y-2">
                    <Button asChild class="w-full">
                      <Link :href="`/services/${plan.id}`">
                        {{ plan.price === 0 ? 'Konsultasi Gratis' : 'Pilih Paket' }}
                      </Link>
                    </Button>
                    <Button variant="outline" asChild class="w-full">
                      <Link :href="`/services/${plan.id}`">
                        Lihat Detail
                      </Link>
                    </Button>
                  </div>
                </CardContent>

                <!-- Popular badge for featured plans -->
                <div v-if="plan.category === 'web_package' && plan.name.includes('Custom')" 
                     class="absolute top-4 right-4">
                  <Badge class="bg-primary text-primary-foreground">
                    <Star class="h-3 w-3 mr-1" />
                    Populer
                  </Badge>
                </div>
              </Card>
            </div>
          </div>
        </template>
      </div>

      <!-- CTA Section -->
      <div class="bg-muted/30 rounded-xl p-8 text-center space-y-4">
        <h3 class="text-2xl font-bold">Butuh Solusi Khusus?</h3>
        <p class="text-muted-foreground max-w-2xl mx-auto">
          Tim ahli kami siap membantu Anda mengembangkan solusi digital yang tepat untuk kebutuhan bisnis Anda
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <Button size="lg" asChild>
            <Link href="/contact">
              <Zap class="h-4 w-4 mr-2" />
              Konsultasi Gratis
            </Link>
          </Button>
          <Button variant="outline" size="lg" asChild>
            <Link href="/portfolio">
              Lihat Portfolio
            </Link>
          </Button>
        </div>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-2xl mx-4 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold">Create New Service Plan</h2>
          <button @click="showCreateModal = false" class="text-gray-500 hover:text-gray-700">
            <X class="h-4 w-4" />
          </button>
        </div>
        
        <form @submit.prevent="submitCreate" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="create-name">Name *</Label>
              <Input 
                id="create-name"
                v-model="createForm.name"
                placeholder="Service plan name"
                required
              />
              <p v-if="createForm.errors.name" class="text-xs text-red-500 mt-1">{{ createForm.errors.name }}</p>
            </div>
            <div>
              <Label for="create-category">Category *</Label>
              <select 
                id="create-category"
                v-model="createForm.category"
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                required
              >
                <option value="">Select Category</option>
                <option v-for="category in availableCategories" :key="category.value" :value="category.value">
                  {{ category.label }}
                </option>
              </select>
              <p v-if="createForm.errors.category" class="text-xs text-red-500 mt-1">{{ createForm.errors.category }}</p>
            </div>
          </div>

          <div>
            <Label for="create-price">Price *</Label>
            <Input 
              id="create-price"
              v-model.number="createForm.price"
              type="number"
              placeholder="0"
              min="0"
              step="1000"
              required
            />
            <p v-if="createForm.errors.price" class="text-xs text-red-500 mt-1">{{ createForm.errors.price }}</p>
          </div>

          <div>
            <Label for="create-description">Description *</Label>
            <textarea 
              id="create-description"
              v-model="createForm.description"
              class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
              placeholder="Describe the service plan..."
              required
            ></textarea>
            <p v-if="createForm.errors.description" class="text-xs text-red-500 mt-1">{{ createForm.errors.description }}</p>
          </div>

          <div>
            <Label>Features</Label>
            <div class="grid grid-cols-2 gap-2 mt-2">
              <label v-for="feature in availableFeatures" :key="feature" class="flex items-center space-x-2">
                <input 
                  type="checkbox" 
                  :checked="createForm.features[feature]"
                  @change="toggleFeature(feature, createForm)"
                  class="rounded border-gray-300"
                >
                <span class="text-sm">{{ feature.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}</span>
              </label>
            </div>
          </div>

          <div class="flex items-center space-x-2">
            <input 
              id="create-active"
              v-model="createForm.is_active"
              type="checkbox"
              class="rounded border-gray-300"
            >
            <Label for="create-active">Active</Label>
          </div>

          <div class="flex justify-end space-x-2 pt-4">
            <Button type="button" variant="outline" @click="showCreateModal = false">
              Cancel
            </Button>
            <Button type="submit" :disabled="createForm.processing">
              {{ createForm.processing ? 'Creating...' : 'Create Service Plan' }}
            </Button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-2xl mx-4 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold">Edit Service Plan</h2>
          <button @click="showEditModal = false" class="text-gray-500 hover:text-gray-700">
            <X class="h-4 w-4" />
          </button>
        </div>
        
        <form @submit.prevent="submitEdit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="edit-name">Name *</Label>
              <Input 
                id="edit-name"
                v-model="editForm.name"
                placeholder="Service plan name"
                required
              />
              <p v-if="editForm.errors.name" class="text-xs text-red-500 mt-1">{{ editForm.errors.name }}</p>
            </div>
            <div>
              <Label for="edit-category">Category *</Label>
              <select 
                id="edit-category"
                v-model="editForm.category"
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                required
              >
                <option value="">Select Category</option>
                <option v-for="category in availableCategories" :key="category.value" :value="category.value">
                  {{ category.label }}
                </option>
              </select>
              <p v-if="editForm.errors.category" class="text-xs text-red-500 mt-1">{{ editForm.errors.category }}</p>
            </div>
          </div>

          <div>
            <Label for="edit-price">Price *</Label>
            <Input 
              id="edit-price"
              v-model.number="editForm.price"
              type="number"
              placeholder="0"
              min="0"
              step="1000"
              required
            />
            <p v-if="editForm.errors.price" class="text-xs text-red-500 mt-1">{{ editForm.errors.price }}</p>
          </div>

          <div>
            <Label for="edit-description">Description *</Label>
            <textarea 
              id="edit-description"
              v-model="editForm.description"
              class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
              placeholder="Describe the service plan..."
              required
            ></textarea>
            <p v-if="editForm.errors.description" class="text-xs text-red-500 mt-1">{{ editForm.errors.description }}</p>
          </div>

          <div>
            <Label>Features</Label>
            <div class="grid grid-cols-2 gap-2 mt-2">
              <label v-for="feature in availableFeatures" :key="feature" class="flex items-center space-x-2">
                <input 
                  type="checkbox" 
                  :checked="editForm.features[feature]"
                  @change="toggleFeature(feature, editForm)"
                  class="rounded border-gray-300"
                >
                <span class="text-sm">{{ feature.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}</span>
              </label>
            </div>
          </div>

          <div class="flex items-center space-x-2">
            <input 
              id="edit-active"
              v-model="editForm.is_active"
              type="checkbox"
              class="rounded border-gray-300"
            >
            <Label for="edit-active">Active</Label>
          </div>

          <div class="flex justify-end space-x-2 pt-4">
            <Button type="button" variant="outline" @click="showEditModal = false">
              Cancel
            </Button>
            <Button type="submit" :disabled="editForm.processing">
              {{ editForm.processing ? 'Updating...' : 'Update Service Plan' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>