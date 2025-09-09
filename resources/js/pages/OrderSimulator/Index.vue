<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Calculator, Globe, Server, Package, Percent, Receipt, ShoppingCart, Download } from 'lucide-vue-next';
import { ref, reactive, computed } from 'vue';
import axios from 'axios';

interface DomainPrice {
  id: number;
  extension: string;
  price: number;
  label: string;
}

interface HostingPlan {
  id: number;
  plan_name: string;
  storage_gb: number;
  cpu_cores: number;
  ram_gb: number;
  price: number;
  label: string;
}

interface ServicePlan {
  id: number;
  name: string;
  category: string;
  price: number;
  description: string;
  label: string;
}

interface OrderItem {
  type: string;
  name: string;
  description: string;
  price: number;
  quantity: number;
  total: number;
}

interface Calculation {
  items: OrderItem[];
  subtotal: number;
  discount_type: string;
  discount_percent: number;
  discount_amount: number;
  after_discount: number;
  tax_percent: number;
  tax_amount: number;
  grand_total: number;
}

interface Props {
  domainPrices: DomainPrice[];
  hostingPlans: HostingPlan[];
  servicePlans: ServicePlan[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Order Simulator', href: '/order-simulator' },
];

// Form state
const form = reactive({
  domain_id: null as number | null,
  domain_name: '',
  hosting_id: null as number | null,
  service_ids: [] as number[],
  discount_type: 'percent' as 'percent' | 'nominal',
  discount_percent: 0,
  discount_nominal: 0,
});

// Calculation state
const calculation = ref<Calculation | null>(null);
const isCalculating = ref(false);

// Computed values
const selectedDomain = computed(() => {
  return props.domainPrices.find(d => d.id === form.domain_id);
});

const selectedHosting = computed(() => {
  return props.hostingPlans.find(h => h.id === form.hosting_id);
});

const selectedServices = computed(() => {
  return props.servicePlans.filter(s => form.service_ids.includes(s.id));
});

const hasSelections = computed(() => {
  return form.domain_id || form.hosting_id || form.service_ids.length > 0;
});

// Format currency
const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount);
};

// Calculate order
const calculateOrder = async () => {
  if (!hasSelections.value) {
    alert('Please select at least one item (domain, hosting, or service)');
    return;
  }

  isCalculating.value = true;
  
  
  try {
    const response = await axios.post('/order-simulator/calculate', {
      domain_id: form.domain_id,
      domain_name: form.domain_name,
      hosting_id: form.hosting_id,
      service_ids: form.service_ids,
      discount_type: form.discount_type,
      discount_percent: form.discount_percent,
      discount_nominal: form.discount_nominal,
    });

    if (response.data.success) {
      calculation.value = response.data.calculation;
    }
  } catch (error) {
    console.error('Calculation error:', error);
    alert('Error calculating order. Please try again.');
  } finally {
    isCalculating.value = false;
  }
};

// Reset form
const resetForm = () => {
  form.domain_id = null;
  form.domain_name = '';
  form.hosting_id = null;
  form.service_ids = [];
  form.discount_type = 'percent';
  form.discount_percent = 0;
  form.discount_nominal = 0;
  calculation.value = null;
};

// Download PDF
const downloadPDF = () => {
  if (!calculation.value) return;

  const formData = new FormData();
  formData.append('calculation', JSON.stringify(calculation.value));
  
  axios.post('/order-simulator/download-pdf', formData, {
    responseType: 'blob'
  }).then(response => {
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `Order-Summary-${new Date().toISOString().split('T')[0]}.pdf`;
    link.click();
    window.URL.revokeObjectURL(url);
  }).catch(error => {
    console.error('Error downloading PDF:', error);
    alert('Terjadi kesalahan saat mengunduh PDF. Silakan coba lagi.');
  });
};

// Toggle service selection
const toggleService = (serviceId: number) => {
  const index = form.service_ids.indexOf(serviceId);
  if (index > -1) {
    form.service_ids.splice(index, 1);
  } else {
    form.service_ids.push(serviceId);
  }
};

