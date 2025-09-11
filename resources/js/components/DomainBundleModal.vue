<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Check, Server, ShoppingCart, X, Globe } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface HostingPlan {
    id: number;
    plan_name: string;
    storage_gb: number;
    cpu_cores: number;
    ram_gb: number;
    bandwidth: string;
    selling_price: number;
    discount_percent: number;
    features: string[];
    is_active: boolean;
}

interface DomainPrice {
    id: number;
    extension: string;
    selling_price: number;
}

interface Props {
    open: boolean;
    domainPrice: DomainPrice;
    hostingPlan?: HostingPlan;
    hostingPlans: HostingPlan[];
}

interface Emits {
    'update:open': [value: boolean];
    'select-bundle': [hostingPlan: HostingPlan, domainPrice: DomainPrice];
    'select-domain-only': [domainPrice: DomainPrice];
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// State untuk hosting plan yang dipilih
const selectedHostingPlan = ref<HostingPlan | null>(null);
const selectedHostingPlanId = ref<string>('');

// Set default hosting plan (basic 2GB)
watch(() => props.hostingPlans, (plans) => {
    if (plans && plans.length > 0) {
        // Cari paket basic 2GB atau ambil yang pertama
        const basicPlan = plans.find(plan => 
            plan.plan_name.toLowerCase().includes('basic') || 
            plan.plan_name.toLowerCase().includes('2gb')
        ) || plans[0];
        selectedHostingPlan.value = basicPlan;
        selectedHostingPlanId.value = basicPlan.id.toString();
    }
}, { immediate: true });

const onHostingPlanChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    const planId = parseInt(target.value);
    const plan = props.hostingPlans.find(p => p.id === planId);
    if (plan) {
        selectedHostingPlan.value = plan;
    }
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const getDiscountedPrice = (price: number, discount: number) => {
    return price * (1 - discount / 100);
};

const bundlePrice = computed(() => {
    if (!selectedHostingPlan.value) return props.domainPrice.selling_price;
    const hostingPrice = getDiscountedPrice(selectedHostingPlan.value.selling_price, selectedHostingPlan.value.discount_percent);
    return hostingPrice + props.domainPrice.selling_price;
});

const bundleSavings = computed(() => {
    if (!selectedHostingPlan.value) return 0;
    // Assume 10% discount for bundle
    return bundlePrice.value * 0.1;
});

const finalBundlePrice = computed(() => {
    return bundlePrice.value - bundleSavings.value;
});

const handleSelectBundle = () => {
    if (selectedHostingPlan.value) {
        emit('select-bundle', selectedHostingPlan.value, props.domainPrice);
    }
    emit('update:open', false);
};

const handleSelectDomainOnly = () => {
    emit('select-domain-only', props.domainPrice);
    emit('update:open', false);
};
</script>

<template>
    <!-- Custom Modal Implementation -->
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50" @click="emit('update:open', false)"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl max-h-[90vh] w-full mx-4 overflow-y-auto">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b">
                <div>
                    <h2 class="text-xl font-semibold flex items-center gap-2">
                        <Globe class="h-6 w-6 text-blue-600" />
                        Pilih Paket Domain
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Dapatkan penawaran terbaik dengan bundling hosting + domain
                    </p>
                </div>
                <button @click="emit('update:open', false)" class="text-gray-500 hover:text-gray-700 cursor-pointer">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Bundle Option -->
                    <Card v-if="hostingPlan" class="relative border-2 border-blue-200 bg-gradient-to-br from-blue-50 to-indigo-50 hover:shadow-lg transition-all">
                        <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                            <Badge class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-1">
                                💰 HEMAT {{ formatPrice(bundleSavings) }}
                            </Badge>
                        </div>
                        
                        <CardHeader class="text-center pt-8">
                            <div class="mb-4 flex justify-center">
                                <div class="rounded-full bg-blue-100 p-4">
                                    <Server class="h-8 w-8 text-blue-600" />
                                </div>
                            </div>
                            <CardTitle class="text-xl text-blue-900">Bundle Hosting + Domain</CardTitle>
                            <p class="text-sm text-gray-600">Solusi lengkap untuk website Anda</p>
                        </CardHeader>

                        <CardContent class="space-y-4">
                            <!-- Domain Info -->
                            <div class="bg-white rounded-lg p-3 border">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900">.{{ domainPrice.extension }} Domain</span>
                                    <span class="text-gray-600">{{ formatPrice(domainPrice.selling_price) }}</span>
                                </div>
                            </div>

                            <!-- Hosting Plan Selector -->
                             <div class="space-y-3">
                                  <label class="text-sm font-medium text-gray-700">Pilih Paket Hosting:</label>
                                  <select 
                                      v-model="selectedHostingPlanId"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      @change="onHostingPlanChange"
                                  >
                                      <option 
                                          v-for="plan in hostingPlans" 
                                          :key="plan.id" 
                                          :value="plan.id.toString()"
                                      >
                                          {{ plan.plan_name }} - {{ plan.storage_gb }}GB - {{ formatPrice(getDiscountedPrice(plan.selling_price, plan.discount_percent)) }}
                                      </option>
                                  </select>
                              </div>

