<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatPrice } from '@/lib/utils';
import { Head, router } from '@inertiajs/vue3';
import { Calculator, ChevronDown, DollarSign, Download, Save, Settings, Trash2, TrendingUp, Upload, X } from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';

interface PricingTier {
    id: number;
    storage_gb: number;
    discount_percentage: number;
    sort_order: number;
    is_active: boolean;
}

interface HostingPlan {
    id: number;
    plan_name: string;
    storage_gb: number;
    selling_price: number;
    plan_multiplier?: number;
    use_bulk_pricing: boolean;
}

interface SavedConfig {
    id: number;
    name: string;
    description?: string;
    base_price_per_gb: number;
    cost_per_gb: number;
    plan_multipliers: Record<string, number>;
    tier_discounts: Array<{ storage_gb: number; discount_percentage: number }>;
    is_default: boolean;
}

interface SimulationData {
    plan_id: number;
    plan_name: string;
    storage_gb: number;
    cpu_cores: number;
    ram_gb: number;
    current_price: number;
    discount_percentage: number;
    price_per_gb: number;
    new_total_price: number;
    price_difference: number;
    total_cost: number;
    profit: number;
    profit_per_gb: number;
    profit_margin: number;
}

const props = defineProps<{
    pricingTiers: PricingTier[];
    hostingPlans: HostingPlan[];
    savedConfigs: SavedConfig[];
    defaultConfig: {
        base_price_per_gb: number;
        cost_per_gb: number;
        plan_multipliers: Record<string, number>;
        tier_discounts: Array<{ storage_gb: number; discount_percentage: number }>;
    };
    simulationResults?: {
        simulation: Record<string, SimulationData[]>;
    };
}>();

const form = reactive({
    base_price_per_gb: props.defaultConfig.base_price_per_gb,
    cost_per_gb: props.defaultConfig.cost_per_gb,
    plan_multipliers: { ...props.defaultConfig.plan_multipliers },
    tier_discounts: [...props.defaultConfig.tier_discounts],
});

const simulation = ref<Record<string, SimulationData[]>>(props.simulationResults?.simulation || {});
const isSimulating = ref(false);
const isApplying = ref(false);
const viewMode = ref<'basic' | 'lite'>('basic');
const showSaveModal = ref(false);
const enabledPlans = ref({
    basic: true,
    lite: false,
});

// Update plan_multipliers based on enabled plans
const updateActivePlans = () => {
    const newMultipliers: Record<string, number> = {};

    if (enabledPlans.value.basic) {
        newMultipliers.basic = form.plan_multipliers.basic || 1.0;
    }
    if (enabledPlans.value.lite) {
        newMultipliers.lite = form.plan_multipliers.lite || 0.77;
    }

    form.plan_multipliers = newMultipliers;

    // Run simulation with updated plans
    runSimulation();
};

// Filter simulation based on active plan multipliers
const filteredSimulation = computed(() => {
    const activePlans = Object.keys(form.plan_multipliers);
    const filtered: Record<string, SimulationData[]> = {};

    for (const planType of activePlans) {
        if (simulation.value[planType]) {
            filtered[planType] = simulation.value[planType];
        }
    }

    return filtered;
});

// Watch for props changes
watch(() => props.simulationResults, (newResults) => {
    if (newResults?.simulation) {
        simulation.value = newResults.simulation;
        console.log('Simulation updated from props:', simulation.value);
    }
}, { deep: true, immediate: true });
const saveForm = reactive({
    name: '',
    description: '',
    is_default: false,
});


const getStatusClass = (profitMargin: number) => {
    if (profitMargin < 0) return 'text-red-600 bg-red-50';
    if (profitMargin < 10) return 'text-orange-600 bg-orange-50';
    if (profitMargin < 30) return 'text-yellow-600 bg-yellow-50';
    return 'text-green-600 bg-green-50';
};

const getProfitStatus = (profitMargin: number) => {
    if (profitMargin < 0) return 'Loss';
    if (profitMargin < 10) return 'Low';
    if (profitMargin < 30) return 'Good';
    return 'Excellent';
};

