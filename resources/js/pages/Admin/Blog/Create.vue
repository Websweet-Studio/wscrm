<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, Image as ImageIcon, Calendar, Tag, Settings } from 'lucide-vue-next';
import { ref } from 'vue';

interface BlogCategory {
    id: number;
    name: string;
    slug: string;
    color: string;
    icon?: string;
}

interface Props {
    categories: BlogCategory[];
}

const props = defineProps<Props>();

const form = useForm({
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    featured_image: null as File | null,
    blog_category_id: '',
    type: 'article' as 'article' | 'announcement' | 'news',
    status: 'draft' as 'draft' | 'published' | 'archived',
    is_featured: false,
    is_pinned: false,
    allow_comments: true,
    published_at: '',
    meta_data: {},
});

const featuredImagePreview = ref<string | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Blog', href: '/admin/blog' },
    { title: 'Buat Artikel', href: '/admin/blog/create' },
];

const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        form.featured_image = file;

        const reader = new FileReader();
        reader.onload = (e) => {
            featuredImagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const generateSlug = () => {
    if (form.title && !form.slug) {
        form.slug = form.title
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }
};

const submit = () => {
    form.post('/admin/blog', {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect to blog index on success
        },
    });
};

const saveAsDraft = () => {
    form.status = 'draft';
    submit();
};

const publish = () => {
    form.status = 'published';
    if (!form.published_at) {
        form.published_at = new Date().toISOString().slice(0, 16);
    }
    submit();
};
</script>

