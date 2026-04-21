<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Calculator, Globe, Server, Shield, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface DomainPrice {
    id: number;
    extension: string;
    base_cost: number;
    renewal_cost: number;
    selling_price: number;
    renewal_price_with_tax: number;
    is_active: boolean;
}

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

interface ServicePlan {
    id: number;
    name: string;
    category: string;
    description: string;
    price: number;
    features: Record<string, any>;
    is_active: boolean;
}

interface Props {
    domainPrices: DomainPrice[];
    hostingPlans: HostingPlan[];
    servicePlans: ServicePlan[];
}

const props = defineProps<Props>();

const features = [
    {
        icon: Server,
        title: 'Hosting Terpercaya',
        description: 'Hosting web profesional dengan jaminan uptime 99.9%',
    },
    {
        icon: Globe,
        title: 'Registrasi Domain',
        description: 'Daftarkan domain sempurna Anda dengan harga terbaik',
    },
    {
        icon: Shield,
        title: 'Aman & Terlindungi',
        description: 'Sertifikat SSL dan backup harian sudah termasuk',
    },
    {
        icon: Users,
        title: 'Support 24/7',
        description: 'Tim support ahli siap membantu Anda kapan saja',
    },
];

const selectedDomain = ref<number | null>(null);
const selectedHosting = ref<number | null>(null);
const selectedServices = ref<number[]>([]);
const domainName = ref('contoh');

const selectedDomainPrice = computed(() => {
    return filteredDomainPrices.value.find((d) => d.id === selectedDomain.value);
});

const selectedHostingPlan = computed(() => {
    return filteredHostingPlans.value.find((h) => h.id === selectedHosting.value);
});

const selectedServicesList = computed(() => {
    return filteredServicePlans.value.filter((s) => selectedServices.value.includes(s.id));
});

const toggleService = (serviceId: number) => {
    const index = selectedServices.value.indexOf(serviceId);
    if (index > -1) {
        selectedServices.value.splice(index, 1);
    } else {
        selectedServices.value.push(serviceId);
    }
};

const getDomainPrice = (domain: any) => {
    if (!domain) return 0;
    const price = parseFloat(String(domain.selling_price).replace(/[^0-9.-]/g, ''));
    return !isNaN(price) ? price : 0;
};

const getHostingPrice = (plan: any) => {
    if (!plan) return 0;
    const price = parseFloat(String(plan.selling_price).replace(/[^0-9.-]/g, ''));
    return !isNaN(price) ? price : 0;
};

const getServicePrice = (service: any) => {
    if (!service) return 0;
    const price = parseFloat(String(service.price).replace(/[^0-9.-]/g, ''));
    return !isNaN(price) ? price : 0;
};

const calculateTotal = computed(() => {
    let total = 0;

    if (selectedDomainPrice.value) {
        total += getDomainPrice(selectedDomainPrice.value);
    }

    if (selectedHostingPlan.value) {
        const hostingPrice = getHostingPrice(selectedHostingPlan.value);
        const discount = Number(selectedHostingPlan.value.discount_percent) || 0;
        total += hostingPrice * (1 - discount / 100);
    }

    selectedServicesList.value.forEach((service) => {
        total += getServicePrice(service);
    });

    return total;
});

const filteredDomainPrices = computed(() => {
    return (props.domainPrices || []).filter((d) => d.is_active);
});

const filteredHostingPlans = computed(() => {
    return (props.hostingPlans || []).filter((h) => h.is_active && !h.plan_name.toLowerCase().includes('lite'));
});

const filteredServicePlans = computed(() => {
    return (props.servicePlans || []).filter((s) => s.is_active);
});

const formatPrice = (price: number) => {
    const validPrice = typeof price === 'number' && !isNaN(price) ? price : 0;
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(validPrice);
};
</script>

