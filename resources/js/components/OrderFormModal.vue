<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Check, ChevronsUpDown, Plus, Trash2, X } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

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
    discount_percent: number;
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
    billing_cycle?: 'onetime' | 'monthly' | 'quarterly' | 'semi_annually' | 'annually';
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
    billing_cycle: 'annually' as 'onetime' | 'monthly' | 'quarterly' | 'semi_annually' | 'annually',
    status: 'pending' as 'pending' | 'processing' | 'active' | 'suspended' | 'expired' | 'terminated' | 'cancelled',
    expires_at: '',
    auto_renew: false,
    discount_amount: '',
    items: [
        {
            item_type: 'hosting' as 'hosting' | 'domain' | 'service' | 'app' | 'web' | 'maintenance',
            item_id: '',
            billing_cycle: 'annually' as 'onetime' | 'monthly' | 'quarterly' | 'semi_annually' | 'annually',
        },
    ] as OrderItem[],
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);
const customerPickerOpen = ref(false);
const customerQuery = ref('');
const customerPickerRoot = ref<HTMLElement | null>(null);
const customerSearchInput = ref<HTMLInputElement | null>(null);

const filteredCustomers = computed(() => {
    const query = customerQuery.value.toLowerCase().trim();
    if (! query) return props.customers;
    return props.customers.filter((customer) => {
        return (
            customer.name.toLowerCase().includes(query) ||
            customer.email.toLowerCase().includes(query)
        );
    });
});

const selectedCustomer = computed(() => {
    const id = Number(formData.value.customer_id || 0);
    if (! id) return null;
    return props.customers.find((c) => c.id === id) || null;
});

const toggleCustomerPicker = async () => {
    customerPickerOpen.value = ! customerPickerOpen.value;
    if (customerPickerOpen.value) {
        await nextTick();
        customerSearchInput.value?.focus();
    }
};

const selectCustomer = (customerId: number) => {
    formData.value.customer_id = String(customerId);
    customerPickerOpen.value = false;
};

const clearCustomer = () => {
    formData.value.customer_id = '';
    customerQuery.value = '';
};

const onDocumentPointerDown = (event: MouseEvent | PointerEvent) => {
    if (! customerPickerOpen.value) return;
    const root = customerPickerRoot.value;
    if (! root) return;
    const target = event.target as Node | null;
    if (target && root.contains(target)) return;
    customerPickerOpen.value = false;
};

onMounted(() => {
    document.addEventListener('pointerdown', onDocumentPointerDown, { capture: true });
});

onBeforeUnmount(() => {
    document.removeEventListener('pointerdown', onDocumentPointerDown, { capture: true } as any);
});

const modalTitle = computed(() => {
    return props.mode === 'create' ? 'Buat Pesanan Baru' : `Edit Pesanan #${props.order?.id}`;
});

const modalDescription = computed(() => {
    return props.mode === 'create' ? 'Buat pesanan baru untuk pelanggan dengan berbagai item dan layanan.' : 'Perbarui informasi pesanan ini';
});

const isEditMode = computed(() => props.mode === 'edit');

// Price calculation functions
const getItemPrice = (item: OrderItem): number => {
    if (!item.item_id || !item.item_type) return 0;

    const plans = getPlansForType(item.item_type);
    const selectedPlan = plans.find((plan) => plan.id.toString() === item.item_id.toString());

    // Ensure price is a number
    const price = selectedPlan?.price;
    return price !== undefined && price !== null ? Number(price) : 0;
};

// Calculate total price for an item based on its billing cycle
const calculateItemTotal = (item: OrderItem): number => {
    const basePrice = getItemPrice(item);
    const cycle = item.billing_cycle || formData.value.billing_cycle;
    
    if (isNaN(basePrice)) return 0;
    
    if (item.item_type === 'hosting') {
        // Hosting price is Annual (per year)
        switch (cycle) {
            case 'monthly':
                return basePrice / 12;
            case 'quarterly':
                return basePrice / 4;
            case 'semi_annually':
            case 'semi_annual':
                return basePrice / 2;
            case 'annually':
            case 'annual':
                return basePrice;
            default:
                return basePrice;
        }
    } else if (item.item_type === 'domain') {
        // Domain price is Annual
        return basePrice;
    } else if (['service', 'app', 'web'].includes(item.item_type)) {
        // Services are One-time
        return basePrice;
    } else if (item.item_type === 'maintenance') {
        // Maintenance is usually Monthly
        // If base price is monthly:
        switch (cycle) {
            case 'monthly': return basePrice;
            case 'quarterly': return basePrice * 3;
            case 'semi_annually':
            case 'semi_annual': return basePrice * 6;
            case 'annually':
            case 'annual': return basePrice * 12;
            default: return basePrice;
        }
    }
    
    return basePrice;
};

