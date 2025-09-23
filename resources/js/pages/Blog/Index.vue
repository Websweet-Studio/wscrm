<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
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

// Carousel functionality
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
        carouselInterval = setInterval(nextSlide, 5000); // Auto-advance every 5 seconds
    }
};

const stopCarousel = () => {
    if (carouselInterval) {
        clearInterval(carouselInterval);
        carouselInterval = null;
    }
};

// Filters
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
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">Blog WebSweetStudio</h1>
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Informasi terbaru seputar teknologi, hosting, dan tips berguna untuk mengembangkan bisnis online Anda
                </p>
            </div>

            <!-- Featured Posts Carousel -->
            <div v-if="featuredPosts.length > 0" class="relative mb-12">
                <Card class="overflow-hidden">
                    <div class="relative h-96 md:h-[500px]">
                        <!-- Carousel slides -->
                        <div
                            v-for="(post, index) in featuredPosts"
                            :key="post.id"
                            :class="[
                                'absolute inset-0 transition-opacity duration-500',
                                currentSlide === index ? 'opacity-100' : 'opacity-0'
                            ]"
                        >
                            <div class="relative h-full">
                                <!-- Background image -->
                                <div
                                    v-if="post.featured_image"
                                    class="absolute inset-0 bg-cover bg-center"
                                    :style="{ backgroundImage: `url(${post.featured_image_url})` }"
                                >
                                    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
                                </div>
                                <div v-else class="absolute inset-0 bg-gradient-to-br from-blue-600 to-purple-700"></div>

                                <!-- Content overlay -->
                                <div class="relative h-full flex items-end p-8">
                                    <div class="text-white max-w-2xl">
                                        <div class="flex items-center space-x-3 mb-4">
                                            <Star class="h-5 w-5 text-yellow-400 fill-current" />
                                            <span class="text-yellow-400 font-medium">Artikel Unggulan</span>
                                            <Badge
                                                :class="getTypeColor(post.type)"
                                                class="text-xs"
                                            >
                                                {{ getTypeText(post.type) }}
                                            </Badge>
                                        </div>
                                        <h2 class="text-3xl md:text-4xl font-bold mb-4 leading-tight">
                                            {{ post.title }}
                                        </h2>
                                        <p class="text-lg mb-6 opacity-90">{{ post.excerpt }}</p>
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

                        <!-- Navigation arrows -->
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

                        <!-- Dots indicator -->
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card v-for="post in pinnedPosts" :key="post.id" class="overflow-hidden hover:shadow-lg transition-shadow">
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
                            <div class="flex items-center justify-between text-xs text-gray-500">
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
                            <Link :href="`/blog/${post.slug}`" class="mt-4 block">
                                <Button variant="outline" size="sm" class="w-full">Baca Selengkapnya</Button>
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-3">
                    <!-- Filters -->
                    <Card class="mb-8">
                        <CardHeader>
                            <CardTitle class="flex items-center space-x-2">
                                <Filter class="h-5 w-5" />
                                <span>Filter Artikel</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <Input
                                        v-model="search"
                                        placeholder="Cari artikel..."
                                        @input="applyFilters"
                                        class="w-full"
                                    >
                                        <template #prefix>
                                            <Search class="h-4 w-4" />
                                        </template>
                                    </Input>
                                </div>
                                <div>
                                    <select
                                        v-model="categoryFilter"
                                        @change="applyFilters"
                                        class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                    >
                                        <option value="">Semua Kategori</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }} ({{ category.published_posts_count }})
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <select
                                        v-model="typeFilter"
                                        @change="applyFilters"
                                        class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                    >
                                        <option value="">Semua Tipe</option>
                                        <option value="article">Artikel</option>
                                        <option value="announcement">Pengumuman</option>
                                        <option value="news">Berita</option>
                                    </select>
                                </div>
                                <div>
                                    <Button @click="resetFilters" variant="outline" class="w-full">
                                        Reset Filter
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Posts Grid -->
                    <div v-if="posts.data.length > 0">
                        <h2 class="text-2xl font-bold mb-6">Artikel Terbaru</h2>
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
                        <Search class="h-16 w-16 text-gray-400 mx-auto mb-4" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Tidak ada artikel ditemukan</h3>
                        <p class="text-gray-600 dark:text-gray-400">Coba gunakan kata kunci lain atau ubah filter pencarian.</p>
                        <Button @click="resetFilters" variant="outline" class="mt-4">Reset Filter</Button>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Categories -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center space-x-2">
                                <Tag class="h-5 w-5" />
                                <span>Kategori</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2">
                                <Link
                                    v-for="category in categories"
                                    :key="category.id"
                                    :href="`/blog/category/${category.slug}`"
                                    class="flex items-center justify-between p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                >
                                    <div class="flex items-center space-x-2">
                                        <div
                                            class="w-3 h-3 rounded-full"
                                            :style="{ backgroundColor: category.color }"
                                        ></div>
                                        <span class="text-sm">{{ category.name }}</span>
                                    </div>
                                    <Badge variant="secondary" class="text-xs">
                                        {{ category.published_posts_count }}
                                    </Badge>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Popular Posts -->
                    <Card v-if="popularPosts.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center space-x-2">
                                <TrendingUp class="h-5 w-5" />
                                <span>Artikel Populer</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <Link
                                    v-for="post in popularPosts"
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
                                                <Eye class="h-3 w-3" />
                                                <span>{{ post.views_count }}</span>
                                            </div>
                                        </div>
                                    </div>
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