const addTier = () => {
    const maxStorage = Math.max(...form.tier_discounts.map(t => t.storage_gb));
    const maxDiscount = Math.max(...form.tier_discounts.map(t => t.discount_percentage));

    // Limit max discount increase to 30% and make incremental increases smaller
    const newDiscount = Math.min(maxDiscount + 2, 30); // Increase by 2% max, capped at 30%

    form.tier_discounts.push({
        storage_gb: maxStorage * 2,
        discount_percentage: newDiscount,
    });
};

const removeTier = (index: number) => {
    if (form.tier_discounts.length > 1) {
        form.tier_discounts.splice(index, 1);
    }
};

const runSimulation = async () => {
    isSimulating.value = true;
    
    try {
        router.post('/admin/bulk-pricing/simulate', form, {
            preserveScroll: true,
            onSuccess: () => {
                isSimulating.value = false;
            },
            onError: (errors) => {
                console.error('Simulation error:', errors);
                isSimulating.value = false;
            }
        });
    } catch (error) {
        console.error('Simulation error:', error);
        isSimulating.value = false;
    }
};

const applyPricing = () => {
    if (!Object.keys(filteredSimulation.value).length) {
        alert('Jalankan simulasi terlebih dahulu!');
        return;
    }

    if (!confirm('Apakah Anda yakin ingin menerapkan pricing baru ini ke semua hosting plans? Perubahan tidak dapat dibatalkan.')) {
        return;
    }

    isApplying.value = true;
    
    const selectedPlanIds = props.hostingPlans.map(plan => plan.id);
    
    router.post('/admin/bulk-pricing/apply', {
        ...form,
        plan_ids: selectedPlanIds,
    }, {
        onSuccess: () => {
            alert('Bulk pricing berhasil diterapkan!');
        },
        onError: () => {
            alert('Terjadi kesalahan saat menerapkan bulk pricing!');
        },
        onFinish: () => {
            isApplying.value = false;
        },
    });
};

const loadConfig = async (configId: number) => {
    try {
        const response = await fetch(`/admin/bulk-pricing/load-config/${configId}`);
        if (response.ok) {
            const configData = await response.json();
            
            // Update form with loaded config
            Object.assign(form, configData);
            
            // Run simulation with new config
            await runSimulation();
        }
    } catch (error) {
        console.error('Error loading config:', error);
        alert('Gagal memuat konfigurasi!');
    }
};

const saveConfig = () => {
    // Enhanced validation
    if (!saveForm.name.trim()) {
        alert('Nama konfigurasi wajib diisi!');
        return;
    }

    if (saveForm.name.length > 255) {
        alert('Nama konfigurasi terlalu panjang (maksimal 255 karakter)!');
        return;
    }

    // Validate form data
    if (!form.base_price_per_gb || form.base_price_per_gb <= 0) {
        alert('Harga dasar per GB harus lebih dari 0!');
        return;
    }

    if (!form.cost_per_gb || form.cost_per_gb <= 0) {
        alert('Biaya per GB harus lebih dari 0!');
        return;
    }

    if (!form.tier_discounts || form.tier_discounts.length === 0) {
        alert('Minimal harus ada satu tier discount!');
        return;
    }

    // Validate tier discounts
    for (let i = 0; i < form.tier_discounts.length; i++) {
        const tier = form.tier_discounts[i];
        if (!tier.storage_gb || tier.storage_gb <= 0) {
            alert(`Storage GB pada tier ${i + 1} harus lebih dari 0!`);
            return;
        }
        if (tier.discount_percentage < 0 || tier.discount_percentage > 30) {
            alert(`Persentase diskon pada tier ${i + 1} harus antara 0-30% (maksimal 30%)!`);
            return;
        }
    }

    const saveData = {
        name: saveForm.name.trim(),
        description: saveForm.description?.trim() || '',
        is_default: saveForm.is_default || false,
        base_price_per_gb: parseFloat(form.base_price_per_gb),
        cost_per_gb: parseFloat(form.cost_per_gb),
        plan_multipliers: { ...form.plan_multipliers },
        tier_discounts: form.tier_discounts.map(tier => ({
            storage_gb: parseInt(tier.storage_gb),
            discount_percentage: parseFloat(tier.discount_percentage)
        }))
    };

    console.log('Saving config data:', saveData);

    router.post('/admin/bulk-pricing/save-config', saveData, {
        onSuccess: (page) => {
            console.log('Config saved successfully');
            saveForm.name = '';
            saveForm.description = '';
            saveForm.is_default = false;
            showSaveModal.value = false;
        },
        onError: (errors) => {
            console.error('Save config errors:', errors);

            // Show specific error messages
            if (typeof errors === 'object' && errors !== null) {
                const errorMessages = [];
                for (const [field, messages] of Object.entries(errors)) {
                    if (Array.isArray(messages)) {
                        errorMessages.push(...messages);
                    } else {
                        errorMessages.push(String(messages));
                    }
                }
                alert('Gagal menyimpan konfigurasi:\n' + errorMessages.join('\n'));
            } else {
                alert('Gagal menyimpan konfigurasi! Silakan coba lagi.');
            }
        },
        onFinish: () => {
            console.log('Save request finished');
        }
    });
};

