<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatPrice } from '@/lib/utils';
import { Head, router } from '@inertiajs/vue3';
import { Calculator, ChevronDown, DollarSign, Download, Save, Settings, Trash2, TrendingUp, Upload } from 'lucide-vue-next';
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

const uniquePlanTypes = computed(() => {
    const types = new Set(props.hostingPlans.map(plan => plan.plan_name.toLowerCase()));
    return Array.from(types);
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
    form.tier_discounts.push({
        storage_gb: maxStorage * 2,
        discount_percentage: 50,
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
    if (!Object.keys(simulation.value).length) {
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
    if (!saveForm.name.trim()) {
        alert('Nama konfigurasi wajib diisi!');
        return;
    }

    router.post('/admin/bulk-pricing/save-config', {
        ...saveForm,
        ...form,
    }, {
        onSuccess: () => {
            saveForm.name = '';
            saveForm.description = '';
            saveForm.is_default = false;
        },
        onError: () => {
            alert('Gagal menyimpan konfigurasi!');
        },
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
        <div class="space-y-6 p-6">
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

                    <!-- Plan Multipliers -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Plan Multipliers</CardTitle>
                            <CardDescription>Pengali harga untuk setiap tipe plan</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-for="planType in uniquePlanTypes" :key="planType">
                                <Label :for="`multiplier-${planType}`">{{ planType.charAt(0).toUpperCase() + planType.slice(1) }}</Label>
                                <Input
                                    :id="`multiplier-${planType}`"
                                    v-model.number="form.plan_multipliers[planType]"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    @input="runSimulation"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Tier Discounts -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Tier Discounts</CardTitle>
                            <CardDescription>Persentase diskon berdasarkan ukuran storage</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-for="(tier, index) in form.tier_discounts" :key="index" class="flex gap-2">
                                <div class="flex-1">
                                    <Input
                                        v-model.number="tier.storage_gb"
                                        type="number"
                                        placeholder="GB"
                                        @input="runSimulation"
                                    />
                                </div>
                                <div class="flex-1">
                                    <Input
                                        v-model.number="tier.discount_percentage"
                                        type="number"
                                        step="0.1"
                                        max="100"
                                        placeholder="Discount %"
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
                                    {{ isSimulating ? 'Simulating...' : 'Run Simulation' }}
                                </Button>
                                
                                <Dialog>
                                    <DialogTrigger as-child>
                                        <Button variant="outline" class="w-full">
                                            <Save class="h-4 w-4 mr-2" />
                                            Save Config
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <DialogHeader>
                                            <DialogTitle>Simpan Konfigurasi</DialogTitle>
                                            <DialogDescription>
                                                Simpan konfigurasi saat ini untuk digunakan kembali di kemudian hari
                                            </DialogDescription>
                                        </DialogHeader>
                                        <div class="space-y-4">
                                            <div>
                                                <Label for="config-name">Nama Konfigurasi</Label>
                                                <Input
                                                    id="config-name"
                                                    v-model="saveForm.name"
                                                    placeholder="Masukkan nama konfigurasi"
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
                                                />
                                                <Label for="is-default">Set sebagai konfigurasi default</Label>
                                            </div>
                                        </div>
                                        <DialogFooter class="gap-2">
                                            <DialogClose as-child>
                                                <Button variant="outline">
                                                    Batal
                                                </Button>
                                            </DialogClose>
                                            <DialogClose as-child>
                                                <Button @click="saveConfig">
                                                    <Save class="h-4 w-4 mr-2" />
                                                    Simpan
                                                </Button>
                                            </DialogClose>
                                        </DialogFooter>
                                    </DialogContent>
                                </Dialog>
                                
                                <Button @click="applyPricing" :disabled="isApplying || !Object.keys(simulation).length" class="w-full" variant="destructive">
                                    <DollarSign class="h-4 w-4 mr-2" />
                                    {{ isApplying ? 'Applying...' : 'Apply Pricing' }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Simulation Results -->
                <div class="lg:col-span-8">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <TrendingUp class="h-5 w-5" />
                                Simulasi Harga & Keuntungan
                            </CardTitle>
                            <CardDescription>Analisis pricing dengan margin keuntungan</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="Object.keys(simulation).length === 0" class="py-8 text-center text-muted-foreground">
                                Jalankan simulasi untuk melihat hasil
                            </div>
                            <div v-else class="space-y-6">
                                <div v-for="(planData, planType) in simulation" :key="planType">
                                    <h3 class="text-lg font-semibold mb-3 capitalize">{{ planType }} Plan</h3>
                                    <div class="overflow-x-auto">
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>Plan</TableHead>
                                                    <TableHead>Specs</TableHead>
                                                    <TableHead>Harga Saat Ini</TableHead>
                                                    <TableHead>Harga Baru</TableHead>
                                                    <TableHead>Selisih</TableHead>
                                                    <TableHead>Discount</TableHead>
                                                    <TableHead>Modal</TableHead>
                                                    <TableHead>Profit</TableHead>
                                                    <TableHead>Margin</TableHead>
                                                    <TableHead>Status</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                <TableRow v-for="data in planData" :key="data.plan_id">
                                                    <TableCell class="font-medium">
                                                        <div class="font-semibold">{{ data.plan_name }}</div>
                                                    </TableCell>
                                                    <TableCell class="text-sm text-muted-foreground">
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
                                                    <TableCell :class="data.price_difference >= 0 ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                                                        {{ data.price_difference >= 0 ? '+' : '' }}{{ formatPrice(data.price_difference) }}
                                                    </TableCell>
                                                    <TableCell>{{ data.discount_percentage }}%</TableCell>
                                                    <TableCell>{{ formatPrice(data.total_cost) }}</TableCell>
                                                    <TableCell :class="data.profit >= 0 ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                                                        {{ formatPrice(data.profit) }}
                                                    </TableCell>
                                                    <TableCell :class="data.profit_margin >= 0 ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                                                        {{ data.profit_margin.toFixed(1) }}%
                                                    </TableCell>
                                                    <TableCell>
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
    </AppLayout>
</template>