<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Edit,
    Eye,
    Heart,
    Calendar,
    User,
    Tag,
    Star,
    Pin,
    MessageCircle,
    Clock,
    Trash2,
    Share2
} from 'lucide-vue-next';
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
    meta_data?: any;
}

interface Props {
    post: BlogPost;
}

const props = defineProps<Props>();

const showDeleteModal = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Blog', href: '/admin/blog' },
    { title: props.post.title, href: `/admin/blog/${props.post.id}` },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
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

const toggleFeatured = () => {
    router.patch(`/admin/blog/${props.post.id}/toggle-featured`, {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const togglePinned = () => {
    router.patch(`/admin/blog/${props.post.id}/toggle-pinned`, {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deletePost = () => {
    router.delete(`/admin/blog/${props.post.id}`, {
        onSuccess: () => {
            router.visit('/admin/blog');
        },
    });
};

const sharePost = () => {
    if (navigator.share) {
        navigator.share({
            title: props.post.title,
            text: props.post.excerpt,
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href);
        // Could show a toast notification here
    }
};
</script>

<template>
    <Head :title="post.title" />

    <AppLayout>
        <template #breadcrumbs>
            {{ breadcrumbs }}
        </template>

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-2">
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                            :class="getStatusColor(post.status)"
                        >
                            {{ getStatusText(post.status) }}
                        </span>
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                            :class="getTypeColor(post.type)"
                        >
                            {{ getTypeText(post.type) }}
                        </span>
                        <Star
                            v-if="post.is_featured"
                            class="h-4 w-4 text-yellow-500 fill-yellow-500"
                        />
                        <Pin
                            v-if="post.is_pinned"
                            class="h-4 w-4 text-blue-500"
                        />
                    </div>
                    <h1 class="text-2xl font-bold leading-tight">{{ post.title }}</h1>
                    <p class="text-muted-foreground mt-2">{{ post.excerpt }}</p>
                </div>
                <div class="flex space-x-2">
                    <Button @click="router.visit('/admin/blog')" variant="outline" class="flex items-center space-x-2">
                        <ArrowLeft class="h-4 w-4" />
                        <span>Kembali</span>
                    </Button>
                    <Link :href="`/admin/blog/${post.id}/edit`">
                        <Button class="flex items-center space-x-2">
                            <Edit class="h-4 w-4" />
                            <span>Edit</span>
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Featured Image -->
                    <Card v-if="post.featured_image">
                        <CardContent class="p-0">
                            <img
                                :src="post.featured_image_url"
                                :alt="post.title"
                                class="w-full h-64 object-cover rounded-t-lg"
                            />
                        </CardContent>
                    </Card>

                    <!-- Content -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Konten Artikel</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="prose prose-gray dark:prose-invert max-w-none">
                                <div v-html="post.content.replace(/\n/g, '<br>')"></div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Aksi Cepat</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Button
                                @click="toggleFeatured"
                                variant="outline"
                                class="w-full justify-start"
                                :class="post.is_featured ? 'border-yellow-500 text-yellow-600' : ''"
                            >
                                <Star class="h-4 w-4 mr-2" :class="post.is_featured ? 'fill-current' : ''" />
                                {{ post.is_featured ? 'Hapus dari Unggulan' : 'Jadikan Unggulan' }}
                            </Button>

                            <Button
                                @click="togglePinned"
                                variant="outline"
                                class="w-full justify-start"
                                :class="post.is_pinned ? 'border-blue-500 text-blue-600' : ''"
                            >
                                <Pin class="h-4 w-4 mr-2" />
                                {{ post.is_pinned ? 'Unpin Artikel' : 'Pin Artikel' }}
                            </Button>

                            <Button
                                @click="sharePost"
                                variant="outline"
                                class="w-full justify-start"
                            >
                                <Share2 class="h-4 w-4 mr-2" />
                                Bagikan
                            </Button>

                            <Button
                                @click="confirmDelete"
                                variant="outline"
                                class="w-full justify-start text-red-600 border-red-300 hover:bg-red-50"
                            >
                                <Trash2 class="h-4 w-4 mr-2" />
                                Hapus Artikel
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Article Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Informasi Artikel</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <User class="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <div class="text-sm font-medium">{{ post.author.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ post.author.email }}</div>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <Tag class="h-4 w-4 text-muted-foreground" />
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                    :style="{ backgroundColor: post.category.color + '20', color: post.category.color }"
                                >
                                    {{ post.category.name }}
                                </span>
                            </div>

                            <div class="flex items-center space-x-3">
                                <Calendar class="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <div class="text-sm">
                                        {{ post.formatted_date || formatDate(post.created_at) }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        Dibuat {{ formatDate(post.created_at) }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <Clock class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm">{{ post.reading_time }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Statistics -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Statistik</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <Eye class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Views</span>
                                </div>
                                <span class="font-medium">{{ post.views_count.toLocaleString() }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <Heart class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Likes</span>
                                </div>
                                <span class="font-medium">{{ post.likes_count.toLocaleString() }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <MessageCircle class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Komentar</span>
                                </div>
                                <span class="font-medium">
                                    {{ post.allow_comments ? 'Diizinkan' : 'Dinonaktifkan' }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SEO Info -->
                    <Card v-if="post.meta_data && Object.keys(post.meta_data).length > 0">
                        <CardHeader>
                            <CardTitle>SEO Metadata</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2 text-sm">
                                <div v-for="(value, key) in post.meta_data" :key="key" class="flex flex-col">
                                    <span class="text-muted-foreground capitalize">{{ key.replace('_', ' ') }}:</span>
                                    <span class="break-words">{{ value }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
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
                        Apakah Anda yakin ingin menghapus artikel "{{ post.title }}"?
                        Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="flex justify-end space-x-3">
                    <Button variant="outline" @click="showDeleteModal = false">
                        Batal
                    </Button>
                    <Button variant="destructive" @click="deletePost">
                        Hapus
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>