const getBillingCycleMultiplier = (cycle?: string): number => {
    // This function is now mainly used for displaying duration (months)
    const targetCycle = cycle || formData.value.billing_cycle;

    switch (targetCycle) {
        case 'monthly':
            return 1;
        case 'quarterly':
            return 3;
        case 'semi_annual':
        case 'semi_annually':
            return 6;
        case 'annual':
        case 'annually':
            return 12;
        case 'onetime':
        default:
            return 1;
    }
};

// Computed properties for price calculation
const itemsSubtotal = computed((): number => {
    return formData.value.items.reduce((total, item) => {
        const itemTotal = calculateItemTotal(item);
        return total + (isNaN(itemTotal) ? 0 : itemTotal);
    }, 0);
});

const discountAmount = computed((): number => {
    const discount = parseFloat(formData.value.discount_amount as any) || 0;
    return Math.max(0, discount);
});

const totalAmount = computed((): number => {
    const subtotal = isNaN(itemsSubtotal.value) ? 0 : itemsSubtotal.value;
    const discount = isNaN(discountAmount.value) ? 0 : discountAmount.value;
    return Math.max(0, subtotal - discount);
});

const formatPrice = (amount: number): string => {
    const num = Number(amount);
    if (isNaN(num)) return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(0);
    
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(num);
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
watch(
    () => props.order,
    (order) => {
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
                items: order.order_items.map((item) => ({
                    id: item.id,
                    item_type: item.item_type,
                    item_id: item.item_id?.toString() || '1',
                    price: item.price?.toString() || '',
                    billing_cycle: order.billing_cycle, // Default to order cycle if not stored in item
                })),
            };
            errors.value = {};
        }
    },
    { immediate: true },
);

// Reset form when switching to create mode
watch(
    () => props.mode,
    (mode) => {
        if (mode === 'create') {
            formData.value = {
                customer_id: '',
                domain_name: '',
                billing_cycle: 'annually',
                status: 'pending',
                expires_at: '',
                auto_renew: false,
                discount_amount: '',
                items: [
                    {
                        item_type: 'hosting',
                        item_id: '',
                        billing_cycle: 'annually',
                    },
                ],
            };
            errors.value = {};
        }
    },
);

const addItem = () => {
    formData.value.items.push({
        item_type: 'hosting',
        item_id: '',
        billing_cycle: 'annually',
    });
};

const removeItem = (index: number) => {
    if (formData.value.items.length > 1) {
        formData.value.items.splice(index, 1);
    }
};

const getItemTypeText = (type: string) => {
    switch (type) {
        case 'hosting':
            return 'Hosting';
        case 'domain':
            return 'Domain';
        case 'service':
            return 'Layanan';
        case 'app':
            return 'Aplikasi';
        case 'web':
            return 'Website';
        case 'maintenance':
            return 'Pemeliharaan';
        default:
            return type;
    }
};

const getPlansForType = (type: string) => {
    switch (type) {
        case 'hosting':
            return props.hostingPlans.map((plan) => {
                const discountedPrice = plan.selling_price * (1 - plan.discount_percent / 100);
                return {
                    id: plan.id,
                    name: `${plan.plan_name} (${plan.storage_gb}GB, ${plan.cpu_cores} CPU, ${plan.ram_gb}GB RAM) - ${formatPrice(discountedPrice)}`,
                    price: discountedPrice,
                };
            });
        case 'domain':
            return props.domainPrices.map((domain) => ({
                id: domain.id,
                name: `${domain.extension} - ${formatPrice(domain.selling_price)}`,
                price: domain.selling_price,
            }));
        case 'service':
            return props.servicePlans.map((service) => ({
                id: service.id,
                name: `${service.name} (${service.category}) - ${formatPrice(service.price)}`,
                price: service.price,
            }));
        default:
            return [];
    }
};

const getBillingCyclesForType = (type: string) => {
    const allCycles = [
        { value: 'onetime', label: 'Sekali' },
        { value: 'monthly', label: 'Bulanan' },
        { value: 'quarterly', label: '3 Bln' },
        { value: 'semi_annually', label: '6 Bln' },
        { value: 'annually', label: 'Tahunan' },
    ];

    if (['domain'].includes(type)) {
        return allCycles.filter((c) => ['annually'].includes(c.value));
    }
    if (['service', 'app', 'web'].includes(type)) {
        return allCycles.filter((c) => ['onetime'].includes(c.value));
    }
    // Hosting, Maintenance
    return allCycles.filter((c) => ['monthly', 'quarterly', 'semi_annually', 'annually'].includes(c.value));
};

