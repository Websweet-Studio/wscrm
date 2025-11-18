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
import { Check, HardDrive, Server, X } from 'lucide-vue-next';
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

    const domainData = props.domainPrices.find((d) => d.extension === selectedExtension.value);
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
watch(
    () => props.open,
    (newValue) => {
        if (!newValue) {
            domainOption.value = 'new';
            selectedExistingDomain.value = '';
            newDomainName.value = '';
            selectedExtension.value = '';
        }
    },
);

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
                    domain_name:
                        domainOption.value === 'existing' ? selectedExistingDomain.value : `${newDomainName.value}${selectedExtension.value}`,
                },
            ],
        };

        // Add domain item if ordering new domain
        if (domainOption.value === 'new') {
            const domainData = props.domainPrices.find((d) => d.extension === selectedExtension.value);
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
        <div class="relative mx-4 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white shadow-xl">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b p-6">
                <div>
                    <h2 class="flex items-center gap-2 text-lg font-semibold">
                        <Server class="h-5 w-5 text-blue-600" />
                        Order Hosting Plan
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">Pilih hosting dan domain untuk memulai website Anda</p>
                </div>
                <button @click="emit('update:open', false)" class="cursor-pointer text-gray-500 hover:text-gray-700">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <div class="space-y-6">
                    <!-- Selected Hosting Plan -->
                    <div class="rounded-lg border border-blue-200 bg-blue-50/30 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <Server class="h-5 w-5 text-blue-600" />
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ hostingPlan.plan_name }}</h3>
                                    <div class="mt-1 flex items-center gap-3 text-sm text-gray-600">
                                        <span class="flex items-center gap-1">
                                            <HardDrive class="h-3 w-3" />
                                            {{ hostingPlan.storage_gb }}GB
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <Server class="h-3 w-3" />
                                            {{ hostingPlan.cpu_cores }} Core
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="text-right">
                                    <div v-if="bundleSavings > 0" class="text-xs text-gray-500">
                                        <span class="line-through">{{
                                            formatPrice(getDiscountedPrice(hostingPlan.selling_price, hostingPlan.discount_percent))
                                        }}</span>
                                    </div>
                                    <div class="text-lg font-bold text-blue-600">{{ formatPrice(hostingPrice) }}</div>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <Badge v-if="hostingPlan.discount_percent > 0" class="bg-red-500 px-2 py-0.5 text-xs text-white">
                                        {{ hostingPlan.discount_percent }}% OFF
                                    </Badge>
                                    <Badge v-if="qualifiesForDiscount && domainOption === 'new'" class="bg-green-500 px-2 py-0.5 text-xs text-white">
                                        Bundle 10%
                                    </Badge>
                                </div>
                            </div>
                        </div>
                        <div v-if="bundleSavings > 0" class="mt-2 rounded bg-green-50 px-2 py-1 text-center text-xs text-green-600">
                            Hemat {{ formatPrice(bundleSavings) }} dengan bundle discount
                        </div>
                    </div>

                    <!-- Domain Selection -->
                    <div class="space-y-4">
                        <Label class="text-base font-semibold">Pilih Domain <span class="text-red-500">*</span></Label>

                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <input
                                    type="radio"
                                    id="new"
                                    value="new"
                                    v-model="domainOption"
                                    class="h-4 w-4 cursor-pointer border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <Label for="new" class="cursor-pointer font-medium">Order Domain Baru</Label>
                            </div>

                            <div v-if="existingDomains.length > 0" class="flex items-center space-x-2">
                                <input
                                    type="radio"
                                    id="existing"
                                    value="existing"
                                    v-model="domainOption"
                                    class="h-4 w-4 cursor-pointer border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <Label for="existing" class="cursor-pointer font-medium">Gunakan Domain yang Sudah Ada</Label>
                            </div>
                        </div>

                        <!-- New Domain Form -->
                        <div v-if="domainOption === 'new'" class="space-y-4 rounded-lg border bg-gray-50 p-4">
                            <div class="space-y-2">
                                <Label>Nama Domain</Label>
                                <div class="flex gap-2">
                                    <Input v-model="newDomainName" placeholder="namawebsite" class="flex-1" />
                                    <select
                                        v-model="selectedExtension"
                                        class="h-9 w-32 cursor-pointer rounded-md border border-gray-300 px-3 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    >
                                        <option value="">Ekstensi</option>
                                        <option v-for="domain in domainPrices.slice(0, 10)" :key="domain.id" :value="domain.extension">
                                            {{ domain.extension }} - {{ formatPrice(domain.selling_price) }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div v-if="newDomainName && selectedExtension" class="rounded border border-blue-200 bg-blue-50 p-3">
                                <div class="font-medium text-blue-900">{{ newDomainName }}{{ selectedExtension }}</div>
                                <div class="text-sm text-blue-700">Harga Domain: {{ formatPrice(domainPrice) }}</div>
                            </div>
                        </div>

                        <!-- Existing Domain Selection -->
                        <div v-if="domainOption === 'existing' && existingDomains.length > 0" class="space-y-2 rounded-lg border bg-gray-50 p-4">
                            <Label>Pilih Domain</Label>
                            <select
                                v-model="selectedExistingDomain"
                                class="h-9 w-full cursor-pointer rounded-md border border-gray-300 px-3 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            >
                                <option value="">Pilih domain yang sudah ada</option>
                                <option v-for="domain in existingDomains" :key="domain.id" :value="domain.domain_name">
                                    {{ domain.domain_name }} ({{ domain.status }})
                                </option>
                            </select>
                        </div>

                        <div
                            v-if="domainOption === 'existing' && existingDomains.length === 0"
                            class="rounded-lg border border-yellow-200 bg-yellow-50 p-4"
                        >
                            <div class="text-sm text-yellow-800">
                                Anda belum memiliki domain. Silakan pilih "Order Domain Baru" untuk melanjutkan.
                            </div>
                        </div>
                    </div>

                    <!-- Bundle Discount Info -->
                    <div v-if="qualifiesForDiscount && domainOption === 'new'" class="rounded-lg border border-green-200 bg-green-50 p-4">
                        <div class="mb-1 flex items-center gap-2 font-medium text-green-800">
                            <Check class="h-4 w-4" />
                            Bundle Discount Berlaku!
                        </div>
                        <div class="text-sm text-green-700">Hemat 10% untuk hosting karena minimal 2GB storage dan order domain baru</div>
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

                            <div class="text-sm text-muted-foreground">Billing cycle: Annual (12 bulan)</div>
                        </CardContent>
                    </Card>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 pt-4">
                        <Button variant="outline" @click="emit('update:open', false)" class="cursor-pointer"> Batal </Button>
                        <Button @click="handleSubmit" :disabled="!isFormValid || isSubmitting" class="min-w-[120px] cursor-pointer">
                            {{ isSubmitting ? 'Memproses...' : 'Order Sekarang' }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