<template>
    <Head title="Buat Artikel Baru" />

    <AppLayout>
        <template #breadcrumbs>
            {{ breadcrumbs }}
        </template>

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
                <div>
                    <h1 class="text-2xl font-bold">Buat Artikel Baru</h1>
                    <p class="text-muted-foreground">
                        Tulis dan publikasikan artikel, pengumuman, atau berita
                    </p>
                </div>
                <div class="flex space-x-2">
                    <Button @click="router.visit('/admin/blog')" variant="outline" class="flex items-center space-x-2">
                        <ArrowLeft class="h-4 w-4" />
                        <span>Kembali</span>
                    </Button>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Informasi Dasar</CardTitle>
                            <CardDescription>Informasi utama artikel</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label for="title">Judul Artikel *</Label>
                                <Input
                                    id="title"
                                    v-model="form.title"
                                    @input="generateSlug"
                                    placeholder="Masukkan judul artikel..."
                                    required
                                    :class="{ 'border-red-500': form.errors.title }"
                                />
                                <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.title }}
                                </div>
                            </div>

                            <div>
                                <Label for="slug">URL Slug</Label>
                                <Input
                                    id="slug"
                                    v-model="form.slug"
                                    placeholder="artikel-slug-url"
                                    :class="{ 'border-red-500': form.errors.slug }"
                                />
                                <p class="text-sm text-muted-foreground mt-1">
                                    URL akan menjadi: /blog/{{ form.slug || 'artikel-slug-url' }}
                                </p>
                                <div v-if="form.errors.slug" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.slug }}
                                </div>
                            </div>

                            <div>
                                <Label for="excerpt">Ringkasan</Label>
                                <Textarea
                                    id="excerpt"
                                    v-model="form.excerpt"
                                    placeholder="Ringkasan singkat artikel (maksimal 500 karakter)..."
                                    rows="3"
                                    maxlength="500"
                                    :class="{ 'border-red-500': form.errors.excerpt }"
                                />
                                <p class="text-sm text-muted-foreground mt-1">
                                    {{ form.excerpt?.length || 0 }}/500 karakter
                                </p>
                                <div v-if="form.errors.excerpt" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.excerpt }}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Content -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Konten Artikel</CardTitle>
                            <CardDescription>Tulis konten lengkap artikel</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div>
                                <Label for="content">Konten *</Label>
                                <Textarea
                                    id="content"
                                    v-model="form.content"
                                    placeholder="Tulis konten artikel di sini..."
                                    rows="15"
                                    required
                                    :class="{ 'border-red-500': form.errors.content }"
                                />
                                <div v-if="form.errors.content" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.content }}
                                </div>
                                <p class="text-sm text-muted-foreground mt-1">
                                    Rich text editor akan ditambahkan pada update selanjutnya
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Featured Image -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center space-x-2">
                                <ImageIcon class="h-5 w-5" />
                                <span>Gambar Unggulan</span>
                            </CardTitle>
                            <CardDescription>Upload gambar untuk artikel</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div>
                                    <Label for="featured_image">Pilih Gambar</Label>
                                    <Input
                                        id="featured_image"
                                        type="file"
                                        accept="image/*"
                                        @change="handleImageChange"
                                        :class="{ 'border-red-500': form.errors.featured_image }"
                                    />
                                    <p class="text-sm text-muted-foreground mt-1">
                                        Format: JPEG, PNG, JPG, GIF, WebP. Maksimal 2MB.
                                    </p>
                                    <div v-if="form.errors.featured_image" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.featured_image }}
                                    </div>
                                </div>

                                <div v-if="featuredImagePreview" class="mt-4">
                                    <Label>Preview</Label>
                                    <div class="mt-2">
                                        <img
                                            :src="featuredImagePreview"
                                            alt="Preview"
                                            class="w-full max-w-md h-48 object-cover rounded-lg border"
                                        />
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Publish Settings -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center space-x-2">
                                <Settings class="h-5 w-5" />
                                <span>Pengaturan Publikasi</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label for="status">Status</Label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Terbit</option>
                                    <option value="archived">Arsip</option>
                                </select>
                            </div>

                            <div>
                                <Label for="type">Tipe Konten</Label>
                                <select
                                    id="type"
                                    v-model="form.type"
                                    class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <option value="article">Artikel</option>
                                    <option value="announcement">Pengumuman</option>
                                    <option value="news">Berita</option>
                                </select>
                            </div>

                            <div>
                                <Label for="published_at">Tanggal Publikasi</Label>
                                <Input
                                    id="published_at"
                                    v-model="form.published_at"
                                    type="datetime-local"
                                    :class="{ 'border-red-500': form.errors.published_at }"
                                />
                                <div v-if="form.errors.published_at" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.published_at }}
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <Label for="is_featured">Artikel Unggulan</Label>
                                    <Switch id="is_featured" v-model:checked="form.is_featured" />
                                </div>

                                <div class="flex items-center justify-between">
                                    <Label for="is_pinned">Pin Artikel</Label>
                                    <Switch id="is_pinned" v-model:checked="form.is_pinned" />
                                </div>

                                <div class="flex items-center justify-between">
                                    <Label for="allow_comments">Izinkan Komentar</Label>
                                    <Switch id="allow_comments" v-model:checked="form.allow_comments" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Category -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center space-x-2">
                                <Tag class="h-5 w-5" />
                                <span>Kategori</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div>
                                <Label for="blog_category_id">Pilih Kategori *</Label>
                                <select
                                    id="blog_category_id"
                                    v-model="form.blog_category_id"
                                    required
                                    :class="[
                                        'flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none',
                                        { 'border-red-500': form.errors.blog_category_id }
                                    ]"
                                >
                                    <option value="">Pilih kategori...</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.blog_category_id" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.blog_category_id }}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Action Buttons -->
                    <Card>
                        <CardContent class="pt-6">
                            <div class="space-y-3">
                                <Button
                                    @click="saveAsDraft"
                                    type="button"
                                    variant="outline"
                                    class="w-full"
                                    :disabled="form.processing"
                                >
                                    <Save class="h-4 w-4 mr-2" />
                                    Simpan Draft
                                </Button>

                                <Button
                                    @click="publish"
                                    type="button"
                                    class="w-full"
                                    :disabled="form.processing"
                                >
                                    <Calendar class="h-4 w-4 mr-2" />
                                    Publikasikan
                                </Button>
                            </div>

                            <div v-if="form.processing" class="text-center text-sm text-muted-foreground mt-3">
                                Menyimpan artikel...
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </form>
        </div>
    </AppLayout>
</template>