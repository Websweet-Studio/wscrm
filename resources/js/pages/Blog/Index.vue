<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import CustomerPublicLayout from "@/layouts/CustomerPublicLayout.vue";
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Search,
    Calendar,
    User,
    Eye,
    Heart,
    Clock,
    Tag,
    ChevronLeft,
    ChevronRight,
    Star,
    Pin,
    TrendingUp,
    Filter
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';

interface BlogCategory {
    id: number;
    name: string;
    slug: string;
    color: string;
    icon?: string;
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
    featuredPosts: BlogPost[];
    pinnedPosts: BlogPost[];
    posts: {
        data: BlogPost[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    recentPosts: BlogPost[];
    popularPosts: BlogPost[];
    categories: BlogCategory[];
    filters?: {
        search?: string;
        category?: string;
        type?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const categoryFilter = ref(props.filters?.category || '');
const typeFilter = ref(props.filters?.type || '');
const currentSlide = ref(0);
let carouselInterval: NodeJS.Timeout | null = null;

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

const nextSlide = () => {
    if (props.featuredPosts.length > 0) {
        currentSlide.value = (currentSlide.value + 1) % props.featuredPosts.length;
    }
};

const prevSlide = () => {
    if (props.featuredPosts.length > 0) {
        currentSlide.value = currentSlide.value === 0 ? props.featuredPosts.length - 1 : currentSlide.value - 1;
    }
};

const goToSlide = (index: number) => {
    currentSlide.value = index;
};

const startCarousel = () => {
    if (props.featuredPosts.length > 1) {
        carouselInterval = setInterval(nextSlide, 5000);
    }
};

const stopCarousel = () => {
    if (carouselInterval) {
        clearInterval(carouselInterval);
        carouselInterval = null;
    }
};

const applyFilters = () => {
    const params = new URLSearchParams();

    if (search.value) params.set('search', search.value);
    if (categoryFilter.value) params.set('category', categoryFilter.value);
    if (typeFilter.value) params.set('type', typeFilter.value);

    const url = `/blog${params.toString() ? '?' + params.toString() : ''}`;
    router.get(url, {}, { preserveState: true });
};

const resetFilters = () => {
    search.value = '';
    categoryFilter.value = '';
    typeFilter.value = '';
    applyFilters();
};

onMounted(() => {
    startCarousel();
});

onUnmounted(() => {
    stopCarousel();
});
</script>

<template>
    <Head title="Blog - WSCRM" />

    <CustomerPublicLayout title="Blog - WebSweetStudio">
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">Blog</h1>
            </div>

            <!-- Featured Posts Carousel -->
            <div v-if="featuredPosts.length > 0" class="relative mb-12">
                <Card class="overflow-hidden">
                    <div class="relative h-96 md:h-[500px]">
                        <div
                            v-for="(post, index) in featuredPosts"
                            :key="post.id"
                            :class="[
                                'absolute inset-0 transition-opacity duration-500',
                                currentSlide === index ? 'opacity-100' : 'opacity-0'
                            ]"
                        >
                            <div class="relative h-full">
                                <div
                                    v-if="post.featured_image"
                                    class="absolute inset-0 bg-cover bg-center"
                                    :style="{ backgroundImage: `url(${post.featured_image_url})` }"
                                >
                                    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
                                </div>
                                <div v-else class="absolute inset-0 bg-gradient-to-br from-blue-600 to-purple-700"></div>

                                <div class="relative h-full flex items-end p-8">
                                    <div class="text-white max-w-2xl py-8">
                                        <div class="flex items-center space-x-3 mb-4">
                                            <Star class="h-5 w-5 text-yellow-400 fill-current" />
                                            <span class="text-yellow-400 font-medium">Artikel Unggulan</span>
                                            <Badge :class="getTypeColor(post.type)" class="text-xs">
                                                {{ getTypeText(post.type) }}
                                            </Badge>
                                        </div>
                                        <h2 class="text-3xl md:text-4xl font-bold mb-4 leading-tight">
                                            {{ post.title }}
                                        </h2>
                                        <div class="flex items-center space-x-6 text-sm mb-6">
                                            <div class="flex items-center space-x-2">
                                                <User class="h-4 w-4" />
                                                <span>{{ post.author.name }}</span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <Calendar class="h-4 w-4" />
                                                <span>{{ post.formatted_date || formatDate(post.created_at) }}</span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <Clock class="h-4 w-4" />
                                                <span>{{ post.reading_time }}</span>
                                            </div>
                                        </div>
                                        <Link :href="`/blog/${post.slug}`">
                                            <Button size="lg" class="bg-white text-gray-900 hover:bg-gray-100">
                                                Baca Artikel
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button
                            v-if="featuredPosts.length > 1"
                            @click="prevSlide"
                            class="absolute left-4 top-1/2 -translate-y-1/2 p-2 rounded-full bg-black bg-opacity-50 text-white hover:bg-opacity-75 transition-all"
                        >
                            <ChevronLeft class="h-6 w-6" />
                        </button>
                        <button
                            v-if="featuredPosts.length > 1"
                            @click="nextSlide"
                            class="absolute right-4 top-1/2 -translate-y-1/2 p-2 rounded-full bg-black bg-opacity-50 text-white hover:bg-opacity-75 transition-all"
                        >
                            <ChevronRight class="h-6 w-6" />
                        </button>

                        <div v-if="featuredPosts.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
                            <button
                                v-for="(post, index) in featuredPosts"
                                :key="index"
                                @click="goToSlide(index)"
                                :class="[
                                    'w-3 h-3 rounded-full transition-all',
                                    currentSlide === index
                                        ? 'bg-white'
                                        : 'bg-white bg-opacity-50 hover:bg-opacity-75'
                                ]"
                            />
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Pinned Posts -->
            <div v-if="pinnedPosts.length > 0" class="mb-12">
                <div class="flex items-center space-x-2 mb-6">
                    <Pin class="h-5 w-5 text-blue-600" />
                    <h2 class="text-2xl font-bold">Artikel Terpilih</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <article v-for="post in pinnedPosts" :key="post.id" class="group cursor-pointer">
                        <Link :href="`/blog/${post.slug}`" class="block">
                            <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 group-hover:-translate-y-2">
                                <div class="relative h-48 overflow-hidden">
                                    <img
                                        :src="post.featured_image_url || `https://via.placeholder.com/400x300/3B82F6/FFFFFF?text=${encodeURIComponent(post.category.name)}`"
                                        :alt="post.title"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    />
                                    <div class="absolute top-4 left-4">
                                        <Badge
                                            :style="{ backgroundColor: post.category.color, color: 'white' }"
                                            class="text-xs font-medium shadow-lg"
                                        >
                                            {{ post.category.name }}
                                        </Badge>
                                    </div>
                                    <div class="absolute top-4 right-4">
                                        <Pin class="h-4 w-4 text-yellow-400 fill-current" />
                                    </div>
                                </div>
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <Badge :class="getTypeColor(post.type)" class="text-xs">
                                            {{ getTypeText(post.type) }}
                                        </Badge>
                                        <div class="flex items-center space-x-3 text-xs text-gray-500">
                                            <div class="flex items-center space-x-1">
                                                <Eye class="h-3 w-3" />
                                                <span>{{ post.views_count }}</span>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <Heart class="h-3 w-3" />
                                                <span>{{ post.likes_count }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-blue-600 transition-colors line-clamp-2">
                                        {{ post.title }}
                                    </h3>
                                    <p class="text-gray-600 dark:text-gray-300 mb-4 line-clamp-3 leading-relaxed">
                                        {{ post.excerpt }}
                                    </p>
                                    <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                        <div class="flex items-center space-x-2">
                                            <User class="h-4 w-4" />
                                            <span>{{ post.author.name }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <Calendar class="h-4 w-4" />
                                            <span>{{ post.formatted_date || formatDate(post.created_at) }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <Clock class="h-4 w-4" />
                                            <span>{{ post.reading_time }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </article>
                </div>
            </div>

            <!-- Articles Grid -->
            <div v-if="posts.data.length > 0">
                <h2 class="text-3xl font-bold mb-8">Artikel Terbaru</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <article v-for="post in posts.data" :key="post.id" class="group cursor-pointer">
                        <Link :href="`/blog/${post.slug}`" class="block">
                            <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 group-hover:-translate-y-2">
                                <div class="relative h-48 overflow-hidden">
                                    <img
                                        :src="post.featured_image_url || `https://via.placeholder.com/400x300/3B82F6/FFFFFF?text=WebSweetStudio`"
                                        :alt="post.title"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    />
                                    <div class="absolute top-4 left-4">
                                        <Badge
                                            :style="{ backgroundColor: post.category.color, color: 'white' }"
                                            class="text-xs font-medium shadow-lg"
                                        >
                                            {{ post.category.name }}
                                        </Badge>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <Badge :class="getTypeColor(post.type)" class="text-xs">
                                            {{ getTypeText(post.type) }}
                                        </Badge>
                                        <div class="flex items-center space-x-3 text-xs text-gray-500">
                                            <div class="flex items-center space-x-1">
                                                <Eye class="h-3 w-3" />
                                                <span>{{ post.views_count }}</span>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <Heart class="h-3 w-3" />
                                                <span>{{ post.likes_count }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-blue-600 transition-colors line-clamp-2">
                                        {{ post.title }}
                                    </h3>
                                    <p class="text-gray-600 dark:text-gray-300 mb-4 line-clamp-3 leading-relaxed">
                                        {{ post.excerpt }}
                                    </p>
                                    <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                        <div class="flex items-center space-x-2">
                                            <User class="h-4 w-4" />
                                            <span>{{ post.author.name }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <Calendar class="h-4 w-4" />
                                            <span>{{ post.formatted_date || formatDate(post.created_at) }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <Clock class="h-4 w-4" />
                                            <span>{{ post.reading_time }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </article>
                </div>

                <!-- Modern Pagination -->
                <div v-if="posts.links && posts.links.length > 3" class="mt-12 flex justify-center">
                    <nav class="flex items-center space-x-2">
                        <template v-for="link in posts.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    'px-4 py-2 rounded-xl text-sm font-medium transition-all',
                                    link.active
                                        ? 'bg-blue-600 text-white shadow-lg'
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-700'
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                :class="[
                                    'px-4 py-2 rounded-xl text-sm cursor-not-allowed opacity-50 bg-gray-100 dark:bg-gray-800 text-gray-500'
                                ]"
                                v-html="link.label"
                            />
                        </template>
                    </nav>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
                    <Search class="h-12 w-12 text-gray-400" />
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Tidak ada artikel ditemukan</h3>
                <p class="text-gray-600 dark:text-gray-400">Silakan kembali lagi nanti untuk artikel terbaru.</p>
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