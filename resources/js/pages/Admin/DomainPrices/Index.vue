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
import { Search, Plus, Edit, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface DomainPrice {
  id: number;
  extension: string;
  base_cost: number;
  renewal_cost: number;
  selling_price: number;
  renewal_price_with_tax: number;
  is_active: boolean;
}

interface Props {
  domainPrices: {
    data: DomainPrice[];
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
const selectedDomain = ref<DomainPrice | null>(null);

const createForm = useForm({
  extension: '',
  base_cost: '',
  renewal_cost: '',
  selling_price: '',
  renewal_price_with_tax: '',
  is_active: true,
});

const editForm = useForm({
  extension: '',
  base_cost: '',
  renewal_cost: '',
  selling_price: '',
  renewal_price_with_tax: '',
  is_active: true,
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Domain Prices', href: '/admin/domain-prices' },
];

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price);
};

const handleSearch = () => {
  router.get('/admin/domain-prices', { search: search.value }, { 
    preserveState: true,
    replace: true 
  });
};

const submitCreate = () => {
  createForm.post('/admin/domain-prices', {
    onSuccess: () => {
      showCreateModal.value = false;
      createForm.reset();
    },
  });
};

const openEditModal = (domain: DomainPrice) => {
  selectedDomain.value = domain;
  editForm.reset();
  editForm.extension = domain.extension;
  editForm.base_cost = domain.base_cost;
  editForm.renewal_cost = domain.renewal_cost;
  editForm.selling_price = domain.selling_price;
  editForm.renewal_price_with_tax = domain.renewal_price_with_tax;
  editForm.is_active = domain.is_active;
  showEditModal.value = true;
};

const submitEdit = () => {
  if (!selectedDomain.value) return;
  
  editForm.put(`/admin/domain-prices/${selectedDomain.value.id}`, {
    onSuccess: () => {
      showEditModal.value = false;
      editForm.reset();
      selectedDomain.value = null;
    },
  });
};

const deleteDomainPrice = (id: number) => {
  if (confirm('Are you sure you want to delete this domain price?')) {
    router.delete(`/admin/domain-prices/${id}`);
  }
};
</script>

<template>
  <Head title="Domain Prices Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Domain Prices</h1>
          <p class="text-muted-foreground">Manage domain extension pricing</p>
        </div>
        <Button @click="showCreateModal = true">
          <Plus class="h-4 w-4 mr-2" />
          Add Domain Price
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Domain Prices</CardTitle>
          <CardDescription>
            Manage pricing for different domain extensions
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="mb-4 flex items-center space-x-2">
            <div class="relative flex-1 max-w-sm">
              <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="search"
                placeholder="Search domain extensions..."
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
                  <TableHead>Extension</TableHead>
                  <TableHead>Base Cost</TableHead>
                  <TableHead>Renewal Cost</TableHead>
                  <TableHead>Selling Price</TableHead>
                  <TableHead>Renewal w/ Tax</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead class="w-[100px]">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="domain in domainPrices.data" :key="domain.id">
                  <TableCell class="font-medium">.{{ domain.extension }}</TableCell>
                  <TableCell>{{ formatPrice(domain.base_cost) }}</TableCell>
                  <TableCell>{{ formatPrice(domain.renewal_cost) }}</TableCell>
                  <TableCell>{{ formatPrice(domain.selling_price) }}</TableCell>
                  <TableCell>{{ formatPrice(domain.renewal_price_with_tax) }}</TableCell>
                  <TableCell>
                    <Badge :variant="domain.is_active ? 'default' : 'secondary'">
                      {{ domain.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div class="flex items-center space-x-2">
                      <Button size="sm" variant="outline" @click="openEditModal(domain)">
                        <Edit class="h-3 w-3" />
                      </Button>
                      <Button 
                        size="sm" 
                        variant="outline"
                        @click="deleteDomainPrice(domain.id)"
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
              Showing {{ (domainPrices.current_page - 1) * domainPrices.per_page + 1 }} to 
              {{ Math.min(domainPrices.current_page * domainPrices.per_page, domainPrices.total) }} of 
              {{ domainPrices.total }} results
            </div>
            <div class="flex items-center space-x-2">
              <template v-for="link in domainPrices.links" :key="link.label">
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

    <!-- Create Domain Price Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-semibold">Add New Domain Price</h2>
            <p class="text-sm text-muted-foreground">Create a new domain price configuration</p>
          </div>
          <button @click="showCreateModal = false" class="text-gray-500 hover:text-gray-700">
            <X class="h-4 w-4" />
          </button>
        </div>
        <form @submit.prevent="submitCreate" class="space-y-4">
          <div>
            <Label for="create-extension">Domain Extension *</Label>
            <Input
              id="create-extension"
              v-model="createForm.extension"
              placeholder="com"
              :class="{ 'border-red-500': createForm.errors.extension }"
              required
            />
            <p class="text-sm text-muted-foreground mt-1">Enter without the dot (e.g., "com" not ".com")</p>
            <p v-if="createForm.errors.extension" class="text-xs text-red-500 mt-1">{{ createForm.errors.extension }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="create-base-cost">Base Cost (IDR) *</Label>
              <Input
                id="create-base-cost"
                v-model="createForm.base_cost"
                type="number"
                step="0.01"
                min="0"
                :class="{ 'border-red-500': createForm.errors.base_cost }"
                required
              />
              <p v-if="createForm.errors.base_cost" class="text-xs text-red-500 mt-1">{{ createForm.errors.base_cost }}</p>
            </div>
            <div>
              <Label for="create-renewal-cost">Renewal Cost (IDR) *</Label>
              <Input
                id="create-renewal-cost"
                v-model="createForm.renewal_cost"
                type="number"
                step="0.01"
                min="0"
                :class="{ 'border-red-500': createForm.errors.renewal_cost }"
                required
              />
              <p v-if="createForm.errors.renewal_cost" class="text-xs text-red-500 mt-1">{{ createForm.errors.renewal_cost }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
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
            <div>
              <Label for="create-renewal-price-tax">Renewal Price w/ Tax (IDR) *</Label>
              <Input
                id="create-renewal-price-tax"
                v-model="createForm.renewal_price_with_tax"
                type="number"
                step="0.01"
                min="0"
                :class="{ 'border-red-500': createForm.errors.renewal_price_with_tax }"
                required
              />
              <p v-if="createForm.errors.renewal_price_with_tax" class="text-xs text-red-500 mt-1">{{ createForm.errors.renewal_price_with_tax }}</p>
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
              {{ createForm.processing ? 'Creating...' : 'Create Domain Price' }}
            </Button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Domain Price Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-semibold">Edit Domain Price</h2>
            <p class="text-sm text-muted-foreground">Update domain price settings</p>
          </div>
          <button @click="showEditModal = false" class="text-gray-500 hover:text-gray-700">
            <X class="h-4 w-4" />
          </button>
        </div>
        <form @submit.prevent="submitEdit" class="space-y-4">
          <div>
            <Label for="edit-extension">Domain Extension *</Label>
            <Input
              id="edit-extension"
              v-model="editForm.extension"
              placeholder="com"
              :class="{ 'border-red-500': editForm.errors.extension }"
              required
            />
            <p class="text-sm text-muted-foreground mt-1">Enter without the dot (e.g., "com" not ".com")</p>
            <p v-if="editForm.errors.extension" class="text-xs text-red-500 mt-1">{{ editForm.errors.extension }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="edit-base-cost">Base Cost (IDR) *</Label>
              <Input
                id="edit-base-cost"
                v-model="editForm.base_cost"
                type="number"
                step="0.01"
                min="0"
                :class="{ 'border-red-500': editForm.errors.base_cost }"
                required
              />
              <p v-if="editForm.errors.base_cost" class="text-xs text-red-500 mt-1">{{ editForm.errors.base_cost }}</p>
            </div>
            <div>
              <Label for="edit-renewal-cost">Renewal Cost (IDR) *</Label>
              <Input
                id="edit-renewal-cost"
                v-model="editForm.renewal_cost"
                type="number"
                step="0.01"
                min="0"
                :class="{ 'border-red-500': editForm.errors.renewal_cost }"
                required
              />
              <p v-if="editForm.errors.renewal_cost" class="text-xs text-red-500 mt-1">{{ editForm.errors.renewal_cost }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
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
            <div>
              <Label for="edit-renewal-price-tax">Renewal Price w/ Tax (IDR) *</Label>
              <Input
                id="edit-renewal-price-tax"
                v-model="editForm.renewal_price_with_tax"
                type="number"
                step="0.01"
                min="0"
                :class="{ 'border-red-500': editForm.errors.renewal_price_with_tax }"
                required
              />
              <p v-if="editForm.errors.renewal_price_with_tax" class="text-xs text-red-500 mt-1">{{ editForm.errors.renewal_price_with_tax }}</p>
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
              {{ editForm.processing ? 'Updating...' : 'Update Domain Price' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>"}]