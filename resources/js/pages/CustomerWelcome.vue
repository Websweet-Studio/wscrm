<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Globe, Monitor, Server, Shield, Users } from 'lucide-vue-next';
import { computed } from 'vue';

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
    use_bulk_pricing?: boolean;
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
    companyWhatsapp: string | null;
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
        icon: Monitor,
        title: 'Demo Website',
        description: 'Lihat contoh website profesional yang bisa Anda miliki',
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
                    <Button asChild class="text-lg px-6 py-4" style="background-color: var(--primary); color: var(--primary-foreground); border-radius: 12px;">
                        <Link href="/hosting">Lihat Paket Hosting &amp; VPS</Link>
                    </Button>
                    <Button asChild variant="outline" class="text-lg px-6 py-4" style="background-color: var(--primary-foreground); color: #4d4c48; border: 1px solid #e8e6dc; border-radius: 12px;">
                        <Link href="/domains">Cari Domain</Link>
                    </Button>
                    <Button asChild variant="outline" class="text-lg px-6 py-4" style="background-color: var(--primary-foreground); color: #4d4c48; border: 1px solid #e8e6dc; border-radius: 12px;">
                        <Link href="/demo-web">Demo Website</Link>
                    </Button>
                </div>
            </div>


            <!-- Features Grid -->
            <div class="mb-12 grid gap-4 sm:grid-cols-2 sm:gap-6 lg:mb-16 lg:grid-cols-5">
                <Card v-for="feature in features" :key="feature.title" class="text-center transition-all" style="background-color: var(--primary-foreground); border: 1px solid #f0eee6; border-radius: 12px; box-shadow: rgba(0,0,0,0.05) 0px 4px 24px;">
                    <CardHeader class="pb-4">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg" style="background-color: #e8e6dc;">
                            <component :is="feature.icon" class="h-6 w-6" style="color: var(--primary);" />
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
