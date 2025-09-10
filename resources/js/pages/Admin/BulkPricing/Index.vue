<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatPrice } from '@/lib/utils';
import { Head, router } from '@inertiajs/vue3';
import { Calculator, DollarSign, Settings, TrendingUp } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';

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

interface SimulationData {
    storage_gb: number;
    discount_percentage: number;
    price_per_gb: number;
    total_price: number;
    total_cost: number;
    profit: number;
    profit_per_gb: number;
    profit_margin: number;
}

const props = defineProps<{
    pricingTiers: PricingTier[];
    hostingPlans: HostingPlan[];
}>();

const form = reactive({
    base_price_per_gb: 150000,
    cost_per_gb: 112500,
    plan_multipliers: {
        basic: 1.0,
        lite: 0.77,
        premium: 1.3,
    },
    tier_discounts: props.pricingTiers.map(tier => ({
        storage_gb: tier.storage_gb,
        discount_percentage: tier.discount_percentage,
    })),
});

const simulation = ref<Record<string, SimulationData[]>>({});
const isSimulating = ref(false);
const isApplying = ref(false);

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
        const response = await fetch('/admin/bulk-pricing/simulate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(form),
        });

        if (response.ok) {
            const data = await response.json();
            simulation.value = data.simulation;
        }
    } catch (error) {
        console.error('Simulation error:', error);
    } finally {
        isSimulating.value = false;
    }
};

const applyPricing = () => {
    if (!Object.keys(simulation.value).length) {
        alert('Jalankan simulasi terlebih dahulu!');
        return;
    }

    isApplying.value = true;
    
    const selectedPlanIds = props.hostingPlans.map(plan => plan.id);
    
    router.post('/admin/bulk-pricing/apply', {
        ...form,
        plan_ids: selectedPlanIds,
    }, {
        onFinish: () => {
            isApplying.value = false;
        },
    });
};

// Run initial simulation
runSimulation();
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

                    <!-- Actions -->
                    <Card>
                        <CardContent class="pt-6">
                            <div class="space-y-3">
                                <Button @click="runSimulation" :disabled="isSimulating" class="w-full">
                                    <Calculator class="h-4 w-4 mr-2" />
                                    {{ isSimulating ? 'Simulating...' : 'Run Simulation' }}
                                </Button>
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
                                                    <TableHead>Storage</TableHead>
                                                    <TableHead>Discount</TableHead>
                                                    <TableHead>Price/GB</TableHead>
                                                    <TableHead>Total Price</TableHead>
                                                    <TableHead>Total Cost</TableHead>
                                                    <TableHead>Profit</TableHead>
                                                    <TableHead>Margin</TableHead>
                                                    <TableHead>Status</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                <TableRow v-for="data in planData" :key="data.storage_gb">
                                                    <TableCell class="font-medium">{{ data.storage_gb }}GB</TableCell>
                                                    <TableCell>{{ data.discount_percentage }}%</TableCell>
                                                    <TableCell>{{ formatPrice(data.price_per_gb) }}</TableCell>
                                                    <TableCell>{{ formatPrice(data.total_price) }}</TableCell>
                                                    <TableCell>{{ formatPrice(data.total_cost) }}</TableCell>
                                                    <TableCell :class="data.profit >= 0 ? 'text-green-600' : 'text-red-600'">
                                                        {{ formatPrice(data.profit) }}
                                                    </TableCell>
                                                    <TableCell :class="data.profit_margin >= 0 ? 'text-green-600' : 'text-red-600'">
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