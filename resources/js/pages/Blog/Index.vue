<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Calendar, ChevronLeft, ChevronRight, Clock, Eye, Heart, Pin, Search, Star, User } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const page = usePage();
const brandingSettings = computed(() => page.props.brandingSettings || {});
const appName = computed(() => brandingSettings.value.app_name || 'WSCRM');

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

onMounted(() => {
    startCarousel();
});

onUnmounted(() => {
    stopCarousel();
});
</script>

<template>
    <CustomerPublicLayout title="Blog">
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-12 text-center">
                <h1 class="mb-4 text-4xl font-bold text-gray-900 md:text-5xl dark:text-white">Blog</h1>
            </div>

            <!-- Featured Posts Carousel -->
            <div v-if="featuredPosts.length > 0" class="relative mb-12">
                <Card class="overflow-hidden">
                    <div class="relative h-96 md:h-[500px]">
                        <div
                            v-for="(post, index) in featuredPosts"
                            :key="post.id"
                            :class="['absolute inset-0 transition-opacity duration-500', currentSlide === index ? 'opacity-100' : 'opacity-0']"
                        >
                            <div class="relative h-full">
                                <div
                                    v-if="post.featured_image"
                                    class="absolute inset-0 bg-cover bg-center"
                                    :style="{ backgroundImage: `url(${post.featured_image_url})` }"
                                >
                                    <div class="bg-opacity-50 absolute inset-0 bg-black"></div>
                                </div>
                                <div v-else class="absolute inset-0 bg-gradient-to-br from-blue-600 to-purple-700"></div>

                                <div class="relative flex h-full items-end p-8">
                                    <div class="max-w-2xl py-8 text-white">
                                        <div class="mb-4 flex items-center space-x-3">
                                            <Star class="h-5 w-5 fill-current text-yellow-400" />
                                            <span class="font-medium text-yellow-400">Artikel Unggulan</span>
                                            <Badge :class="getTypeColor(post.type)" class="text-xs">
                                                {{ getTypeText(post.type) }}
                                            </Badge>
                                        </div>
                                        <h2 class="mb-4 text-3xl leading-tight font-bold md:text-4xl">
                                            {{ post.title }}
                                        </h2>
                                        <div class="mb-6 flex items-center space-x-6 text-sm">
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
                                            <Button size="lg" class="bg-white text-gray-900 hover:bg-gray-100"> Baca Artikel </Button>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button
                            v-if="featuredPosts.length > 1"
                            @click="prevSlide"
                            class="bg-opacity-50 hover:bg-opacity-75 absolute top-1/2 left-4 -translate-y-1/2 rounded-full bg-black p-2 text-white transition-all"
                        >
                            <ChevronLeft class="h-6 w-6" />
                        </button>
                        <button
                            v-if="featuredPosts.length > 1"
                            @click="nextSlide"
                            class="bg-opacity-50 hover:bg-opacity-75 absolute top-1/2 right-4 -translate-y-1/2 rounded-full bg-black p-2 text-white transition-all"
                        >
                            <ChevronRight class="h-6 w-6" />
                        </button>

                        <div v-if="featuredPosts.length > 1" class="absolute bottom-4 left-1/2 flex -translate-x-1/2 space-x-2">
                            <button
                                v-for="(post, index) in featuredPosts"
                                :key="index"
                                @click="goToSlide(index)"
                                :class="[
                                    'h-3 w-3 rounded-full transition-all',
                                    currentSlide === index ? 'bg-white' : 'bg-opacity-50 hover:bg-opacity-75 bg-white',
                                ]"
                            />
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Pinned Posts -->
            <div v-if="pinnedPosts.length > 0" class="mb-12">
                <div class="mb-6 flex items-center space-x-2">
                    <Pin class="h-5 w-5 text-blue-600" />
                    <h2 class="text-2xl font-bold">Artikel Terpilih</h2>
                </div>
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <article v-for="post in pinnedPosts" :key="post.id" class="group cursor-pointer">
                        <Link :href="`/blog/${post.slug}`" class="block">
                            <div
                                class="overflow-hidden rounded-2xl bg-white shadow-md transition-all duration-300 group-hover:-translate-y-2 hover:shadow-2xl dark:bg-gray-800"
                            >
                                <div class="relative h-48 overflow-hidden">
                                    <img
                                        :src="post.featured_image_url || `https://via.placeholder.com/400x300/3B82F6/FFFFFF?text=${appName}`"
                                        :alt="post.title"
                                        class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
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
                                        <Pin class="h-4 w-4 fill-current text-yellow-400" />
                                    </div>
                                </div>
                                <div class="p-6">
                                    <div class="mb-4 flex items-center justify-between">
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
                                    <h3
                                        class="mb-3 line-clamp-2 text-xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white"
                                    >
                                        {{ post.title }}
                                    </h3>
                                    <p class="mb-4 line-clamp-3 leading-relaxed text-gray-600 dark:text-gray-300">
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
                <h2 class="mb-8 text-3xl font-bold">Artikel Terbaru</h2>
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <article v-for="post in posts.data" :key="post.id" class="group cursor-pointer">
                        <Link :href="`/blog/${post.slug}`" class="block">
                            <div
                                class="overflow-hidden rounded-2xl bg-white shadow-md transition-all duration-300 group-hover:-translate-y-2 hover:shadow-2xl dark:bg-gray-800"
                            >
                                <div class="relative h-48 overflow-hidden">
                                    <img
                                        :src="post.featured_image_url || `https://via.placeholder.com/400x300/3B82F6/FFFFFF?text=${appName}`"
                                        :alt="post.title"
                                        class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
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
                                    <div class="mb-4 flex items-center justify-between">
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
                                    <h3
                                        class="mb-3 line-clamp-2 text-xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white"
                                    >
                                        {{ post.title }}
                                    </h3>
                                    <p class="mb-4 line-clamp-3 leading-relaxed text-gray-600 dark:text-gray-300">
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
                                    'rounded-xl px-4 py-2 text-sm font-medium transition-all',
                                    link.active
                                        ? 'bg-blue-600 text-white shadow-lg'
                                        : 'border border-gray-200 bg-white text-gray-700 hover:bg-blue-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                :class="['cursor-not-allowed rounded-xl bg-gray-100 px-4 py-2 text-sm text-gray-500 opacity-50 dark:bg-gray-800']"
                                v-html="link.label"
                            />
                        </template>
                    </nav>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-16 text-center">
                <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                    <Search class="h-12 w-12 text-gray-400" />
                </div>
                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Tidak ada artikel ditemukan</h3>
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
