<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { DatePicker } from '@/components/ui/date-picker';
import { Plus, Trash2, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

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
    id?: number;
    item_type: 'hosting' | 'domain' | 'service' | 'app' | 'web' | 'maintenance';
    item_id: string;
    price?: string;
}

interface Order {
    id: number;
    customer: Customer;
    domain_name?: string;
    billing_cycle: string;
    status: string;
    expires_at?: string;
    auto_renew?: boolean;
    discount_amount?: number;
    order_items: any[];
}

interface Props {
    show: boolean;
    mode: 'create' | 'edit';
    order?: Order | null;
    customers: Customer[];
    hostingPlans: HostingPlan[];
    domainPrices: DomainPrice[];
    servicePlans: ServicePlan[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
    close: [];
    submit: [data: any];
}>();

// Create form without automatic submission
const formData = ref({
    customer_id: '',
    domain_name: '',
    billing_cycle: 'annual' as 'onetime' | 'monthly' | 'quarterly' | 'semi_annually' | 'annually',
    status: 'pending' as 'pending' | 'processing' | 'active' | 'suspended' | 'expired' | 'terminated' | 'cancelled',
    expires_at: '',
    auto_renew: false,
    discount_amount: '',
    items: [
        {
            item_type: 'hosting' as 'hosting' | 'domain' | 'service' | 'app' | 'web' | 'maintenance',
            item_id: '',
        },
    ] as OrderItem[],
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const modalTitle = computed(() => {
    return props.mode === 'create' 
        ? 'Buat Pesanan Baru' 
        : `Edit Pesanan #${props.order?.id}`;
});

const modalDescription = computed(() => {
    return props.mode === 'create' 
        ? 'Buat pesanan baru untuk pelanggan dengan berbagai item dan layanan.' 
        : 'Perbarui informasi pesanan ini';
});

const isEditMode = computed(() => props.mode === 'edit');

// Price calculation functions
const getItemPrice = (item: OrderItem): number => {
    if (!item.item_id || !item.item_type) return 0;
    
    const plans = getPlansForType(item.item_type);
    const selectedPlan = plans.find(plan => plan.id.toString() === item.item_id.toString());
    
    return selectedPlan?.price || 0;
};

const getBillingCycleMultiplier = (): number => {
    switch (formData.value.billing_cycle) {
        case 'monthly': return 1;
        case 'quarterly': return 3;
        case 'semi_annual': return 6;
        case 'annual': return 12;
        case 'onetime':
        default:
            return 1;
    }
};

// Computed properties for price calculation
const itemsSubtotal = computed((): number => {
    return formData.value.items.reduce((total, item) => {
        const itemPrice = getItemPrice(item);
        return total + (itemPrice * getBillingCycleMultiplier());
    }, 0);
});

const discountAmount = computed((): number => {
    const discount = parseFloat(formData.value.discount_amount) || 0;
    return Math.max(0, discount);
});

const totalAmount = computed((): number => {
    return Math.max(0, itemsSubtotal.value - discountAmount.value);
});

const formatPrice = (amount: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

// Function to format date for input[type="date"]
const formatDateForInput = (dateString: string | null): string => {
    if (!dateString) return '';
    
    try {
        // Handle various date formats and convert to YYYY-MM-DD
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '';
        
        return date.toISOString().split('T')[0]; // Format: YYYY-MM-DD
    } catch (error) {
        console.warn('Invalid date format:', dateString);
        return '';
    }
};

// Watch for order changes to populate form
watch(() => props.order, (order) => {
    if (order && props.mode === 'edit') {
        // Reset form data
        formData.value = {
            customer_id: order.customer.id.toString(),
            domain_name: order.domain_name || '',
            billing_cycle: order.billing_cycle as typeof formData.value.billing_cycle,
            status: order.status as typeof formData.value.status,
            expires_at: formatDateForInput(order.expires_at || ''),
            auto_renew: order.auto_renew || false,
            discount_amount: order.discount_amount?.toString() || '',
            items: order.order_items.map(item => ({
                id: item.id,
                item_type: item.item_type,
                item_id: item.item_id?.toString() || '1',
                price: item.price?.toString() || '',
            })),
        };
        errors.value = {};
    }
}, { immediate: true });

// Reset form when switching to create mode
watch(() => props.mode, (mode) => {
    if (mode === 'create') {
        formData.value = {
            customer_id: '',
            domain_name: '',
            billing_cycle: 'annual',
            status: 'pending',
            expires_at: '',
            auto_renew: false,
            discount_amount: '',
            items: [{
                item_type: 'hosting',
                item_id: '',
            }],
        };
        errors.value = {};
    }
});

const addItem = () => {
    formData.value.items.push({
        item_type: 'hosting',
        item_id: '',
    });
};

const removeItem = (index: number) => {
    if (formData.value.items.length > 1) {
        formData.value.items.splice(index, 1);
    }
};

const getItemTypeText = (type: string) => {
    switch (type) {
        case 'hosting': return 'Hosting';
        case 'domain': return 'Domain';
        case 'service': return 'Layanan';
        case 'app': return 'Aplikasi';
        case 'web': return 'Website';
        case 'maintenance': return 'Pemeliharaan';
        default: return type;
    }
};

const getPlansForType = (type: string) => {
    switch (type) {
        case 'hosting':
            return props.hostingPlans.map(plan => ({
                id: plan.id,
                name: `${plan.plan_name} (${plan.storage_gb}GB, ${plan.cpu_cores} CPU, ${plan.ram_gb}GB RAM) - ${formatPrice(plan.selling_price)}`,
                price: plan.selling_price,
            }));
        case 'domain':
            return props.domainPrices.map(domain => ({
                id: domain.id,
                name: `${domain.extension} - ${formatPrice(domain.selling_price)}`,
                price: domain.selling_price,
            }));
        case 'service':
            return props.servicePlans.map(service => ({
                id: service.id,
                name: `${service.name} (${service.category}) - ${formatPrice(service.price)}`,
                price: service.price,
            }));
        default:
            return [];
    }
};

const submit = () => {
    // Simply emit the form data, let parent handle submission
    emit('submit', formData.value);
};

const close = () => {
    emit('close');
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/50" @click="close"></div>
        
        <!-- Modal Content -->
        <div class="relative mx-4 w-full max-w-4xl rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900 max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold">{{ modalTitle }}</h2>
                    <p class="text-sm text-muted-foreground">{{ modalDescription }}</p>
                </div>
                <button @click="close" class="cursor-pointer text-gray-500 hover:text-gray-700">
                    <X class="h-4 w-4" />
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Customer dan Domain -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label :for="`${mode}-customer`">Pelanggan *</Label>
                        <select
                            :id="`${mode}-customer`"
                            v-model="formData.customer_id"
                            class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                            required
                        >
                            <option value="">Pilih Pelanggan</option>
                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                {{ customer.name }} ({{ customer.email }})
                            </option>
                        </select>
                        <p v-if="errors.customer_id" class="mt-1 text-xs text-red-500">{{ errors.customer_id }}</p>
                    </div>
                    
                    <div>
                        <Label :for="`${mode}-domain`">Nama Domain</Label>
                        <Input 
                            :id="`${mode}-domain`"
                            v-model="formData.domain_name" 
                            placeholder="Masukkan nama domain (contoh: example.com)" 
                        />
                        <p v-if="errors.domain_name" class="mt-1 text-xs text-red-500">{{ errors.domain_name }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">Field ini opsional - kosongkan jika tidak ada domain</p>
                    </div>
                </div>

                <!-- Billing Cycle and Status for Edit Mode -->
                <div class="grid" :class="isEditMode ? 'grid-cols-2 gap-4' : 'grid-cols-1'">
                    <div>
                        <Label :for="`${mode}-billing-cycle`">Siklus Pembayaran *</Label>
                        <select
                            :id="`${mode}-billing-cycle`"
                            v-model="formData.billing_cycle"
                            class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                            required
                        >
                            <option value="onetime">Sekali Bayar</option>
                            <option value="monthly">Bulanan</option>
                            <option value="quarterly">Triwulan</option>
                            <option value="semi_annually">Semi Annual</option>
                            <option value="annually">Tahunan</option>
                        </select>
                        <p v-if="errors.billing_cycle" class="mt-1 text-xs text-red-500">{{ errors.billing_cycle }}</p>
                    </div>

                    <!-- Status field only for edit mode -->
                    <div v-if="isEditMode">
                        <Label :for="`${mode}-status`">Status *</Label>
                        <select
                            :id="`${mode}-status`"
                            v-model="formData.status"
                            class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                            required
                        >
                            <option value="pending">Menunggu</option>
                            <option value="processing">Diproses</option>
                            <option value="active">Aktif</option>
                            <option value="suspended">Ditangguhkan</option>
                            <option value="expired">Kedaluwarsa</option>
                            <option value="cancelled">Dibatalkan</option>
                            <option value="terminated">Dihentikan</option>
                        </select>
                        <p v-if="errors.status" class="mt-1 text-xs text-red-500">{{ errors.status }}</p>
                    </div>
                </div>

                <!-- Discount Amount -->
                <div>
                    <Label :for="`${mode}-discount-amount`">Potongan Harga (Rp)</Label>
                    <Input
                        :id="`${mode}-discount-amount`"
                        v-model="formData.discount_amount"
                        type="number"
                        min="0"
                        step="1"
                        placeholder="Masukkan nominal potongan harga"
                    />
                    <p v-if="errors.discount_amount" class="mt-1 text-xs text-red-500">{{ errors.discount_amount }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">Masukkan nominal dalam Rupiah (contoh: 50000 untuk Rp 50.000)</p>
                </div>

                <!-- Additional fields for edit mode -->
                <div v-if="isEditMode" class="grid grid-cols-2 gap-4">
                    <div>
                        <Label :for="`${mode}-expires-at`">Tanggal Kedaluwarsa</Label>
                        <DatePicker
                            v-model="formData.expires_at"
                            placeholder="Pilih tanggal kedaluwarsa"
                            :min-date="new Date().toISOString().split('T')[0]"
                        />
                        <p v-if="errors.expires_at" class="mt-1 text-xs text-red-500">{{ errors.expires_at }}</p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <input
                            :id="`${mode}-auto-renew`"
                            v-model="formData.auto_renew"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <Label :for="`${mode}-auto-renew`">Perpanjang Otomatis</Label>
                    </div>
                </div>

                <!-- Items Section -->
                <div>
                    <div class="mb-4 flex items-center justify-between">
                        <Label class="text-base font-medium">Item Pesanan *</Label>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="addItem"
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            Tambah Item
                        </Button>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(item, index) in formData.items"
                            :key="index"
                            class="grid grid-cols-12 gap-4 items-end rounded-lg border p-4"
                        >
                            <!-- Item Type -->
                            <div class="col-span-3">
                                <Label :for="`item-type-${index}`">Tipe Item</Label>
                                <select
                                    :id="`item-type-${index}`"
                                    v-model="item.item_type"
                                    class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                                >
                                    <option value="hosting">Hosting</option>
                                    <option value="domain">Domain</option>
                                    <option value="service">Layanan</option>
                                    <option value="app">Aplikasi</option>
                                    <option value="web">Website</option>
                                    <option value="maintenance">Pemeliharaan</option>
                                </select>
                                <p v-if="errors[`items.${index}.item_type`]" class="mt-1 text-xs text-red-500">
                                    {{ errors[`items.${index}.item_type`] }}
                                </p>
                            </div>

                            <!-- Item Selection -->
                            <div class="col-span-4">
                                <Label :for="`item-id-${index}`">{{ getItemTypeText(item.item_type) }}</Label>
                                <select
                                    v-if="['hosting', 'domain', 'service'].includes(item.item_type)"
                                    :id="`item-id-${index}`"
                                    v-model="item.item_id"
                                    class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                                    required
                                >
                                    <option value="">Pilih {{ getItemTypeText(item.item_type) }}</option>
                                    <option
                                        v-for="plan in getPlansForType(item.item_type)"
                                        :key="plan.id"
                                        :value="plan.id"
                                    >
                                        {{ plan.name }}
                                    </option>
                                </select>
                                <select
                                    v-else
                                    :id="`item-id-${index}`"
                                    v-model="item.item_id"
                                    class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                                    required
                                >
                                    <option value="1">Standard</option>
                                    <option value="2">Premium</option>
                                    <option value="3">Enterprise</option>
                                </select>
                                <p v-if="errors[`items.${index}.item_id`]" class="mt-1 text-xs text-red-500">
                                    {{ errors[`items.${index}.item_id`] }}
                                </p>
                            </div>

                            <!-- Price Display (Create Mode) / Custom Price (Edit Mode) -->
                            <div v-if="!isEditMode" class="col-span-4">
                                <Label>Harga</Label>
                                <div class="flex h-9 items-center rounded-md border border-input bg-muted px-3 py-1 text-sm">
                                    <span v-if="item.item_id && item.item_type" class="font-medium text-primary">
                                        {{ formatPrice(getItemPrice(item)) }}
                                        <span v-if="formData.billing_cycle !== 'onetime'" class="text-xs text-muted-foreground ml-1">
                                            /{{ formData.billing_cycle === 'monthly' ? 'bulan' : formData.billing_cycle === 'quarterly' ? '3 bulan' : formData.billing_cycle === 'semi_annual' ? '6 bulan' : 'tahun' }}
                                        </span>
                                    </span>
                                    <span v-else class="text-muted-foreground">
                                        Pilih item untuk melihat harga
                                    </span>
                                </div>
                            </div>
                            <div v-else class="col-span-4">
                                <Label :for="`item-price-${index}`">Harga Custom (Opsional)</Label>
                                <Input
                                    :id="`item-price-${index}`"
                                    v-model="item.price"
                                    type="number"
                                    placeholder="Kosongkan untuk harga default"
                                />
                                <p v-if="errors[`items.${index}.price`]" class="mt-1 text-xs text-red-500">
                                    {{ errors[`items.${index}.price`] }}
                                </p>
                            </div>

                            <!-- Remove Button -->
                            <div class="col-span-1">
                                <Button
                                    v-if="formData.items.length > 1"
                                    type="button"
                                    variant="destructive"
                                    size="sm"
                                    @click="removeItem(index)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                    <p v-if="errors.items" class="mt-2 text-xs text-red-500">{{ errors.items }}</p>
                </div>

                <!-- Price Summary -->
                <div v-if="!isEditMode" class="rounded-lg border bg-muted/30 p-4">
                    <h3 class="text-lg font-medium mb-4">Ringkasan Harga</h3>
                    
                    <!-- Individual Item Prices -->
                    <div class="space-y-2 mb-4">
                        <div 
                            v-for="(item, index) in formData.items" 
                            :key="index"
                            class="flex justify-between text-sm"
                        >
                            <span v-if="item.item_id && item.item_type">
                                {{ getItemTypeText(item.item_type) }} - 
                                {{ getPlansForType(item.item_type).find(p => p.id.toString() === item.item_id.toString())?.name?.split(' - ')[0] || 'Item' }}
                                <span v-if="formData.billing_cycle !== 'onetime'" class="text-muted-foreground">
                                    ({{ getBillingCycleMultiplier() }} {{ formData.billing_cycle === 'monthly' ? 'bulan' : formData.billing_cycle === 'quarterly' ? 'kuartal' : formData.billing_cycle === 'semi_annual' ? 'semester' : 'tahun' }})
                                </span>
                            </span>
                            <span v-if="item.item_id && item.item_type" class="font-medium">
                                {{ formatPrice(getItemPrice(item) * getBillingCycleMultiplier()) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="border-t pt-4">
                        <!-- Subtotal -->
                        <div class="flex justify-between text-sm mb-2">
                            <span>Subtotal:</span>
                            <span class="font-medium">{{ formatPrice(itemsSubtotal) }}</span>
                        </div>
                        
                        <!-- Discount -->
                        <div v-if="discountAmount > 0" class="flex justify-between text-sm text-green-600 mb-2">
                            <span>Potongan Harga:</span>
                            <span class="font-medium">-{{ formatPrice(discountAmount) }}</span>
                        </div>
                        
                        <!-- Total -->
                        <div class="flex justify-between text-lg font-bold pt-2 border-t">
                            <span>Total:</span>
                            <span class="text-primary">{{ formatPrice(totalAmount) }}</span>
                        </div>
                        
                        <!-- Billing Cycle Info -->
                        <div v-if="formData.billing_cycle !== 'onetime'" class="text-xs text-muted-foreground mt-2">
                            Total untuk {{ formData.billing_cycle === 'monthly' ? '1 bulan' : formData.billing_cycle === 'quarterly' ? '3 bulan' : formData.billing_cycle === 'semi_annual' ? '6 bulan' : '12 bulan' }} periode
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-6">
                    <Button type="button" variant="outline" @click="close">
                        Batal
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Menyimpan...' : (isEditMode ? 'Perbarui Pesanan' : 'Buat Pesanan') }}
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>