// Watch for item type changes to ensure valid billing cycle
watch(
    () => formData.value.items,
    (items) => {
        items.forEach((item) => {
            const validCycles = getBillingCyclesForType(item.item_type).map((c) => c.value);
            // If current cycle is not valid for this type (or if it's undefined), set to default (usually the first one)
            // Ideally we want 'annually' as preference if valid, otherwise first valid.
            if (!validCycles.includes(item.billing_cycle)) {
                if (validCycles.includes('annually')) {
                    item.billing_cycle = 'annually';
                } else {
                    item.billing_cycle = validCycles[0] as any;
                }
            }
        });

        // Sync global billing cycle with the first item's billing cycle
        // This ensures backend validation passes even though we hid the global field
        if (items.length > 0 && items[0].billing_cycle) {
            formData.value.billing_cycle = items[0].billing_cycle;
        }
    },
    { deep: true },
);

const submit = () => {
    // Simply emit the form data, let parent handle submission
    emit('submit', formData.value);
};

const close = () => {
    emit('close');
};

const parseDateInput = (value: string) => {
    const dateString = value.includes(' ') ? value.split(' ')[0] : value;
    const parts = dateString.split('-').map((v) => Number(v));
    if (parts.length !== 3 || parts.some((n) => Number.isNaN(n))) {
        return null;
    }
    const [year, month, day] = parts;
    return new Date(year, month - 1, day);
};

