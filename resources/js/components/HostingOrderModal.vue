<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
// Using custom modal implementation like Admin Customers page
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
// Using native HTML elements instead of custom components
import { Separator } from '@/components/ui/separator';
import { router } from '@inertiajs/vue3';
import { Check, HardDrive, Search, Server, X } from 'lucide-vue-next';
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

interface ExistingDomain {
    id: number;
    domain_name: string;
    status: string;
}

interface Props {
    open: boolean;
    hostingPlan: HostingPlan;
    domainPrices: DomainPrice[];
    existingDomains: ExistingDomain[];
}

interface Emits {
    (e: 'update:open', value: boolean): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const domainOption = ref<'existing' | 'new'>('new');
const selectedExistingDomain = ref('');
const newDomainName = ref('');
const selectedExtension = ref('');
const isSubmitting = ref(false);

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

// Check if hosting qualifies for bundle discount (2GB+ storage)
const qualifiesForDiscount = computed(() => {
    return props.hostingPlan.storage_gb >= 2;
});

// Calculate hosting price with bundle discount
const hostingPrice = computed(() => {
    const originalPrice = getDiscountedPrice(Number(props.hostingPlan.selling_price), props.hostingPlan.discount_percent);
    if (qualifiesForDiscount.value && domainOption.value === 'new') {
        return originalPrice * 0.9; // 10% discount
    }
    return originalPrice;
});

// Get selected domain price
const domainPrice = computed(() => {
    if (domainOption.value === 'existing') return 0;
    if (!selectedExtension.value) return 0;
    
    const domainData = props.domainPrices.find(d => d.extension === selectedExtension.value);
    return Number(domainData?.selling_price) || 0;
});

// Calculate total price
const totalPrice = computed(() => {
    return hostingPrice.value + domainPrice.value;
});

// Calculate savings
const bundleSavings = computed(() => {
    if (!qualifiesForDiscount.value || domainOption.value !== 'new') return 0;
    const originalHostingPrice = getDiscountedPrice(Number(props.hostingPlan.selling_price), props.hostingPlan.discount_percent);
    return originalHostingPrice * 0.1; // 10% of hosting price
});

// Check if form is valid
const isFormValid = computed(() => {
    if (domainOption.value === 'existing') {
        return selectedExistingDomain.value !== '';
    } else {
        return newDomainName.value !== '' && selectedExtension.value !== '';
    }
});

// Reset form when modal closes
watch(() => props.open, (newValue) => {
    if (!newValue) {
        domainOption.value = 'new';
        selectedExistingDomain.value = '';
        newDomainName.value = '';
        selectedExtension.value = '';
    }
});

const handleSubmit = async () => {
    if (!isFormValid.value) return;
    
    isSubmitting.value = true;
    
    try {
        const orderData = {
            billing_cycle: 'annually',
            items: [
                {
                    item_type: 'hosting',
                    item_id: props.hostingPlan.id,
                    quantity: 1,
                    domain_name: domainOption.value === 'existing' 
                        ? selectedExistingDomain.value 
                        : `${newDomainName.value}${selectedExtension.value}`,
                },
            ],
        };

        // Add domain item if ordering new domain
        if (domainOption.value === 'new') {
            const domainData = props.domainPrices.find(d => d.extension === selectedExtension.value);
            if (domainData) {
                orderData.items.push({
                    item_type: 'domain',
                    item_id: domainData.id,
                    quantity: 1,
                    domain_name: `${newDomainName.value}${selectedExtension.value}`,
                });
            }
        }

        router.post('/customer/orders', orderData, {
            onSuccess: () => {
                emit('update:open', false);
            },
            onError: (errors) => {
                console.error('Order creation failed:', errors);
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    } catch (error) {
        console.error('Error creating order:', error);
        isSubmitting.value = false;
    }
};
</script>

<template>
    <!-- Custom Modal Implementation -->
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50" @click="emit('update:open', false)"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-2xl max-h-[90vh] w-full mx-4 overflow-y-auto">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b">
                <div>
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <Server class="h-5 w-5 text-blue-600" />
                        Order Hosting Plan
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Pilih hosting dan domain untuk memulai website Anda
                    </p>
                </div>
                <button @click="emit('update:open', false)" class="text-gray-500 hover:text-gray-700">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <div class="space-y-6">
                <!-- Selected Hosting Plan -->
                <Card class="border-blue-200 bg-blue-50/50">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-lg flex items-center justify-between">
                            {{ hostingPlan.plan_name }}
                            <div class="flex items-center gap-2">
                                <Badge v-if="hostingPlan.discount_percent > 0" class="bg-red-500 text-white">
                                    {{ hostingPlan.discount_percent }}% OFF
                                </Badge>
                                <Badge v-if="qualifiesForDiscount && domainOption === 'new'" class="bg-green-500 text-white">
                                    Bundle Discount 10%
                                </Badge>
                            </div>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="flex items-center gap-2">
                                <HardDrive class="h-4 w-4 text-blue-500" />
                                <span>Storage: {{ hostingPlan.storage_gb }}GB</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Server class="h-4 w-4 text-green-500" />
                                <span>CPU: {{ hostingPlan.cpu_cores }} Cores</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div v-if="bundleSavings > 0" class="text-sm">
                                <span class="line-through text-muted-foreground">{{ formatPrice(getDiscountedPrice(hostingPlan.selling_price, hostingPlan.discount_percent)) }}</span>
                                <span class="ml-2 text-green-600 font-semibold">Hemat {{ formatPrice(bundleSavings) }}</span>
                            </div>
                            <div class="text-lg font-bold text-blue-600">{{ formatPrice(hostingPrice) }}</div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Domain Selection -->
                <div class="space-y-4">
                    <Label class="text-base font-semibold">Pilih Domain <span class="text-red-500">*</span></Label>
                    
                    <div class="space-y-4">
                        <div class="flex items-center space-x-2">
                            <input type="radio" id="new" value="new" v-model="domainOption" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" />
                            <Label for="new" class="font-medium">Order Domain Baru</Label>
                        </div>
                        
                        <div v-if="existingDomains.length > 0" class="flex items-center space-x-2">
                            <input type="radio" id="existing" value="existing" v-model="domainOption" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" />
                            <Label for="existing" class="font-medium">Gunakan Domain yang Sudah Ada</Label>
                        </div>
                    </div>

                    <!-- New Domain Form -->
                    <div v-if="domainOption === 'new'" class="space-y-4 p-4 border rounded-lg bg-gray-50">
                        <div class="space-y-2">
                            <Label>Nama Domain</Label>
                            <div class="flex gap-2">
                                <Input 
                                    v-model="newDomainName" 
                                    placeholder="namawebsite" 
                                    class="flex-1"
                                />
                                <select v-model="selectedExtension" class="w-32 h-10 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                    <option value="">Ekstensi</option>
                                    <option 
                                        v-for="domain in domainPrices.slice(0, 10)" 
                                        :key="domain.id"
                                        :value="domain.extension"
                                    >
                                        {{ domain.extension }} - {{ formatPrice(domain.selling_price) }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div v-if="newDomainName && selectedExtension" class="p-3 bg-blue-50 border border-blue-200 rounded">
                            <div class="font-medium text-blue-900">
                                {{ newDomainName }}{{ selectedExtension }}
                            </div>
                            <div class="text-sm text-blue-700">
                                Harga Domain: {{ formatPrice(domainPrice) }}
                            </div>
                        </div>
                    </div>

                    <!-- Existing Domain Selection -->
                    <div v-if="domainOption === 'existing' && existingDomains.length > 0" class="space-y-2 p-4 border rounded-lg bg-gray-50">
                        <Label>Pilih Domain</Label>
                        <select v-model="selectedExistingDomain" class="w-full h-10 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            <option value="">Pilih domain yang sudah ada</option>
                            <option 
                                v-for="domain in existingDomains" 
                                :key="domain.id"
                                :value="domain.domain_name"
                            >
                                {{ domain.domain_name }} ({{ domain.status }})
                            </option>
                        </select>
                    </div>

                    <div v-if="domainOption === 'existing' && existingDomains.length === 0" class="p-4 border rounded-lg bg-yellow-50 border-yellow-200">
                        <div class="text-yellow-800 text-sm">
                            Anda belum memiliki domain. Silakan pilih "Order Domain Baru" untuk melanjutkan.
                        </div>
                    </div>
                </div>

                <!-- Bundle Discount Info -->
                <div v-if="qualifiesForDiscount && domainOption === 'new'" class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center gap-2 text-green-800 font-medium mb-1">
                        <Check class="h-4 w-4" />
                        Bundle Discount Berlaku!
                    </div>
                    <div class="text-sm text-green-700">
                        Hemat 10% untuk hosting karena minimal 2GB storage dan order domain baru
                    </div>
                </div>

                <!-- Order Summary -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Ringkasan Order</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span>{{ hostingPlan.plan_name }}</span>
                            <span>{{ formatPrice(hostingPrice) }}</span>
                        </div>
                        
                        <div v-if="domainOption === 'new' && domainPrice > 0" class="flex justify-between">
                            <span>Domain {{ newDomainName }}{{ selectedExtension }}</span>
                            <span>{{ formatPrice(domainPrice) }}</span>
                        </div>

                        <div v-if="bundleSavings > 0" class="flex justify-between text-green-600">
                            <span>Bundle Discount (10%)</span>
                            <span>-{{ formatPrice(bundleSavings) }}</span>
                        </div>

                        <Separator />
                        
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total</span>
                            <span>{{ formatPrice(totalPrice) }}</span>
                        </div>
                        
                        <div class="text-sm text-muted-foreground">
                            Billing cycle: Annual (12 bulan)
                        </div>
                    </CardContent>
                </Card>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 pt-4">
                    <Button variant="outline" @click="emit('update:open', false)">
                        Batal
                    </Button>
                    <Button 
                        @click="handleSubmit" 
                        :disabled="!isFormValid || isSubmitting"
                        class="min-w-[120px]"
                    >
                        {{ isSubmitting ? 'Memproses...' : 'Order Sekarang' }}
                    </Button>
                </div>
                </div>
            </div>
        </div>
    </div>
</template>