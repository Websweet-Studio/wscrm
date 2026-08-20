<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import RichTextEditor from '@/components/RichTextEditor.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Megaphone, Send, Users } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    customerCount: number;
    activeCustomerCount: number;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Broadcast Email', href: '/admin/broadcast' },
];

const form = useForm({
    subject: '',
    body: '',
    target: 'all' as 'all' | 'active',
});

const targetCount = computed(() =>
    form.target === 'active' ? props.activeCustomerCount : props.customerCount
);

const submit = () => {
    form.post('/admin/broadcast', {
        preserveScroll: true,
        onSuccess: () => {
            // Kosongkan subject & body setelah berhasil; target dipertahankan.
            form.reset('subject', 'body');
        },
    });
};
</script>

<template>
    <Head title="Broadcast Email" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-medium" style="font-family: Georgia, serif;">Broadcast Email</h1>
                <p class="text-muted-foreground">
                    Kirim pengumuman massal ke member — misalnya saat merilis model AI baru atau promo.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <Card class="rounded-2xl shadow-[rgba(0,0,0,0.05)_0px_4px_24px]">
                    <CardHeader class="pb-2">
                        <CardDescription>Total Customer</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ customerCount }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card class="rounded-2xl shadow-[rgba(0,0,0,0.05)_0px_4px_24px]">
                    <CardHeader class="pb-2">
                        <CardDescription>Customer Aktif</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ activeCustomerCount }}</CardTitle>
                    </CardHeader>
                </Card>
            </div>

            <Card class="rounded-2xl shadow-[rgba(0,0,0,0.05)_0px_4px_24px]">
                <CardHeader>
                    <CardTitle class="flex items-center space-x-2 font-serif font-medium tracking-tight">
                        <Megaphone class="h-4 w-4" />
                        <span>Compose Broadcast</span>
                    </CardTitle>
                    <CardDescription>
                        Subject & body akan dikirim ke email member. Body mendukung format teks kaya (bold, list, link, dll).
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <Label for="subject">Subject *</Label>
                            <Input
                                id="subject"
                                v-model="form.subject"
                                required
                                maxlength="255"
                                placeholder="mis. Model baru mimo-v2.5 kini tersedia!"
                                class="mt-1"
                            />
                            <p v-if="form.errors.subject" class="mt-1 text-sm text-destructive">{{ form.errors.subject }}</p>
                        </div>

                        <div>
                            <Label>Target *</Label>
                            <div class="mt-1 flex flex-wrap gap-3">
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-3 text-sm">
                                    <input v-model="form.target" type="radio" value="all" class="h-4 w-4" />
                                    <span class="font-medium">Semua customer</span>
                                    <span class="text-muted-foreground">({{ customerCount }})</span>
                                </label>
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-3 text-sm">
                                    <input v-model="form.target" type="radio" value="active" class="h-4 w-4" />
                                    <span class="font-medium">Customer aktif saja</span>
                                    <span class="text-muted-foreground">({{ activeCustomerCount }})</span>
                                </label>
                            </div>
                            <p v-if="form.errors.target" class="mt-1 text-sm text-destructive">{{ form.errors.target }}</p>
                        </div>

                        <div>
                            <Label>Body *</Label>
                            <div class="mt-1">
                                <RichTextEditor v-model="form.body" :height="260" placeholder="Tulis isi pengumuman di sini..." />
                            </div>
                            <p v-if="form.errors.body" class="mt-1 text-sm text-destructive">{{ form.errors.body }}</p>
                        </div>

                        <div class="flex items-center gap-3 border-t pt-4">
                            <Button type="submit" :disabled="form.processing" class="cursor-pointer">
                                <Send class="mr-2 h-4 w-4" />
                                {{ form.processing ? 'Mengirim...' : `Kirim ke ${targetCount} customer` }}
                            </Button>
                            <span class="inline-flex items-center gap-1.5 text-xs text-muted-foreground">
                                <Users class="h-3.5 w-3.5" />
                                Email dikirim antrean (queue) agar tidak membebani server.
                            </span>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