// Get service category color
const getCategoryColor = (category: string) => {
  switch (category) {
    case 'web_package': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
    case 'addon': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    case 'license': return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
    case 'custom_system': return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
  }
};

// Get category name
const getCategoryName = (category: string) => {
  const categories = {
    'web_package': 'Web Package',
    'addon': 'Add-on',
    'license': 'License',
    'custom_system': 'Custom System'
  };
  return categories[category] || category;
};
</script>

<template>
  <Head title="Order Simulator - WebSweetStudio" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-8 p-6">
      <!-- Hero Section -->
      <div class="text-center space-y-4">
        <div class="flex items-center justify-center gap-3">
          <Calculator class="h-8 w-8 text-primary" />
          <h1 class="text-4xl font-bold tracking-tight">Order Simulator</h1>
        </div>
        <p class="text-xl text-muted-foreground max-w-2xl mx-auto">
          Simulasikan pesanan Anda dan lihat estimasi total biaya dengan berbagai pilihan layanan
        </p>
      </div>

      <div class="grid gap-8 lg:grid-cols-3">
        <!-- Selection Panel -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Domain Selection -->
          <Card>
            <CardHeader>
              <div class="flex items-center gap-2">
                <Globe class="h-5 w-5 text-blue-500" />
                <CardTitle>Pilih Domain</CardTitle>
              </div>
              <CardDescription>Pilih ekstensi domain yang Anda inginkan</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <Label for="domain-name">Nama Domain (opsional)</Label>
                  <Input
                    id="domain-name"
                    v-model="form.domain_name"
                    placeholder="contoh: websweetstudio"
                  />
                </div>
                <div>
                  <Label for="domain-extension">Ekstensi Domain</Label>
                  <select 
                    id="domain-extension"
                    v-model="form.domain_id"
                    class="flex h-9 w-full rounded-md border border-input bg-background dark:bg-gray-800 px-3 py-1 text-sm shadow-sm transition-colors text-foreground dark:text-white placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring cursor-pointer"
                  >
                    <option :value="null">Pilih Domain</option>
                    <option v-for="domain in domainPrices" :key="domain.id" :value="domain.id">
                      {{ domain.label }}
                    </option>
                  </select>
                </div>
              </div>
              
              <div v-if="selectedDomain" class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <div class="flex items-center justify-between">
                  <span class="font-medium">
                    {{ form.domain_name || 'example' }}{{ selectedDomain.extension }}
                  </span>
                  <span class="text-blue-600 font-bold">{{ formatCurrency(selectedDomain.price) }}/tahun</span>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Hosting Selection -->
          <Card>
            <CardHeader>
              <div class="flex items-center gap-2">
                <Server class="h-5 w-5 text-green-500" />
                <CardTitle>Pilih Hosting</CardTitle>
              </div>
              <CardDescription>Pilih paket hosting yang sesuai kebutuhan</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <select 
                v-model="form.hosting_id"
                class="flex h-9 w-full rounded-md border border-input bg-background dark:bg-gray-800 px-3 py-1 text-sm shadow-sm transition-colors text-foreground dark:text-white placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring cursor-pointer"
              >
                <option :value="null">Pilih Hosting</option>
                <option v-for="hosting in hostingPlans" :key="hosting.id" :value="hosting.id">
                  {{ hosting.label }}
                </option>
              </select>
              
              <div v-if="selectedHosting" class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                  <span class="font-medium">{{ selectedHosting.plan_name }}</span>
                  <span class="text-green-600 font-bold">{{ formatCurrency(selectedHosting.price) }}/tahun</span>
                </div>
                <div class="text-sm text-muted-foreground">
                  {{ selectedHosting.storage_gb }}GB Storage • {{ selectedHosting.cpu_cores }} CPU Cores • {{ selectedHosting.ram_gb }}GB RAM
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Service Selection -->
          <Card>
            <CardHeader>
              <div class="flex items-center gap-2">
                <Package class="h-5 w-5 text-purple-500" />
                <CardTitle>Pilih Layanan Tambahan</CardTitle>
              </div>
              <CardDescription>Pilih layanan yang Anda butuhkan (dapat memilih lebih dari satu)</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-3">
                <div 
                  v-for="service in servicePlans" 
                  :key="service.id"
                  class="p-3 border rounded-lg cursor-pointer transition-all hover:shadow-md"
                  :class="form.service_ids.includes(service.id) ? 'border-primary bg-primary/5' : 'border-border'"
                  @click="toggleService(service.id)"
                >
                  <div class="flex items-start justify-between mb-2">
                    <div class="flex-1">
                      <div class="flex items-center gap-2 mb-1">
                        <span class="font-medium">{{ service.name }}</span>
                        <Badge :class="getCategoryColor(service.category)" class="text-xs">
                          {{ getCategoryName(service.category) }}
                        </Badge>
                      </div>
                      <p class="text-sm text-muted-foreground">{{ service.description }}</p>
                    </div>
                    <div class="text-right">
                      <div class="font-bold text-purple-600">
                        {{ service.price > 0 ? formatCurrency(service.price) : 'Hubungi Kami' }}
                      </div>
                      <input 
                        type="checkbox" 
                        :checked="form.service_ids.includes(service.id)"
                        class="mt-1 cursor-pointer"
                        @click.stop
                      />
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="selectedServices.length > 0" class="mt-4 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                <h4 class="font-medium mb-2">Layanan Terpilih:</h4>
                <div class="space-y-1">
                  <div v-for="service in selectedServices" :key="service.id" class="flex justify-between text-sm">
                    <span>{{ service.name }}</span>
                    <span class="font-medium">{{ service.price > 0 ? formatCurrency(service.price) : 'Custom' }}</span>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Discount Section -->
          <Card>
            <CardHeader>
              <div class="flex items-center gap-2">
                <Percent class="h-5 w-5 text-orange-500" />
                <CardTitle>Diskon</CardTitle>
              </div>
              <CardDescription>Pilih tipe diskon dan masukkan nilai diskon</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <!-- Discount Type Selection -->
              <div class="flex items-center gap-4">
                <Label class="text-sm font-medium">Tipe Diskon:</Label>
                <div class="flex items-center gap-4">
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input
                      type="radio"
                      v-model="form.discount_type"
                      value="percent"
                      class="text-primary cursor-pointer"
                    />
                    <span class="text-sm">Persentase (%)</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input
                      type="radio"
                      v-model="form.discount_type"
                      value="nominal"
                      class="text-primary cursor-pointer"
                    />
                    <span class="text-sm">Nominal (Rp)</span>
                  </label>
                </div>
              </div>

              <!-- Discount Input -->
              <div class="flex items-center gap-4">
                <Label :for="`discount-${form.discount_type}`">
                  {{ form.discount_type === 'percent' ? 'Diskon (%)' : 'Diskon (Rp)' }}
                </Label>
                <div class="flex items-center gap-2">
                  <Input
                    v-if="form.discount_type === 'percent'"
                    :id="`discount-${form.discount_type}`"
                    type="number"
                    :min="0"
                    :max="100"
                    v-model="form.discount_percent"
                    placeholder="0"
                    class="max-w-[150px]"
                  />
                  <Input
                    v-else
                    :id="`discount-${form.discount_type}`"
                    type="number"
                    :min="0"
                    v-model="form.discount_nominal"
                    placeholder="0"
                    class="max-w-[150px]"
                  />
                  <span class="text-sm text-muted-foreground">
                    {{ form.discount_type === 'percent' ? '%' : 'Rp' }}
                  </span>
                </div>
              </div>
              
              <!-- Discount Preview -->
              <div v-if="(form.discount_type === 'percent' && form.discount_percent > 0) || (form.discount_type === 'nominal' && form.discount_nominal > 0)" 
                   class="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                <div class="text-sm">
                  <span class="font-medium">Diskon yang dipilih:</span>
                  <span v-if="form.discount_type === 'percent'">
                    {{ form.discount_percent }}%
                  </span>
                  <span v-else>
                    {{ formatCurrency(form.discount_nominal) }}
                  </span>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Action Buttons -->
          <div class="flex gap-4">
            <Button 
              @click="calculateOrder" 
              :disabled="!hasSelections || isCalculating"
              size="lg"
              class="flex-1 cursor-pointer disabled:cursor-not-allowed"
            >
              <Calculator class="h-4 w-4 mr-2" />
              {{ isCalculating ? 'Menghitung...' : 'Hitung Total' }}
            </Button>
            <Button variant="outline" size="lg" @click="resetForm" class="cursor-pointer">
              Reset
            </Button>
          </div>
        </div>

        <!-- Calculation Result -->
        <div class="lg:col-span-1">
          <Card class="sticky top-6">
            <CardHeader>
              <div class="flex items-center gap-2">
                <Receipt class="h-5 w-5 text-indigo-500" />
                <CardTitle>Ringkasan Pesanan</CardTitle>
              </div>
              <CardDescription>Estimasi total biaya pesanan Anda</CardDescription>
            </CardHeader>
            <CardContent>
              <div v-if="!calculation" class="text-center py-8 text-muted-foreground">
                <ShoppingCart class="h-12 w-12 mx-auto mb-3 text-muted-foreground/50" />
                <p>Pilih item dan klik "Hitung Total" untuk melihat estimasi biaya</p>
              </div>

              <div v-else class="space-y-4">
                <!-- Order Items -->
                <div class="space-y-2">
                  <h4 class="font-medium">Item Pesanan:</h4>
                  <div v-for="item in calculation.items" :key="item.name" class="flex justify-between text-sm">
                    <div>
                      <div class="font-medium">{{ item.name }}</div>
                      <div class="text-muted-foreground text-xs">{{ item.description }}</div>
                    </div>
                    <div class="text-right">
                      <div>{{ formatCurrency(item.total) }}</div>
                    </div>
                  </div>
                </div>

                <hr />

                <!-- Calculation Breakdown -->
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span>{{ formatCurrency(calculation.subtotal) }}</span>
                  </div>
                  
                  <div v-if="calculation.discount_amount > 0" class="flex justify-between text-green-600">
                    <span>Diskon 
                      <template v-if="calculation.discount_type === 'percent'">
                        ({{ Math.round(calculation.discount_percent * 100) / 100 }}%)
                      </template>
                      <template v-else>
                        (Nominal)
                      </template>:
                    </span>
                    <span>-{{ formatCurrency(calculation.discount_amount) }}</span>
                  </div>
                  
                  <div v-if="calculation.discount_percent > 0" class="flex justify-between">
                    <span>Setelah Diskon:</span>
                    <span>{{ formatCurrency(calculation.after_discount) }}</span>
                  </div>
                  
                  <div v-if="calculation.tax_percent > 0" class="flex justify-between">
                    <span>PPN ({{ calculation.tax_percent }}%):</span>
                    <span>{{ formatCurrency(calculation.tax_amount) }}</span>
                  </div>
                </div>

                <hr />

                <!-- Grand Total -->
                <div class="flex justify-between font-bold text-lg">
                  <span>Total:</span>
                  <span class="text-primary">{{ formatCurrency(calculation.grand_total) }}</span>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2 mt-4">
                  <Button class="w-full cursor-pointer" size="lg">
                    <ShoppingCart class="h-4 w-4 mr-2" />
                    Buat Pesanan
                  </Button>
                  <Button variant="outline" class="w-full cursor-pointer" @click="downloadPDF">
                    <Download class="h-4 w-4 mr-2" />
                    Download PDF
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>