<template>
    <CustomerPublicLayout title="Hosting Web Profesional & Layanan Domain - WebSweetStudio">
        <!-- Hero Section -->
        <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16 lg:py-20">
            <div class="mb-12 text-center sm:mb-16">
                <h1 class="mb-4 text-4xl leading-tight font-medium sm:text-5xl md:text-6xl" style="color: #141413; line-height: 1.1; font-family: Georgia, serif;">
                    Hosting Web Profesional
                </h1>
                <p class="mx-auto mb-6 max-w-2xl text-base sm:text-lg lg:text-xl" style="color: #5e5d59; line-height: 1.6;">
                    Dapatkan layanan hosting web terpercaya dan registrasi domain dengan dukungan ahli. Sempurna untuk bisnis, developer, dan website pribadi.
                </p>
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-center sm:gap-4">
                    <Button asChild class="text-lg px-6 py-4" style="background-color: #c96442; color: #faf9f5; border-radius: 12px;">
                        <Link href="/hosting">Lihat Paket Hosting</Link>
                    </Button>
                    <Button asChild variant="outline" class="text-lg px-6 py-4" style="background-color: #faf9f5; color: #4d4c48; border: 1px solid #e8e6dc; border-radius: 12px;">
                        <Link href="/domains">Cari Domain</Link>
                    </Button>
                </div>
            </div>

            <!-- Simulation Section -->
            <div class="mb-12">
                <Card style="background-color: #faf9f5; border: 1px solid #f0eee6; border-radius: 16px; box-shadow: rgba(0,0,0,0.05) 0px 4px 24px;">
                    <CardHeader>
                        <div class="flex items-center gap-3">
                            <div class="rounded-full p-2" style="background-color: #e8e6dc;">
                                <Calculator class="h-6 w-6" style="color: #c96442;" />
                            </div>
                            <div>
                                <CardTitle class="text-xl font-medium" style="color: #141413; font-family: Georgia, serif;">Simulasi Harga</CardTitle>
                                <CardDescription style="color: #5e5d59;">Pilih domain, hosting, dan layanan tambahan untuk melihat estimasi biaya</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-8 md:grid-cols-2">
                            <div class="space-y-6">
                                <div class="space-y-3">
                                    <Label style="color: #4d4c48;">Nama Domain</Label>
                                    <div class="flex gap-2">
                                        <Input v-model="domainName" placeholder="nama-domain" class="flex-1" style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;" />
                                        <select
                                            v-model="selectedDomain"
                                            class="w-48 rounded-md px-3 py-2 text-sm"
                                            style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                                        >
                                            <option :value="null" disabled>Pilih ekstensi</option>
                                            <option v-for="domain in filteredDomainPrices" :key="domain.id" :value="domain.id">
                                                {{ domain.extension }} - {{ formatPrice(getDomainPrice(domain)) }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <Label style="color: #4d4c48;">Paket Hosting</Label>
                                    <select
                                            v-model="selectedHosting"
                                            class="w-full rounded-md px-3 py-2 text-sm"
                                            style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                                        >
                                            <option :value="null" disabled>Pilih paket hosting</option>
                                            <option v-for="plan in filteredHostingPlans" :key="plan.id" :value="plan.id">
                                                {{ plan.plan_name }} - {{ formatPrice(getHostingPrice(plan) * (1 - (plan.discount_percent || 0) / 100)) }}/tahun
                                            </option>
                                        </select>
                                </div>

                                <Separator style="background-color: #f0eee6;" />

                                <div class="space-y-4">
                                    <Label class="text-base font-medium" style="color: #4d4c48;">Layanan Tambahan</Label>
                                    <div v-for="service in filteredServicePlans" :key="service?.id" class="flex items-center space-x-3">
                                        <input
                                            v-if="service"
                                            type="checkbox"
                                            :id="'service-' + service.id"
                                            :checked="selectedServices.includes(service.id)"
                                            @change="toggleService(service.id)"
                                            class="h-4 w-4 rounded"
                                            style="border-color: #e8e6dc; accent-color: #c96442;"
                                        />
                                        <label v-if="service" :for="'service-' + service.id" class="font-normal cursor-pointer" style="color: #5e5d59;">
                                            {{ service.name }} ({{ formatPrice(getServicePrice(service)) }}/tahun)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <Card class="h-full" style="background-color: #ffffff; border: 1px solid #f0eee6; border-radius: 16px;">
                                    <CardHeader>
                                        <CardTitle class="text-lg font-medium" style="color: #141413; font-family: Georgia, serif;">Ringkasan Biaya Tahunan</CardTitle>
                                    </CardHeader>
                                    <CardContent class="space-y-4">
                                        <div v-if="selectedDomainPrice" class="flex justify-between">
                                            <span style="color: #5e5d59;">
                                                Domain {{ selectedDomainPrice.extension }}
                                            </span>
                                            <span class="font-medium" style="color: #4d4c48;">{{ formatPrice(getDomainPrice(selectedDomainPrice)) }}</span>
                                        </div>
                                        <div v-if="selectedHostingPlan" class="flex justify-between">
                                            <span style="color: #5e5d59;">
                                                Hosting {{ selectedHostingPlan.plan_name }}
                                            </span>
                                            <span class="font-medium" style="color: #4d4c48;">
                                                {{ formatPrice(getHostingPrice(selectedHostingPlan) * (1 - (selectedHostingPlan.discount_percent || 0) / 100)) }}
                                            </span>
                                        </div>
                                        <div v-for="service in selectedServicesList" :key="service?.id" class="flex justify-between">
                                            <span style="color: #5e5d59;">{{ service.name }}</span>
                                            <span class="font-medium" style="color: #4d4c48;">{{ formatPrice(getServicePrice(service)) }}</span>
                                        </div>
                                        <Separator style="background-color: #f0eee6;" />
                                        <div class="flex justify-between text-xl font-bold" style="color: #c96442;">
                                            <span>Total Tahunan</span>
                                            <span>{{ formatPrice(calculateTotal) }}</span>
                                        </div>
                                        <div class="pt-2">
                                            <Button class="w-full" size="lg" :disabled="!selectedDomain || !selectedHosting" style="background-color: #c96442; color: #faf9f5; border-radius: 12px;">
                                                <Calculator class="mr-2 h-4 w-4" />
                                                Pesan Sekarang
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Features Grid -->
            <div class="mb-12 grid gap-4 sm:grid-cols-2 sm:gap-6 lg:mb-16 lg:grid-cols-4">
                <Card v-for="feature in features" :key="feature.title" class="text-center transition-all" style="background-color: #faf9f5; border: 1px solid #f0eee6; border-radius: 12px; box-shadow: rgba(0,0,0,0.05) 0px 4px 24px;">
                    <CardHeader class="pb-4">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg" style="background-color: #e8e6dc;">
                            <component :is="feature.icon" class="h-6 w-6" style="color: #c96442;" />
                        </div>
                        <CardTitle class="text-base sm:text-lg font-medium" style="color: #141413; font-family: Georgia, serif;">{{ feature.title }}</CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0">
                        <CardDescription style="color: #5e5d59;">{{ feature.description }}</CardDescription>
                    </CardContent>
                </Card>
            </div>
        </section>
    </CustomerPublicLayout>
</template>
