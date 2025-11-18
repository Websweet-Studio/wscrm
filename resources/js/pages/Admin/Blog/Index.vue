<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, ChevronDown, ChevronUp, Edit, Eye, Pin, Plus, Search, Star, Tag, Trash2, User } from 'lucide-vue-next';
import { ref } from 'vue';

interface BlogCategory {
    id: number;
    name: string;
    slug: string;
    color: string;
    icon?: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface BlogPost {
    id: number;
    title: string;
    slug: string;
    excerpt: string;
    content: string;
    featured_image?: string;
    featured_image_url: string;
    type: 'article' | 'announcement' | 'news';
    status: 'draft' | 'published' | 'archived';
    is_featured: boolean;
    is_pinned: boolean;
    allow_comments: boolean;
    views_count: number;
    likes_count: number;
    published_at?: string;
    formatted_date?: string;
    reading_time: string;
    created_at: string;
    category: BlogCategory;
    author: User;
}

interface Props {
    posts: {
        data: BlogPost[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    categories: BlogCategory[];
    filters?: {
        search?: string;
        category?: string;
        status?: string;
        type?: string;
        sort?: string;
        direction?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const categoryFilter = ref(props.filters?.category || '');
const statusFilter = ref(props.filters?.status || '');
const typeFilter = ref(props.filters?.type || '');
const showDeleteModal = ref(false);
const postToDelete = ref<BlogPost | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Blog', href: '/admin/blog' },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'published':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'draft':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'archived':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const getStatusText = (status: string) => {
    switch (status) {
        case 'published':
            return 'Terbit';
        case 'draft':
            return 'Draft';
        case 'archived':
            return 'Arsip';
        default:
            return status;
    }
};

const getTypeColor = (type: string) => {
    switch (type) {
        case 'article':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'announcement':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
        case 'news':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const getTypeText = (type: string) => {
    switch (type) {
        case 'article':
            return 'Artikel';
        case 'announcement':
            return 'Pengumuman';
        case 'news':
            return 'Berita';
        default:
            return type;
    }
};

const applyFilters = () => {
    const params = new URLSearchParams();

    if (search.value) params.set('search', search.value);
    if (categoryFilter.value) params.set('category', categoryFilter.value);
    if (statusFilter.value) params.set('status', statusFilter.value);
    if (typeFilter.value) params.set('type', typeFilter.value);

    const url = `/admin/blog${params.toString() ? '?' + params.toString() : ''}`;
    router.get(url, {}, { preserveState: true });
};

const resetFilters = () => {
    search.value = '';
    categoryFilter.value = '';
    statusFilter.value = '';
    typeFilter.value = '';
    applyFilters();
};

const sortBy = (field: string) => {
    const params = new URLSearchParams();

    // Preserve existing filters
    if (search.value) params.set('search', search.value);
    if (categoryFilter.value) params.set('category', categoryFilter.value);
    if (statusFilter.value) params.set('status', statusFilter.value);
    if (typeFilter.value) params.set('type', typeFilter.value);

    // Handle sorting
    let direction = 'desc';
    if (props.filters?.sort === field && props.filters?.direction === 'desc') {
        direction = 'asc';
    }

    params.set('sort', field);
    params.set('direction', direction);

    const url = `/admin/blog${params.toString() ? '?' + params.toString() : ''}`;
    router.get(url, {}, { preserveState: true });
};

const getSortIcon = (field: string) => {
    if (props.filters?.sort !== field) return null;
    return props.filters?.direction === 'asc' ? ChevronUp : ChevronDown;
};

const toggleFeatured = (post: BlogPost) => {
    router.patch(
        `/admin/blog/${post.id}/toggle-featured`,
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const togglePinned = (post: BlogPost) => {
    router.patch(
        `/admin/blog/${post.id}/toggle-pinned`,
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const confirmDelete = (post: BlogPost) => {
    postToDelete.value = post;
    showDeleteModal.value = true;
};

const deletePost = () => {
    if (postToDelete.value) {
        router.delete(`/admin/blog/${postToDelete.value.id}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                postToDelete.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Blog Management" />

    <AppLayout>
        <template #breadcrumbs>
            {{ breadcrumbs }}
        </template>

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
                <div>
                    <h1 class="text-2xl font-bold">Manajemen Blog</h1>
                    <p class="text-muted-foreground">Kelola artikel, pengumuman, dan konten blog</p>
                </div>
                <div class="flex space-x-2">
                    <Link href="/admin/blog/create">
                        <Button class="flex items-center space-x-2">
                            <Plus class="h-4 w-4" />
                            <span>Buat Artikel</span>
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center space-x-2">
                        <Search class="h-4 w-4" />
                        <span>Filter & Pencarian</span>
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                        <div>
                            <Label for="search">Pencarian</Label>
                            <Input id="search" v-model="search" placeholder="Cari judul atau konten..." @input="applyFilters" />
                        </div>
                        <div>
                            <Label for="category">Kategori</Label>
                            <select
                                id="category"
                                v-model="categoryFilter"
                                @change="applyFilters"
                                class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">Semua Kategori</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <Label for="status">Status</Label>
                            <select
                                id="status"
                                v-model="statusFilter"
                                @change="applyFilters"
                                class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">Semua Status</option>
                                <option value="published">Terbit</option>
                                <option value="draft">Draft</option>
                                <option value="archived">Arsip</option>
                            </select>
                        </div>
                        <div>
                            <Label for="type">Tipe</Label>
                            <select
                                id="type"
                                v-model="typeFilter"
                                @change="applyFilters"
                                class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">Semua Tipe</option>
                                <option value="article">Artikel</option>
                                <option value="announcement">Pengumuman</option>
                                <option value="news">Berita</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <Button @click="resetFilters" variant="outline" class="w-full"> Reset Filter </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Posts Table -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Daftar Artikel</CardTitle>
                            <CardDescription> Total {{ posts.total }} artikel ditemukan </CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead>
                                <tr class="border-b">
                                    <th class="pb-3 text-left font-medium">
                                        <button @click="sortBy('title')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>Judul</span>
                                            <component :is="getSortIcon('title')" v-if="getSortIcon('title')" class="h-4 w-4" />
                                        </button>
                                    </th>
                                    <th class="pb-3 text-left font-medium">Kategori</th>
                                    <th class="pb-3 text-left font-medium">Penulis</th>
                                    <th class="pb-3 text-left font-medium">
                                        <button @click="sortBy('status')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>Status</span>
                                            <component :is="getSortIcon('status')" v-if="getSortIcon('status')" class="h-4 w-4" />
                                        </button>
                                    </th>
                                    <th class="pb-3 text-left font-medium">
                                        <button @click="sortBy('type')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>Tipe</span>
                                            <component :is="getSortIcon('type')" v-if="getSortIcon('type')" class="h-4 w-4" />
                                        </button>
                                    </th>
                                    <th class="pb-3 text-left font-medium">
                                        <button @click="sortBy('views_count')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>Views</span>
                                            <component :is="getSortIcon('views_count')" v-if="getSortIcon('views_count')" class="h-4 w-4" />
                                        </button>
                                    </th>
                                    <th class="pb-3 text-left font-medium">
                                        <button @click="sortBy('published_at')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>Tanggal</span>
                                            <component :is="getSortIcon('published_at')" v-if="getSortIcon('published_at')" class="h-4 w-4" />
                                        </button>
                                    </th>
                                    <th class="pb-3 text-center font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="post in posts.data" :key="post.id" class="border-b hover:bg-muted/50">
                                    <td class="py-3">
                                        <div class="flex items-start space-x-3">
                                            <img
                                                v-if="post.featured_image"
                                                :src="post.featured_image_url"
                                                :alt="post.title"
                                                class="h-12 w-16 rounded object-cover"
                                            />
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <h3 class="text-sm leading-tight font-medium">{{ post.title }}</h3>
                                                    <Star v-if="post.is_featured" class="h-4 w-4 fill-yellow-500 text-yellow-500" />
                                                    <Pin v-if="post.is_pinned" class="h-4 w-4 text-blue-500" />
                                                </div>
                                                <p class="mt-1 line-clamp-2 text-xs text-muted-foreground">{{ post.excerpt }}</p>
                                                <div class="mt-1 flex items-center space-x-2">
                                                    <span class="text-xs text-muted-foreground">{{ post.reading_time }}</span>
                                                    <span class="text-xs text-muted-foreground">•</span>
                                                    <span class="text-xs text-muted-foreground">{{ post.likes_count }} suka</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :style="{ backgroundColor: post.category.color + '20', color: post.category.color }"
                                        >
                                            <Tag class="mr-1 h-3 w-3" />
                                            {{ post.category.name }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <div class="flex items-center space-x-2">
                                            <User class="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <div class="text-sm font-medium">{{ post.author.name }}</div>
                                                <div class="text-xs text-muted-foreground">{{ post.author.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span
                                            :class="[
                                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                getStatusColor(post.status),
                                            ]"
                                        >
                                            {{ getStatusText(post.status) }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span
                                            :class="[
                                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                getTypeColor(post.type),
                                            ]"
                                        >
                                            {{ getTypeText(post.type) }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <div class="flex items-center space-x-1 text-sm">
                                            <Eye class="h-4 w-4 text-muted-foreground" />
                                            <span>{{ post.views_count.toLocaleString() }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="flex items-center space-x-1 text-sm text-muted-foreground">
                                            <Calendar class="h-4 w-4" />
                                            <span>{{ post.formatted_date || formatDate(post.created_at) }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="flex items-center justify-center space-x-1">
                                            <Link :href="`/admin/blog/${post.id}`">
                                                <Button variant="ghost" size="sm">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="`/admin/blog/${post.id}/edit`">
                                                <Button variant="ghost" size="sm">
                                                    <Edit class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="toggleFeatured(post)"
                                                :class="post.is_featured ? 'text-yellow-600' : ''"
                                            >
                                                <Star class="h-4 w-4" :class="post.is_featured ? 'fill-current' : ''" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="togglePinned(post)"
                                                :class="post.is_pinned ? 'text-blue-600' : ''"
                                            >
                                                <Pin class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm" @click="confirmDelete(post)" class="text-red-600 hover:text-red-700">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="posts.links && posts.links.length > 3" class="mt-6 flex justify-center">
                        <nav class="flex space-x-1">
                            <template v-for="link in posts.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="['rounded px-3 py-2 text-sm', link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted']"
                                    v-html="link.label"
                                />
                                <span v-else :class="['cursor-not-allowed rounded px-3 py-2 text-sm opacity-50']" v-html="link.label" />
                            </template>
                        </nav>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
            <!-- Modal Content -->
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold">Konfirmasi Hapus</h3>
                    <p class="text-sm text-muted-foreground">
                        Apakah Anda yakin ingin menghapus artikel "{{ postToDelete?.title }}"? Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="flex justify-end space-x-3">
                    <Button variant="outline" @click="showDeleteModal = false"> Batal </Button>
                    <Button variant="destructive" @click="deletePost"> Hapus </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
