<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

interface DemoCategory {
    id: number;
    name: string;
    slug: string;
}

interface DemoPackage {
    id: number;
    name: string;
    slug: string;
}

interface Props {
    categories: DemoCategory[];
    packages: DemoPackage[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Demo Website', href: '/admin/demo-websites' },
    { title: 'Tambah Demo Website', href: '/admin/demo-websites/create' },
];

const imagePreview = ref<string | null>(null);

const form = useForm({
    title: '',
    url: '',
    demo_category_id: '',
    demo_packages: [] as string[],
    featured_image: null as File | null,
    description: '',
    is_active: true,
    sort_order: 0,
});

const handleImageChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0] || null;
    form.featured_image = file;
    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
    }
    imagePreview.value = file ? URL.createObjectURL(file) : null;
};

const submit = () => {
    form.post('/admin/demo-websites', {
        onSuccess: () => {
            if (imagePreview.value) {
                URL.revokeObjectURL(imagePreview.value);
                imagePreview.value = null;
            }
        },
    });
};
</script>

<template>
    <Head title="Tambah Demo Website" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-medium tracking-tight" style="font-family: Georgia, serif;">
                        Tambah Demo Website
                    </h1>
                    <p class="text-muted-foreground">Tambahkan demo website untuk showcase portofolio</p>
                </div>
                <Button variant="outline" @click="router.visit('/admin/demo-websites')" class="cursor-pointer">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Kembali
                </Button>
            </div>

            <!-- Error banner -->
            <div v-if="Object.keys(form.errors).length > 0 && !form.processing"
                class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <p class="font-medium">Gagal menyimpan. Periksa kembali isian berikut:</p>
                <ul class="mt-1 ml-4 list-disc">
                    <li v-for="(msg, key) in form.errors" :key="key">{{ msg }}</li>
                </ul>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Informasi Demo</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Title -->
                        <div>
                            <Label for="title">Judul *</Label>
                            <Input id="title" v-model="form.title" placeholder="Contoh: Website Travel - Desain 1" required />
                            <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
                        </div>

                        <!-- URL -->
                        <div>
                            <Label for="url">URL Demo *</Label>
                            <Input id="url" v-model="form.url" type="url" placeholder="https://demo.example.com" required />
                            <p v-if="form.errors.url" class="mt-1 text-xs text-red-500">{{ form.errors.url }}</p>
                        </div>

                        <!-- Category -->
                        <div>
                            <Label for="category">Kategori *</Label>
                            <select id="category" v-model="form.demo_category_id"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" required>
                                <option value="">Pilih Kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <p v-if="form.errors.demo_category_id" class="mt-1 text-xs text-red-500">{{ form.errors.demo_category_id }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <Label for="description">Deskripsi</Label>
                            <Textarea id="description" v-model="form.description" rows="4" placeholder="Deskripsi demo website..." />
                            <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">{{ form.errors.description }}</p>
                        </div>

                        <!-- Packages -->
                        <div>
                            <Label>Paket (bisa lebih dari satu)</Label>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <label v-for="pkg in packages" :key="pkg.id"
                                    class="flex items-center gap-2 rounded-md border px-3 py-2 cursor-pointer transition-colors"
                                    :class="form.demo_packages.includes(String(pkg.id))
                                        ? 'border-primary bg-primary/10 text-primary'
                                        : 'border-input hover:bg-accent text-foreground'">
                                    <input type="checkbox" :value="String(pkg.id)" v-model="form.demo_packages"
                                        class="rounded border border-input" />
                                    <span class="text-sm">{{ pkg.name }}</span>
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-muted-foreground">Pilih satu atau lebih paket yang sesuai</p>
                        </div>

                        <!-- Featured Image -->
                        <div>
                            <Label for="featured-image">Featured Image</Label>
                            <div v-if="imagePreview" class="mb-3">
                                <p class="mb-1 text-xs font-medium text-green-600">Preview gambar:</p>
                                <img :src="imagePreview" alt="Preview" class="h-48 w-full rounded-md border object-cover" />
                            </div>
                            <Input id="featured-image" type="file" accept="image/*" @input="handleImageChange" />
                            <p class="mt-1 text-xs text-muted-foreground">Upload gambar screenshot demo (maks 5MB)</p>
                            <p v-if="form.errors.featured_image" class="mt-1 text-xs text-red-500">{{ form.errors.featured_image }}</p>
                        </div>

                        <!-- Active & Sort -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center space-x-2">
                                <input id="is-active" type="checkbox" v-model="form.is_active" class="rounded border border-input" />
                                <Label for="is-active">Aktif</Label>
                            </div>
                            <div>
                                <Label for="sort-order">Urutan</Label>
                                <Input id="sort-order" v-model.number="form.sort_order" type="number" min="0" />
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-2 pt-4 border-t">
                            <Button type="button" variant="outline" @click="router.visit('/admin/demo-websites')" class="cursor-pointer">
                                Batal
                            </Button>
                            <Button type="submit" :disabled="form.processing" class="cursor-pointer">
                                {{ form.processing ? 'Menyimpan...' : 'Tambah Demo Website' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
