<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Globe, Server, Shield, Users } from 'lucide-vue-next';

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

const benefits = [
    'Control panel yang mudah digunakan',
    'Sertifikat SSL gratis',
    'Backup otomatis harian',
    'Install aplikasi sekali klik',
    'Email hosting termasuk',
    'Garansi 30 hari uang kembali',
];
</script>

<template>
    <CustomerPublicLayout title="Hosting Web Profesional & Layanan Domain - WebSweetStudio">
        <!-- Hero Section -->
        <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16 lg:py-20">
            <div class="mb-12 text-center sm:mb-16">
                <h1 class="mb-4 text-3xl leading-tight font-bold text-gray-900 sm:mb-6 sm:text-4xl md:text-5xl lg:text-6xl dark:text-white">
                    Hosting Web Profesional
                    <span class="block text-blue-600 dark:text-blue-400">Dibuat Sederhana</span>
                </h1>
                <p class="mx-auto mb-6 max-w-2xl text-base text-gray-600 sm:mb-8 sm:max-w-3xl sm:text-lg lg:text-xl dark:text-gray-300">
                    Dapatkan layanan hosting web terpercaya dan registrasi domain dengan dukungan ahli. Sempurna untuk bisnis, developer, dan website
                    pribadi.
                </p>
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-center sm:gap-4">
                    <Button size="lg" class="w-full sm:w-auto" asChild>
                        <Link href="/hosting">Lihat Paket Hosting</Link>
                    </Button>
                    <Button size="lg" variant="outline" class="w-full sm:w-auto" asChild>
                        <Link href="/domains">Cari Domain</Link>
                    </Button>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="mb-12 grid gap-4 sm:grid-cols-2 sm:gap-6 lg:mb-16 lg:grid-cols-4">
                <Card v-for="feature in features" :key="feature.title" class="text-center transition-all hover:shadow-lg">
                    <CardHeader class="pb-4">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                            <component :is="feature.icon" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <CardTitle class="text-base sm:text-lg">{{ feature.title }}</CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0">
                        <CardDescription class="text-sm sm:text-base">{{ feature.description }}</CardDescription>
                    </CardContent>
                </Card>
            </div>
        </section>
    </CustomerPublicLayout>
</template>
