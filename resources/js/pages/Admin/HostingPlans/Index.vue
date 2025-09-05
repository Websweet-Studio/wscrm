<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Search, Plus, Edit, Trash2, Server, HardDrive, Cpu, MemoryStick, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface HostingPlan {
  id: number;
  plan_name: string;
  storage_gb: number;
  cpu_cores: number;
  ram_gb: number;
  bandwidth: string;
  modal_cost: number;
  maintenance_cost: number;
  discount_percent: number;
  selling_price: number;
  features: string[];
  is_active: boolean;
}

interface Props {
  hostingPlans: {
    data: HostingPlan[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: any[];
  };
  filters: {
    search?: string;
  };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedPlan = ref<HostingPlan | null>(null);

const createForm = useForm({
  plan_name: '',
  storage_gb: '',
  cpu_cores: '',
  ram_gb: '',
  bandwidth: '',
  modal_cost: '',
  maintenance_cost: '',
  discount_percent: '0',
  selling_price: '',
  features: [''],
  is_active: true,
});

const editForm = useForm({
  plan_name: '',
  storage_gb: '',
  cpu_cores: '',
  ram_gb: '',
  bandwidth: '',
  modal_cost: '',
  maintenance_cost: '',
  discount_percent: '0',
  selling_price: '',
  features: [''],
  is_active: true,
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Hosting Plans', href: '/admin/hosting-plans' },
];

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price);
};

const handleSearch = () => {
  router.get('/admin/hosting-plans', { search: search.value }, { 
    preserveState: true,
    replace: true 
  });
};

const addFeature = (form: any) => {
  form.features.push('');
};

const removeFeature = (form: any, index: number) => {
  form.features.splice(index, 1);
};

const submitCreate = () => {
  createForm.post('/admin/hosting-plans', {
    onSuccess: () => {
      showCreateModal.value = false;
      createForm.reset();
      createForm.features = [''];
    },
  });
};

const openEditModal = (plan: HostingPlan) => {
  selectedPlan.value = plan;
  editForm.reset();
  editForm.plan_name = plan.plan_name;
  editForm.storage_gb = plan.storage_gb;
  editForm.cpu_cores = plan.cpu_cores;
  editForm.ram_gb = plan.ram_gb;
  editForm.bandwidth = plan.bandwidth;
  editForm.modal_cost = plan.modal_cost;
  editForm.maintenance_cost = plan.maintenance_cost;
  editForm.discount_percent = plan.discount_percent;
  editForm.selling_price = plan.selling_price;
  editForm.features = plan.features.length > 0 ? [...plan.features] : [''];
  editForm.is_active = plan.is_active;
  showEditModal.value = true;
};

const submitEdit = () => {
  if (!selectedPlan.value) return;
  
  editForm.put(`/admin/hosting-plans/${selectedPlan.value.id}`, {
    onSuccess: () => {
      showEditModal.value = false;
      editForm.reset();
      selectedPlan.value = null;
    },
  });
};

const deleteHostingPlan = (id: number) => {
  if (confirm('Are you sure you want to delete this hosting plan?')) {
    router.delete(`/admin/hosting-plans/${id}`);
  }
};
</script>

<template>
  <Head title="Hosting Plans Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Hosting Plans</h1>
          <p class="text-muted-foreground">Manage hosting plan configurations</p>
        </div>
        <Button @click="showCreateModal = true">
          <Plus class="h-4 w-4 mr-2" />
          Add Hosting Plan
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Hosting Plans</CardTitle>
          <CardDescription>
            Manage hosting plans and their specifications
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="mb-4 flex items-center space-x-2">
            <div class="relative flex-1 max-w-sm">
              <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="search"
                placeholder="Search hosting plans..."
                class="pl-8"
                @keyup.enter="handleSearch"
              />
            </div>
            <Button @click="handleSearch">Search</Button>
          </div>

          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Plan Name</TableHead>
                  <TableHead>Specifications</TableHead>
                  <TableHead>Selling Price</TableHead>
                  <TableHead>Costs</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead class="w-[100px]">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="plan in hostingPlans.data" :key="plan.id">
                  <TableCell class="font-medium">{{ plan.plan_name }}</TableCell>
                  <TableCell>
                    <div class="flex flex-wrap gap-2 text-xs">
                      <div class="flex items-center space-x-1 bg-muted px-2 py-1 rounded">
                        <HardDrive class="h-3 w-3" />
                        <span>{{ plan.storage_gb }}GB Storage</span>
                      </div>
                      <div class="flex items-center space-x-1 bg-muted px-2 py-1 rounded">
                        <Cpu class="h-3 w-3" />
                        <span>{{ plan.cpu_cores }} CPU</span>
                      </div>
                      <div class="flex items-center space-x-1 bg-muted px-2 py-1 rounded">
                        <MemoryStick class="h-3 w-3" />
                        <span>{{ plan.ram_gb }}GB RAM</span>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>{{ formatPrice(plan.selling_price) }}</TableCell>
                  <TableCell>
                    <div class="text-xs space-y-1">
                      <div>Modal: {{ formatPrice(plan.modal_cost) }}</div>
                      <div>Maintenance: {{ formatPrice(plan.maintenance_cost) }}</div>
                      <div v-if="plan.discount_percent > 0" class="text-green-600">{{ plan.discount_percent }}% discount</div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge :variant="plan.is_active ? 'default' : 'secondary'">
                      {{ plan.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div class="flex items-center space-x-2">
                      <Button size="sm" variant="outline" @click="openEditModal(plan)">
                        <Edit class="h-3 w-3" />
                      </Button>
                      <Button 
                        size="sm" 
                        variant="outline"
                        @click="deleteHostingPlan(plan.id)"
                      >
                        <Trash2 class="h-3 w-3" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <!-- Pagination -->
          <div class="flex items-center justify-between pt-4">
            <div class="text-sm text-muted-foreground">
              Showing {{ (hostingPlans.current_page - 1) * hostingPlans.per_page + 1 }} to 
              {{ Math.min(hostingPlans.current_page * hostingPlans.per_page, hostingPlans.total) }} of 
              {{ hostingPlans.total }} results
            </div>
            <div class="flex items-center space-x-2">
              <template v-for="link in hostingPlans.links" :key="link.label">
                <Button 
                  v-if="link.url" 
                  variant="outline" 
                  size="sm" 
                  :disabled="!link.url"
                  @click="router.visit(link.url)"
                  v-html="link.label"
                />
              </template>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Create Hosting Plan Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-2xl mx-4 p-6 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-semibold">Add New Hosting Plan</h2>
            <p class="text-sm text-muted-foreground">Create a new hosting plan with specifications and pricing</p>
          </div>
          <button @click="showCreateModal = false" class="text-gray-500 hover:text-gray-700">
            <X class="h-4 w-4" />
          </button>
        </div>
        
        <form @submit.prevent="submitCreate" class="space-y-4">
          <div>
            <Label for="create-plan-name">Plan Name *</Label>
            <Input
              id="create-plan-name"
              v-model="createForm.plan_name"
              placeholder="Basic Plan"
              :class="{ 'border-red-500': createForm.errors.plan_name }"
              required
            />
            <p v-if="createForm.errors.plan_name" class="text-xs text-red-500 mt-1">{{ createForm.errors.plan_name }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="create-storage">Storage (GB) *</Label>
              <Input
                id="create-storage"
                v-model="createForm.storage_gb"
                type="number"
                step="0.01"
                min="0"
                :class="{ 'border-red-500': createForm.errors.storage_gb }"
                required
              />
              <p v-if="createForm.errors.storage_gb" class="text-xs text-red-500 mt-1">{{ createForm.errors.storage_gb }}</p>
            </div>
            <div>
              <Label for="create-bandwidth">Bandwidth *</Label>
              <Input
                id="create-bandwidth"
                v-model="createForm.bandwidth"
                placeholder="Unlimited"
                :class="{ 'border-red-500': createForm.errors.bandwidth }"
                required
              />
              <p v-if="createForm.errors.bandwidth" class="text-xs text-red-500 mt-1">{{ createForm.errors.bandwidth }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="create-cpu">CPU Cores *</Label>
              <Input
                id="create-cpu"
                v-model="createForm.cpu_cores"
                type="number"
                step="0.1"
                min="0.1"
                :class="{ 'border-red-500': createForm.errors.cpu_cores }"
                required
              />
              <p v-if="createForm.errors.cpu_cores" class="text-xs text-red-500 mt-1">{{ createForm.errors.cpu_cores }}</p>
            </div>
            <div>
              <Label for="create-ram">RAM (GB) *</Label>
              <Input
                id="create-ram"
                v-model="createForm.ram_gb"
                type="number"
                step="0.1"
                min="0"
                :class="{ 'border-red-500': createForm.errors.ram_gb }"
                required
              />
              <p v-if="createForm.errors.ram_gb" class="text-xs text-red-500 mt-1">{{ createForm.errors.ram_gb }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="create-modal-cost">Modal Cost (IDR) *</Label>
              <Input
                id="create-modal-cost"
                v-model="createForm.modal_cost"
                type="number"
                step="0.01"
                min="0"
                :class="{ 'border-red-500': createForm.errors.modal_cost }"
                required
              />
              <p v-if="createForm.errors.modal_cost" class="text-xs text-red-500 mt-1">{{ createForm.errors.modal_cost }}</p>
            </div>
            <div>
              <Label for="create-maintenance-cost">Maintenance Cost (IDR) *</Label>
              <Input
                id="create-maintenance-cost"
                v-model="createForm.maintenance_cost"
                type="number"
                step="0.01"
                min="0"
                :class="{ 'border-red-500': createForm.errors.maintenance_cost }"
                required
              />
              <p v-if="createForm.errors.maintenance_cost" class="text-xs text-red-500 mt-1">{{ createForm.errors.maintenance_cost }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="create-discount">Discount Percent *</Label>
              <Input
                id="create-discount"
                v-model="createForm.discount_percent"
                type="number"
                step="0.01"
                min="0"
                max="100"
                :class="{ 'border-red-500': createForm.errors.discount_percent }"
                required
              />
              <p v-if="createForm.errors.discount_percent" class="text-xs text-red-500 mt-1">{{ createForm.errors.discount_percent }}</p>
            </div>
            <div>
              <Label for="create-selling-price">Selling Price (IDR) *</Label>
              <Input
                id="create-selling-price"
                v-model="createForm.selling_price"
                type="number"
                step="0.01"
                min="0"
                :class="{ 'border-red-500': createForm.errors.selling_price }"
                required
              />
              <p v-if="createForm.errors.selling_price" class="text-xs text-red-500 mt-1">{{ createForm.errors.selling_price }}</p>
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between mb-2">
              <Label>Features</Label>
              <Button type="button" size="sm" @click="addFeature(createForm)">
                <Plus class="h-3 w-3 mr-1" />
                Add Feature
              </Button>
            </div>
            <div v-for="(feature, index) in createForm.features" :key="index" class="flex items-center gap-2 mb-2">
              <Input
                v-model="createForm.features[index]"
                placeholder="Feature description"
                class="flex-1"
              />
              <Button 
                v-if="createForm.features.length > 1"
                type="button" 
                size="sm" 
                variant="outline" 
                @click="removeFeature(createForm, index)"
              >
                <Trash2 class="h-3 w-3" />
              </Button>
            </div>
          </div>

          <div class="flex items-center space-x-2">
            <input
              id="create-is-active"
              type="checkbox"
              v-model="createForm.is_active"
              class="rounded border border-input"
            />
            <Label for="create-is-active">Active</Label>
          </div>

          <div class="flex justify-end space-x-2 pt-4">
            <Button type="button" variant="outline" @click="showCreateModal = false">
              Cancel
            </Button>
            <Button type="submit" :disabled="createForm.processing">
              {{ createForm.processing ? 'Creating...' : 'Create Plan' }}
            </Button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Hosting Plan Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-2xl mx-4 p-6 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-semibold">Edit Hosting Plan</h2>
            <p class="text-sm text-muted-foreground">Update hosting plan specifications and settings</p>
          </div>
          <button @click="showEditModal = false" class="text-gray-500 hover:text-gray-700">
            <X class="h-4 w-4" />
          </button>
        </div>
        <form @submit.prevent="submitEdit" class="space-y-4">
          <div>
            <Label for="edit-plan-name">Plan Name *</Label>
            <Input
              id="edit-plan-name"
              v-model="editForm.plan_name"
              placeholder="Basic Plan"
              :class="{ 'border-red-500': editForm.errors.plan_name }"
              required
            />
            <p v-if="editForm.errors.plan_name" class="text-xs text-red-500 mt-1">{{ editForm.errors.plan_name }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="edit-storage">Storage (GB) *</Label>
              <Input
                id="edit-storage"
                v-model="editForm.storage_gb"
                type="number"
                step="0.01"
                min="0"
                :class="{ 'border-red-500': editForm.errors.storage_gb }"
                required
              />
              <p v-if="editForm.errors.storage_gb" class="text-xs text-red-500 mt-1">{{ editForm.errors.storage_gb }}</p>
            </div>
            <div>
              <Label for="edit-bandwidth">Bandwidth *</Label>
              <Input
                id="edit-bandwidth"
                v-model="editForm.bandwidth"
                placeholder="Unlimited"
                :class="{ 'border-red-500': editForm.errors.bandwidth }"
                required
              />
              <p v-if="editForm.errors.bandwidth" class="text-xs text-red-500 mt-1">{{ editForm.errors.bandwidth }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="edit-cpu">CPU Cores *</Label>
              <Input
                id="edit-cpu"
                v-model="editForm.cpu_cores"
                type="number"
                step="0.1"
                min="0.1"
                :class="{ 'border-red-500': editForm.errors.cpu_cores }"
                required
              />
              <p v-if="editForm.errors.cpu_cores" class="text-xs text-red-500 mt-1">{{ editForm.errors.cpu_cores }}</p>
            </div>
            <div>
              <Label for="edit-ram">RAM (GB) *</Label>
              <Input
                id="edit-ram"
                v-model="editForm.ram_gb"
                type="number"
                step="0.1"
                min="0"
                :class="{ 'border-red-500': editForm.errors.ram_gb }"
                required
              />
              <p v-if="editForm.errors.ram_gb" class="text-xs text-red-500 mt-1">{{ editForm.errors.ram_gb }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="edit-modal-cost">Modal Cost (IDR) *</Label>
              <Input
                id="edit-modal-cost"
                v-model="editForm.modal_cost"
                type="number"
                step="0.01"
                min="0"
                :class="{ 'border-red-500': editForm.errors.modal_cost }"
                required
              />
              <p v-if="editForm.errors.modal_cost" class="text-xs text-red-500 mt-1">{{ editForm.errors.modal_cost }}</p>
            </div>
            <div>
              <Label for="edit-maintenance-cost">Maintenance Cost (IDR) *</Label>
              <Input
                id="edit-maintenance-cost"
                v-model="editForm.maintenance_cost"
                type="number"
                step="0.01"
                min="0"
                :class="{ 'border-red-500': editForm.errors.maintenance_cost }"
                required
              />
              <p v-if="editForm.errors.maintenance_cost" class="text-xs text-red-500 mt-1">{{ editForm.errors.maintenance_cost }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="edit-discount">Discount Percent *</Label>
              <Input
                id="edit-discount"
                v-model="editForm.discount_percent"
                type="number"
                step="0.01"
                min="0"
                max="100"
                :class="{ 'border-red-500': editForm.errors.discount_percent }"
                required
              />
              <p v-if="editForm.errors.discount_percent" class="text-xs text-red-500 mt-1">{{ editForm.errors.discount_percent }}</p>
            </div>
            <div>
              <Label for="edit-selling-price">Selling Price (IDR) *</Label>
              <Input
                id="edit-selling-price"
                v-model="editForm.selling_price"
                type="number"
                step="0.01"
                min="0"
                :class="{ 'border-red-500': editForm.errors.selling_price }"
                required
              />
              <p v-if="editForm.errors.selling_price" class="text-xs text-red-500 mt-1">{{ editForm.errors.selling_price }}</p>
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between mb-2">
              <Label>Features</Label>
              <Button type="button" size="sm" @click="addFeature(editForm)">
                <Plus class="h-3 w-3 mr-1" />
                Add Feature
              </Button>
            </div>
            <div v-for="(feature, index) in editForm.features" :key="index" class="flex items-center gap-2 mb-2">
              <Input
                v-model="editForm.features[index]"
                placeholder="Feature description"
                class="flex-1"
              />
              <Button 
                v-if="editForm.features.length > 1"
                type="button" 
                size="sm" 
                variant="outline" 
                @click="removeFeature(editForm, index)"
              >
                <Trash2 class="h-3 w-3" />
              </Button>
            </div>
          </div>

          <div class="flex items-center space-x-2">
            <input
              id="edit-is-active"
              type="checkbox"
              v-model="editForm.is_active"
              class="rounded border border-input"
            />
            <Label for="edit-is-active">Active</Label>
          </div>

          <div class="flex justify-end space-x-2 pt-4">
            <Button type="button" variant="outline" @click="showEditModal = false">
              Cancel
            </Button>
            <Button type="submit" :disabled="editForm.processing">
              {{ editForm.processing ? 'Updating...' : 'Update Plan' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>