const formatDateInput = (date: Date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const extendExpiryOneYear = () => {
    const base = formData.value.expires_at ? parseDateInput(formData.value.expires_at) : null;
    const date = base || new Date();
    const next = new Date(date);
    next.setFullYear(next.getFullYear() + 1);
    formData.value.expires_at = formatDateInput(next);
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-[#141413]/25 backdrop-blur-sm" @click="close"></div>

        <!-- Modal Content -->
        <div class="relative mx-4 max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-2xl border border-border bg-card text-card-foreground shadow-[rgba(0,0,0,0.05)_0px_4px_24px]">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4 border-b border-border px-6 py-5">
                <div>
                    <h2 class="font-serif text-xl font-medium tracking-tight sm:text-2xl">{{ modalTitle }}</h2>
                    <p class="mt-1 text-sm leading-relaxed text-muted-foreground">{{ modalDescription }}</p>
                </div>
                <Button type="button" variant="ghost" size="icon" class="h-11 w-11" @click="close">
                    <X class="h-5 w-5" />
                </Button>
            </div>

            <form @submit.prevent="submit" class="space-y-6 px-6 py-6">
                <!-- Customer dan Domain -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <Label :for="`${mode}-customer`">Pelanggan *</Label>
                        <div ref="customerPickerRoot" class="relative">
                            <button
                                type="button"
                                :id="`${mode}-customer`"
                                @click="toggleCustomerPicker"
                                class="flex h-9 w-full items-center justify-between rounded-md border border-border bg-input px-3 py-1 text-left text-sm text-foreground shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                            >
                                <span class="min-w-0 truncate">
                                    <span v-if="selectedCustomer" class="font-medium">
                                        {{ selectedCustomer.name }}
                                        <span class="font-normal text-muted-foreground">({{ selectedCustomer.email }})</span>
                                    </span>
                                    <span v-else class="text-muted-foreground">Pilih pelanggan…</span>
                                </span>
                                <span class="flex items-center gap-2">
                                    <button
                                        v-if="formData.customer_id"
                                        type="button"
                                        class="rounded-sm px-1 text-muted-foreground hover:text-foreground"
                                        @click.stop="clearCustomer"
                                    >
                                        <X class="h-4 w-4" />
                                    </button>
                                    <ChevronsUpDown class="h-4 w-4 text-muted-foreground" />
                                </span>
                            </button>

                            <div
                                v-if="customerPickerOpen"
                                class="absolute z-50 mt-2 w-full overflow-hidden rounded-md border border-border bg-popover text-popover-foreground shadow-md"
                            >
                                <div class="border-b border-border p-2">
                                    <Input
                                        ref="customerSearchInput"
                                        v-model="customerQuery"
                                        type="text"
                                        placeholder="Cari nama atau email…"
                                        class="h-9"
                                    />
                                </div>
                                <div class="max-h-64 overflow-y-auto p-1">
                                    <button
                                        v-for="customer in filteredCustomers"
                                        :key="customer.id"
                                        type="button"
                                        class="flex w-full items-center justify-between gap-3 rounded-md px-2 py-2 text-left text-sm transition-colors hover:bg-muted/60"
                                        @click="selectCustomer(customer.id)"
                                    >
                                        <span class="min-w-0">
                                            <span class="block truncate font-medium">{{ customer.name }}</span>
                                            <span class="block truncate text-xs text-muted-foreground">{{ customer.email }}</span>
                                        </span>
                                        <Check v-if="String(customer.id) === String(formData.customer_id)" class="h-4 w-4 text-primary" />
                                    </button>

                                    <div v-if="filteredCustomers.length === 0" class="px-3 py-6 text-center text-sm text-muted-foreground">
                                        Pelanggan tidak ditemukan.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-if="errors.customer_id" class="mt-1 text-xs text-red-500">{{ errors.customer_id }}</p>
                    </div>

                    <div>
                        <Label :for="`${mode}-domain`">Nama Domain</Label>
                        <Input :id="`${mode}-domain`" v-model="formData.domain_name" placeholder="Masukkan nama domain (contoh: example.com)" />
                        <p v-if="errors.domain_name" class="mt-1 text-xs text-red-500">{{ errors.domain_name }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">Field ini opsional - kosongkan jika tidak ada domain</p>
                    </div>
                </div>

                <!-- Billing Cycle and Status -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Status field -->
                    <div class="md:col-span-2">
                        <Label :for="`${mode}-status`">Status *</Label>
                        <select
                            :id="`${mode}-status`"
                            v-model="formData.status"
                            class="flex h-9 w-full cursor-pointer rounded-md border border-border bg-input px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
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

                <!-- Additional fields -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <Label :for="`${mode}-expires-at`">Tanggal Kedaluwarsa</Label>
                        <DatePicker
                            v-model="formData.expires_at"
                            placeholder="Pilih tanggal kedaluwarsa"
                            :min-date="new Date().toISOString().split('T')[0]"
                        />
                        <p v-if="errors.expires_at" class="mt-1 text-xs text-red-500">{{ errors.expires_at }}</p>
                    </div>

                    <div v-if="isEditMode">
                        <Label>Perpanjang Sekarang</Label>
                        <Button type="button" variant="outline" class="w-full" @click="extendExpiryOneYear">Perpanjang +1 Tahun</Button>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Menambah 1 tahun dari tanggal kedaluwarsa. Jika kosong, akan dihitung 1 tahun dari hari ini.
                        </p>
                    </div>
                    <div v-else>
                        <Label :for="`${mode}-auto-renew`">Perpanjang Otomatis</Label>
                        <div class="flex items-center space-x-2">
                            <Switch :id="`${mode}-auto-renew`" v-model:checked="formData.auto_renew" />
                            <Label :for="`${mode}-auto-renew`" class="mb-0">Aktifkan perpanjangan otomatis</Label>
                        </div>
                    </div>
                </div>

                <!-- Items Section -->
                <div>
                    <div class="mb-4 flex items-center justify-between">
                        <Label class="text-base font-medium">Item Pesanan *</Label>
                        <Button type="button" variant="outline" size="sm" @click="addItem">
                            <Plus class="mr-2 h-4 w-4" />
                            Tambah Item
                        </Button>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(item, index) in formData.items"
                            :key="index"
                            class="grid grid-cols-12 items-end gap-2 rounded-lg border border-border bg-background/60 p-4"
                        >
                            <!-- Item Type -->
                            <div class="col-span-2">
                                <Label :for="`item-type-${index}`">Tipe</Label>
                                <select
                                    :id="`item-type-${index}`"
                                    v-model="item.item_type"
                                    class="flex h-9 w-full cursor-pointer rounded-md border border-border bg-input px-2 py-1 text-xs text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <option value="hosting">Hosting</option>
                                    <option value="domain">Domain</option>
                                    <option value="service">Layanan</option>
                                    <option value="app">Aplikasi</option>
                                    <option value="web">Website</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>

                            <!-- Item Selection -->
                            <div class="col-span-4">
                                <Label :for="`item-id-${index}`">{{ getItemTypeText(item.item_type) }}</Label>
                                <select
                                    v-if="['hosting', 'domain', 'service'].includes(item.item_type)"
                                    :id="`item-id-${index}`"
                                    v-model="item.item_id"
                                    class="flex h-9 w-full cursor-pointer rounded-md border border-border bg-input px-2 py-1 text-xs text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                    required
                                >
                                    <option value="">Pilih {{ getItemTypeText(item.item_type) }}</option>
                                    <option v-for="plan in getPlansForType(item.item_type)" :key="plan.id" :value="plan.id">
                                        {{ plan.name }}
                                    </option>
                                </select>
                                <select
                                    v-else
                                    :id="`item-id-${index}`"
                                    v-model="item.item_id"
                                    class="flex h-9 w-full cursor-pointer rounded-md border border-border bg-input px-2 py-1 text-xs text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                    required
                                >
                                    <option value="1">Standard</option>
                                    <option value="2">Premium</option>
                                    <option value="3">Enterprise</option>
                                </select>
                            </div>

                            <!-- Billing Cycle (Per Item) -->
                            <div class="col-span-2">
                                <Label :for="`item-cycle-${index}`">Siklus</Label>
                                <select
                                    :id="`item-cycle-${index}`"
                                    v-model="item.billing_cycle"
                                    class="flex h-9 w-full cursor-pointer rounded-md border border-border bg-input px-2 py-1 text-xs text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <option v-for="cycle in getBillingCyclesForType(item.item_type)" :key="cycle.value" :value="cycle.value">
                                        {{ cycle.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Price Display -->
                            <div v-if="!isEditMode" class="col-span-2">
                                <Label>Harga</Label>
                                <div class="flex h-9 items-center rounded-md border border-input bg-muted px-2 py-1 text-xs">
                                    <span v-if="item.item_id && item.item_type" class="font-medium text-primary truncate">
                                        {{ formatPrice(calculateItemTotal(item)) }}
                                    </span>
                                    <span v-else class="text-muted-foreground">-</span>
                                </div>
                            </div>
                            <div v-else class="col-span-2">
                                <Label :for="`item-price-${index}`">Harga Custom</Label>
                                <Input :id="`item-price-${index}`" v-model="item.price" type="number" placeholder="Kosongkan untuk harga default" />
                                <p v-if="errors[`items.${index}.price`]" class="mt-1 text-xs text-red-500">
                                    {{ errors[`items.${index}.price`] }}
                                </p>
                            </div>

                            <!-- Remove Button -->
                            <div class="col-span-1">
                                <Button v-if="formData.items.length > 1" type="button" variant="destructive" size="sm" @click="removeItem(index)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                    <p v-if="errors.items" class="mt-2 text-xs text-red-500">{{ errors.items }}</p>
                </div>

                <!-- Price Summary -->
                <div v-if="!isEditMode" class="rounded-lg border border-border bg-secondary/40 p-4">
                    <h3 class="mb-4 font-serif text-lg font-medium tracking-tight">Ringkasan Harga</h3>

                    <!-- Individual Item Prices -->
                    <div class="mb-4 space-y-2">
                        <div v-for="(item, index) in formData.items" :key="index" class="flex justify-between text-sm">
                            <span v-if="item.item_id && item.item_type">
                                {{ getItemTypeText(item.item_type) }} -
                                {{
                                    getPlansForType(item.item_type)
                                        .find((p) => p.id.toString() === item.item_id.toString())
                                        ?.name?.split(' - ')[0] || 'Item'
                                }}
                                <span v-if="item.billing_cycle && item.billing_cycle !== 'onetime'" class="text-muted-foreground">
                                    ({{
                                        item.billing_cycle === 'monthly'
                                            ? '1 bulan'
                                            : item.billing_cycle === 'quarterly'
                                              ? '3 bulan'
                                              : item.billing_cycle === 'semi_annually' || item.billing_cycle === 'semi_annual'
                                                ? '6 bulan'
                                                : '1 tahun'
                                    }})
                                </span>
                            </span>
                            <span v-if="item.item_id && item.item_type" class="font-medium">
                                {{ formatPrice(calculateItemTotal(item)) }}
                            </span>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <!-- Subtotal -->
                        <div class="mb-2 flex justify-between text-sm">
                            <span>Subtotal:</span>
                            <span class="font-medium">{{ formatPrice(itemsSubtotal) }}</span>
                        </div>

                        <!-- Discount -->
                        <div v-if="discountAmount > 0" class="mb-2 flex justify-between text-sm text-green-600">
                            <span>Potongan Harga:</span>
                            <span class="font-medium">-{{ formatPrice(discountAmount) }}</span>
                        </div>

                        <!-- Total -->
                        <div class="flex justify-between border-t pt-2 text-lg font-bold">
                            <span>Total:</span>
                            <span class="text-primary">{{ formatPrice(totalAmount) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col-reverse gap-2 pt-6 sm:flex-row sm:justify-end sm:gap-3">
                    <Button type="button" variant="outline" class="w-full sm:w-auto" @click="close"> Batal </Button>
                    <Button type="submit" class="w-full sm:w-auto" :disabled="processing">
                        {{ processing ? 'Menyimpan...' : isEditMode ? 'Perbarui Pesanan' : 'Buat Pesanan' }}
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