                            <!-- Selected Hosting Info -->
                            <div v-if="selectedHostingPlan" class="bg-white rounded-lg p-3 border">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-gray-900">{{ selectedHostingPlan.plan_name }}</span>
                                    <div class="text-right">
                                        <div v-if="selectedHostingPlan.discount_percent > 0" class="text-xs text-gray-500 line-through">
                                            {{ formatPrice(selectedHostingPlan.selling_price) }}
                                        </div>
                                        <span class="text-gray-600">
                                            {{ formatPrice(getDiscountedPrice(selectedHostingPlan.selling_price, selectedHostingPlan.discount_percent)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 text-xs text-gray-500">
                                    <span>{{ selectedHostingPlan.storage_gb }}GB Storage</span>
                                    <span>{{ selectedHostingPlan.cpu_cores }} CPU</span>
                                    <span>{{ selectedHostingPlan.ram_gb }}GB RAM</span>
                                </div>
                            </div>

                            <!-- Bundle Features -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-semibold text-gray-900">Keuntungan Bundle:</h4>
                                <ul class="space-y-1">
                                    <li class="flex items-center gap-2 text-sm text-gray-600">
                                        <Check class="h-3 w-3 text-green-500" />
                                        <span>Hemat {{ formatPrice(bundleSavings) }}</span>
                                    </li>
                                    <li class="flex items-center gap-2 text-sm text-gray-600">
                                        <Check class="h-3 w-3 text-green-500" />
                                        <span>Setup otomatis domain ke hosting</span>
                                    </li>
                                    <li class="flex items-center gap-2 text-sm text-gray-600">
                                        <Check class="h-3 w-3 text-green-500" />
                                        <span>Support prioritas</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Bundle Price -->
                            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg p-4 text-center">
                                <div class="text-sm opacity-90">Total Harga Bundle</div>
                                <div class="text-xs opacity-75 line-through">{{ formatPrice(bundlePrice) }}</div>
                                <div class="text-2xl font-bold">{{ formatPrice(finalBundlePrice) }}</div>
                                <div class="text-xs opacity-90">per tahun</div>
                            </div>

                            <Button @click="handleSelectBundle" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700" size="lg">
                                <ShoppingCart class="mr-2 h-4 w-4" />
                                Pilih Bundle (Hemat {{ formatPrice(bundleSavings) }})
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Domain Only Option -->
                    <Card class="border-2 border-gray-200 hover:shadow-lg transition-all">
                        <CardHeader class="text-center">
                            <div class="mb-4 flex justify-center">
                                <div class="rounded-full bg-gray-100 p-4">
                                    <Globe class="h-8 w-8 text-gray-600" />
                                </div>
                            </div>
                            <CardTitle class="text-xl text-gray-900">Domain Saja</CardTitle>
                            <p class="text-sm text-gray-600">Hanya registrasi domain</p>
                        </CardHeader>

                        <CardContent class="space-y-4">
                            <!-- Domain Info -->
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <div class="text-lg font-semibold text-gray-900">.{{ domainPrice.extension }} Domain</div>
                                <div class="text-2xl font-bold text-gray-900 mt-2">{{ formatPrice(domainPrice.selling_price) }}</div>
                                <div class="text-sm text-gray-600">per tahun</div>
                            </div>

                            <!-- Domain Features -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-semibold text-gray-900">Yang Anda Dapatkan:</h4>
                                <ul class="space-y-1">
                                    <li class="flex items-center gap-2 text-sm text-gray-600">
                                        <Check class="h-3 w-3 text-green-500" />
                                        <span>Registrasi domain 1 tahun</span>
                                    </li>
                                    <li class="flex items-center gap-2 text-sm text-gray-600">
                                        <Check class="h-3 w-3 text-green-500" />
                                        <span>DNS management</span>
                                    </li>
                                    <li class="flex items-center gap-2 text-sm text-gray-600">
                                        <Check class="h-3 w-3 text-green-500" />
                                        <span>Domain forwarding</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="pt-4">
                                <Button @click="handleSelectDomainOnly" variant="outline" class="w-full border-gray-300 hover:bg-gray-50" size="lg">
                                    <Globe class="mr-2 h-4 w-4" />
                                    Pilih Domain Saja
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Additional Info -->
                <div class="mt-6 text-center text-sm text-gray-500">
                    <p>💡 <strong>Tips:</strong> Dengan bundle hosting + domain, Anda bisa langsung memulai website tanpa konfigurasi tambahan!</p>
                </div>
            </div>
        </div>
    </div>
</template>