const deleteConfig = (configId: number, configName: string) => {
    if (confirm(`Apakah Anda yakin ingin menghapus konfigurasi "${configName}"?`)) {
        router.delete(`/admin/bulk-pricing/delete-config/${configId}`);
    }
};

// Initial simulation data comes from props, no need to run on mount
</script>

<template>
    <Head title="Bulk Pricing Simulator" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Bulk Pricing Simulator</h1>
                    <p class="text-muted-foreground">Simulasi harga hosting dengan tier discount dan analisis keuntungan</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Controls -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- Base Configuration -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Settings class="h-5 w-5" />
                                Konfigurasi Dasar
                            </CardTitle>
                            <CardDescription>Pengaturan harga dasar dan modal</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label for="base-price">Harga Per GB (IDR)</Label>
                                <Input
                                    id="base-price"
                                    v-model.number="form.base_price_per_gb"
                                    type="number"
                                    step="1000"
                                    @input="runSimulation"
                                />
                            </div>
                            <div>
                                <Label for="cost-price">Modal Per GB (IDR)</Label>
                                <Input
                                    id="cost-price"
                                    v-model.number="form.cost_per_gb"
                                    type="number"
                                    step="1000"
                                    @input="runSimulation"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Plan Selection & Multipliers -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Plan Selection & Multipliers</CardTitle>
                            <CardDescription>Pilih plan yang aktif dan atur pengali harganya</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Basic Plan -->
                            <div class="flex items-start space-x-3 p-3 border rounded-lg">
                                <input
                                    id="enable-basic"
                                    type="checkbox"
                                    v-model="enabledPlans.basic"
                                    @change="updateActivePlans"
                                    class="mt-1"
                                />
                                <div class="flex-1">
                                    <label for="enable-basic" class="font-medium cursor-pointer">Basic Plan</label>
                                    <p class="text-sm text-muted-foreground">Plan hosting standar untuk kebutuhan umum</p>
                                    <div v-if="enabledPlans.basic" class="mt-2">
                                        <Label for="basic-multiplier">Multiplier</Label>
                                        <Input
                                            id="basic-multiplier"
                                            v-model.number="form.plan_multipliers.basic"
                                            type="number"
                                            step="0.01"
                                            min="0.1"
                                            max="10"
                                            placeholder="1.0"
                                            @input="runSimulation"
                                            class="mt-1"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Lite Plan -->
                            <div class="flex items-start space-x-3 p-3 border rounded-lg">
                                <input
                                    id="enable-lite"
                                    type="checkbox"
                                    v-model="enabledPlans.lite"
                                    @change="updateActivePlans"
                                    class="mt-1"
                                />
                                <div class="flex-1">
                                    <label for="enable-lite" class="font-medium cursor-pointer">Lite Plan</label>
                                    <p class="text-sm text-muted-foreground">Plan hosting ekonomis dengan fitur terbatas</p>
                                    <div v-if="enabledPlans.lite" class="mt-2">
                                        <Label for="lite-multiplier">Multiplier</Label>
                                        <Input
                                            id="lite-multiplier"
                                            v-model.number="form.plan_multipliers.lite"
                                            type="number"
                                            step="0.01"
                                            min="0.1"
                                            max="10"
                                            placeholder="0.77"
                                            @input="runSimulation"
                                            class="mt-1"
                                        />
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Tier Discounts -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Tier Discounts</CardTitle>
                            <CardDescription>Persentase diskon berdasarkan ukuran storage (maksimal 30%, kenaikan bertahap)</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-for="(tier, index) in form.tier_discounts" :key="index" class="flex gap-2">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Storage
                                    </label>
                                    <Input
                                        v-model.number="tier.storage_gb"
                                        type="number"
                                        placeholder="Contoh: 100 GB"
                                        @input="runSimulation"
                                    />
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Diskon (Max 30%)
                                    </label>
                                    <Input
                                        v-model.number="tier.discount_percentage"
                                        type="number"
                                        step="0.5"
                                        min="0"
                                        max="30"
                                        placeholder="Contoh: 5.0%"
                                        @input="runSimulation"
                                    />
                                </div>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="removeTier(index)"
                                    :disabled="form.tier_discounts.length <= 1"
                                >
                                    ×
                                </Button>
                            </div>
                            <Button variant="outline" size="sm" @click="addTier" class="w-full">
                                + Add Tier
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Saved Configurations -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Konfigurasi Tersimpan</CardTitle>
                            <CardDescription>Load konfigurasi yang sudah disimpan</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div v-if="savedConfigs.length === 0" class="text-sm text-muted-foreground text-center py-4">
                                Belum ada konfigurasi tersimpan
                            </div>
                            <div v-else class="space-y-2">
                                <div v-for="config in savedConfigs" :key="config.id" class="flex items-center justify-between p-2 border rounded">
                                    <div class="flex-1">
                                        <div class="font-medium">{{ config.name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ config.description || 'Tanpa deskripsi' }}</div>
                                        <div v-if="config.is_default" class="text-xs text-green-600 font-medium">Default</div>
                                    </div>
                                    <div class="flex gap-1">
                                        <Button size="sm" variant="outline" @click="loadConfig(config.id)">
                                            <Upload class="h-3 w-3" />
                                        </Button>
                                        <Button size="sm" variant="outline" @click="deleteConfig(config.id, config.name)" :disabled="config.is_default">
                                            <Trash2 class="h-3 w-3" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Actions -->
                    <Card>
                        <CardContent class="pt-6">
                            <div class="space-y-3">
                                <Button @click="runSimulation" :disabled="isSimulating" class="w-full">
                                    <Calculator class="h-4 w-4 mr-2" />
                                    {{ isSimulating ? 'Menghitung...' : 'Jalankan Simulasi' }}
                                </Button>
                                
                                <Button variant="outline" class="w-full" @click="showSaveModal = true">
                                    <Save class="h-4 w-4 mr-2" />
                                    Simpan Konfigurasi
                                </Button>
                                
                                <Button @click="applyPricing" :disabled="isApplying || !Object.keys(filteredSimulation).length" class="w-full bg-red-600 hover:bg-red-700 text-white" variant="destructive">
                                    <DollarSign class="h-4 w-4 mr-2" />
                                    {{ isApplying ? 'Menerapkan...' : 'Terapkan Harga Baru' }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Simulation Results -->
                <div class="lg:col-span-8">
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle class="flex items-center gap-2">
                                        <TrendingUp class="h-5 w-5" />
                                        Simulasi Harga & Keuntungan
                                    </CardTitle>
                                    <CardDescription>Analisis pricing dengan margin keuntungan</CardDescription>
                                </div>
                                <div class="flex items-center gap-1 bg-muted rounded-lg p-1">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        :class="[
                                            'transition-all duration-200',
                                            viewMode === 'basic' ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground'
                                        ]"
                                        @click="viewMode = 'basic'"
                                    >
                                        Basic
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        :class="[
                                            'transition-all duration-200',
                                            viewMode === 'lite' ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground'
                                        ]"
                                        @click="viewMode = 'lite'"
                                    >
                                        Lite
                                    </Button>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div v-if="Object.keys(filteredSimulation).length === 0" class="py-8 text-center text-muted-foreground">
                                Jalankan simulasi untuk melihat hasil
                            </div>
                            <div v-else class="space-y-6">
                                <div v-for="(planData, planType) in filteredSimulation" :key="planType">
                                    <h3 class="text-lg font-semibold mb-3 capitalize">{{ planType }} Plan</h3>
                                    <div class="overflow-x-auto">
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>Plan</TableHead>
                                                    <TableHead v-if="viewMode === 'basic'">Specs</TableHead>
                                                    <TableHead>Harga Saat Ini</TableHead>
                                                    <TableHead>Harga Baru</TableHead>
                                                    <TableHead v-if="viewMode === 'basic'">Selisih</TableHead>
                                                    <TableHead v-if="viewMode === 'lite'">Discount</TableHead>
                                                    <TableHead v-if="viewMode === 'lite'">Modal</TableHead>
                                                    <TableHead>Profit</TableHead>
                                                    <TableHead v-if="viewMode === 'lite'">Margin</TableHead>
                                                    <TableHead v-if="viewMode === 'basic'">Status</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                <TableRow v-for="data in planData" :key="data.plan_id">
                                                    <TableCell class="font-medium">
                                                        <div class="font-semibold">{{ data.plan_name }}</div>
                                                    </TableCell>
                                                    <TableCell v-if="viewMode === 'basic'" class="text-sm text-muted-foreground">
                                                        <div>{{ data.storage_gb }}GB Storage</div>
                                                        <div>{{ data.cpu_cores }} Core CPU</div>
                                                        <div>{{ data.ram_gb }}GB RAM</div>
                                                    </TableCell>
                                                    <TableCell class="font-medium">
                                                        {{ formatPrice(data.current_price) }}
                                                    </TableCell>
                                                    <TableCell class="font-medium">
                                                        {{ formatPrice(data.new_total_price) }}
                                                    </TableCell>
                                                    <TableCell v-if="viewMode === 'basic'" :class="data.price_difference >= 0 ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                                                        {{ data.price_difference >= 0 ? '+' : '' }}{{ formatPrice(data.price_difference) }}
                                                    </TableCell>
                                                    <TableCell v-if="viewMode === 'lite'">{{ data.discount_percentage }}%</TableCell>
                                                    <TableCell v-if="viewMode === 'lite'">{{ formatPrice(data.total_cost) }}</TableCell>
                                                    <TableCell :class="data.profit >= 0 ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                                                        {{ formatPrice(data.profit) }}
                                                    </TableCell>
                                                    <TableCell v-if="viewMode === 'lite'" :class="data.profit_margin >= 0 ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                                                        {{ data.profit_margin.toFixed(1) }}%
                                                    </TableCell>
                                                    <TableCell v-if="viewMode === 'basic'">
                                                        <span :class="`px-2 py-1 rounded text-xs font-medium ${getStatusClass(data.profit_margin)}`">
                                                            {{ getProfitStatus(data.profit_margin) }}
                                                        </span>
                                                    </TableCell>
                                                </TableRow>
                                            </TableBody>
                                        </Table>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Save Config Modal -->
        <div v-if="showSaveModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showSaveModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Simpan Konfigurasi</h2>
                    <button @click="showSaveModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <form @submit.prevent="saveConfig" action="#" class="space-y-4">
                    <div>
                        <Label for="config-name">Nama Konfigurasi</Label>
                        <Input
                            id="config-name"
                            v-model="saveForm.name"
                            placeholder="Masukkan nama konfigurasi"
                            required
                        />
                    </div>
                    <div>
                        <Label for="config-description">Deskripsi (Opsional)</Label>
                        <textarea
                            id="config-description"
                            v-model="saveForm.description"
                            placeholder="Deskripsi konfigurasi"
                            rows="3"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        ></textarea>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input
                            id="is-default"
                            v-model="saveForm.is_default"
                            type="checkbox"
                            class="rounded border-gray-300"
                            @click.stop
                            @change.stop
                        />
                        <Label for="is-default">Set sebagai konfigurasi default</Label>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="showSaveModal = false" class="cursor-pointer">
                            Batal
                        </Button>
                        <Button type="submit">
                            <Save class="h-4 w-4 mr-2" />
                            Simpan
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>