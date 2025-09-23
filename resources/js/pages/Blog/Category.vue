<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Calendar,
    User,
    Eye,
    Heart,
    Clock,
    Tag,
    ArrowLeft,
    TrendingUp
} from 'lucide-vue-next';

interface BlogCategory {
    id: number;
    name: string;
    slug: string;
    color: string;
    icon?: string;
    description?: string;
    published_posts_count: number;
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
    category: BlogCategory;
    posts: {
        data: BlogPost[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    recentPosts: BlogPost[];
    categories: BlogCategory[];
}

const props = defineProps<Props>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
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
</script>

<template>
    <Head :title="`${category.name} - Blog WebSweetStudio`" />

    <CustomerPublicLayout :title="`${category.name} - Blog WebSweetStudio`">
        <div class="container mx-auto px-4 py-8">
            <!-- Back Button -->
            <div class="mb-6">
                <Link href="/blog">
                    <Button variant="outline" class="flex items-center space-x-2">
                        <ArrowLeft class="h-4 w-4" />
                        <span>Kembali ke Blog</span>
                    </Button>
                </Link>
            </div>

            <!-- Category Header -->
            <div class="text-center mb-12">
                <div class="flex items-center justify-center mb-4">
                    <div
                        class="w-16 h-16 rounded-full flex items-center justify-center mr-4"
                        :style="{ backgroundColor: category.color + '20' }"
                    >
                        <Tag class="h-8 w-8" :style="{ color: category.color }" />
                    </div>
                    <div>
                        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-2">
                            {{ category.name }}
                        </h1>
                        <Badge
                            :style="{ backgroundColor: category.color + '20', color: category.color }"
                            class="text-sm"
                        >
                            {{ posts.total }} artikel
                        </Badge>
                    </div>
                </div>
                <p v-if="category.description" class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    {{ category.description }}
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-3">
                    <!-- Posts Grid -->
                    <div v-if="posts.data.length > 0">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold">Artikel dalam kategori {{ category.name }}</h2>
                            <span class="text-gray-500 text-sm">
                                Menampilkan {{ posts.data.length }} dari {{ posts.total }} artikel
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <Card v-for="post in posts.data" :key="post.id" class="overflow-hidden hover:shadow-lg transition-shadow">
                                <div v-if="post.featured_image" class="h-48 overflow-hidden">
                                    <img
                                        :src="post.featured_image_url"
                                        :alt="post.title"
                                        class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                    />
                                </div>
                                <CardContent class="p-6">
                                    <div class="flex items-center justify-between mb-3">
                                        <Badge
                                            :style="{ backgroundColor: post.category.color + '20', color: post.category.color }"
                                            class="text-xs"
                                        >
                                            {{ post.category.name }}
                                        </Badge>
                                        <Badge :class="getTypeColor(post.type)" class="text-xs">
                                            {{ getTypeText(post.type) }}
                                        </Badge>
                                    </div>
                                    <h3 class="text-lg font-semibold mb-2 line-clamp-2">{{ post.title }}</h3>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">{{ post.excerpt }}</p>
                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                                        <div class="flex items-center space-x-2">
                                            <User class="h-3 w-3" />
                                            <span>{{ post.author.name }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <Calendar class="h-3 w-3" />
                                            <span>{{ post.formatted_date || formatDate(post.created_at) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex items-center space-x-1">
                                                <Eye class="h-3 w-3" />
                                                <span>{{ post.views_count }}</span>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <Heart class="h-3 w-3" />
                                                <span>{{ post.likes_count }}</span>
                                            </div>
                                        </div>
                                        <span>{{ post.reading_time }}</span>
                                    </div>
                                    <Link :href="`/blog/${post.slug}`" class="block">
                                        <Button variant="outline" size="sm" class="w-full">Baca Selengkapnya</Button>
                                    </Link>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Pagination -->
                        <div v-if="posts.links && posts.links.length > 3" class="mt-8 flex justify-center">
                            <nav class="flex space-x-1">
                                <template v-for="link in posts.links" :key="link.label">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        :class="[
                                            'rounded px-3 py-2 text-sm',
                                            link.active
                                                ? 'bg-primary text-primary-foreground'
                                                : 'hover:bg-muted'
                                        ]"
                                        v-html="link.label"
                                    />
                                    <span
                                        v-else
                                        :class="[
                                            'rounded px-3 py-2 text-sm cursor-not-allowed opacity-50'
                                        ]"
                                        v-html="link.label"
                                    />
                                </template>
                            </nav>
                        </div>
                    </div>

                    <!-- No posts found -->
                    <div v-else class="text-center py-12">
                        <Tag class="h-16 w-16 text-gray-400 mx-auto mb-4" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Belum ada artikel</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Kategori {{ category.name }} belum memiliki artikel yang dipublikasikan.
                        </p>
                        <Link href="/blog">
                            <Button variant="outline" class="mt-4">Lihat Semua Artikel</Button>
                        </Link>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Categories -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center space-x-2">
                                <Tag class="h-5 w-5" />
                                <span>Kategori Lainnya</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2">
                                <Link
                                    v-for="cat in categories.filter(c => c.id !== category.id)"
                                    :key="cat.id"
                                    :href="`/blog/category/${cat.slug}`"
                                    class="flex items-center justify-between p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                >
                                    <div class="flex items-center space-x-2">
                                        <div
                                            class="w-3 h-3 rounded-full"
                                            :style="{ backgroundColor: cat.color }"
                                        ></div>
                                        <span class="text-sm">{{ cat.name }}</span>
                                    </div>
                                    <Badge variant="secondary" class="text-xs">
                                        {{ cat.published_posts_count }}
                                    </Badge>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recent Posts -->
                    <Card v-if="recentPosts.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center space-x-2">
                                <Clock class="h-5 w-5" />
                                <span>Artikel Terbaru</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <Link
                                    v-for="post in recentPosts"
                                    :key="post.id"
                                    :href="`/blog/${post.slug}`"
                                    class="block group"
                                >
                                    <div class="flex space-x-3">
                                        <div v-if="post.featured_image" class="flex-shrink-0">
                                            <img
                                                :src="post.featured_image_url"
                                                :alt="post.title"
                                                class="w-16 h-16 object-cover rounded group-hover:scale-105 transition-transform"
                                            />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-sm font-medium line-clamp-2 group-hover:text-primary transition-colors">
                                                {{ post.title }}
                                            </h4>
                                            <div class="flex items-center space-x-2 mt-1 text-xs text-gray-500">
                                                <Calendar class="h-3 w-3" />
                                                <span>{{ post.formatted_date || formatDate(post.created_at) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Back to All Categories -->
                    <Card>
                        <CardContent class="pt-6">
                            <Link href="/blog">
                                <Button class="w-full">
                                    Lihat Semua Artikel
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </CustomerPublicLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>