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
            return 'bg-secondary text-secondary-foreground';
        case 'announcement':
            return 'bg-muted text-muted-foreground';
        case 'news':
            return 'bg-primary/10 text-primary';
        default:
            return 'bg-muted text-muted-foreground';
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

const formatPaginationLabel = (label: string) => {
    const withoutTags = label.replace(/<[^>]*>/g, ' ').trim();
    return withoutTags
        .replace(/&laquo;/g, '«')
        .replace(/&raquo;/g, '»')
        .replace(/&nbsp;/g, ' ')
        .replace(/&amp;/g, '&')
        .replace(/\s+/g, ' ')
        .trim();
};
</script>

<template>
    <CustomerPublicLayout title="Blog">
        <div class="container mx-auto px-4 py-12 md:py-16">
            <!-- Header -->
            <div class="mb-12 text-center">
                <h1 class="mb-3 text-4xl font-medium tracking-tight md:text-5xl" style="font-family: Georgia, serif;">Blog</h1>
                <p class="text-muted-foreground">Artikel, pengumuman, dan berita terbaru.</p>
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
                                    <div class="absolute inset-0 bg-foreground/60"></div>
                                </div>
                                <div v-else class="absolute inset-0 bg-foreground"></div>

                                <div class="relative flex h-full items-end p-8">
                                    <div class="max-w-2xl py-8 text-primary-foreground">
                                        <div class="mb-4 flex items-center space-x-3">
                                            <Star class="h-5 w-5 text-primary" />
                                            <span class="font-medium text-primary">Artikel Unggulan</span>
                                            <Badge :class="getTypeColor(post.type)" class="text-xs">
                                                {{ getTypeText(post.type) }}
                                            </Badge>
                                        </div>
                                        <h2 class="mb-4 text-3xl leading-tight font-medium md:text-4xl" style="font-family: Georgia, serif;">
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
                                            <Button size="lg" variant="secondary" class="cursor-pointer">Baca Artikel</Button>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button
                            v-if="featuredPosts.length > 1"
                            @click="prevSlide"
                            class="absolute top-1/2 left-4 -translate-y-1/2 rounded-full bg-foreground/40 p-2 text-primary-foreground backdrop-blur-sm transition-all hover:bg-foreground/60"
                        >
                            <ChevronLeft class="h-6 w-6" />
                        </button>
                        <button
                            v-if="featuredPosts.length > 1"
                            @click="nextSlide"
                            class="absolute top-1/2 right-4 -translate-y-1/2 rounded-full bg-foreground/40 p-2 text-primary-foreground backdrop-blur-sm transition-all hover:bg-foreground/60"
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
                    <Pin class="h-5 w-5 text-primary" />
                    <h2 class="text-2xl font-medium" style="font-family: Georgia, serif;">Artikel Terpilih</h2>
                </div>
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <article v-for="post in pinnedPosts" :key="post.id" class="group cursor-pointer">
                        <Link :href="`/blog/${post.slug}`" class="block">
                            <div
                                class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 group-hover:-translate-y-1 hover:shadow-md"
                            >
                                <div class="relative h-48 overflow-hidden">
                                    <img
                                        :src="post.featured_image_url || `https://via.placeholder.com/400x300/C96442/FAF9F5?text=${appName}`"
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
                                        <Pin class="h-4 w-4 text-primary" />
                                    </div>
                                </div>
                                <div class="p-6">
                                    <div class="mb-4 flex items-center justify-between">
                                        <Badge :class="getTypeColor(post.type)" class="text-xs">
                                            {{ getTypeText(post.type) }}
                                        </Badge>
                                        <div class="flex items-center space-x-3 text-xs text-muted-foreground">
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
                                        class="mb-3 line-clamp-2 text-xl font-medium text-foreground transition-colors group-hover:text-primary"
                                        style="font-family: Georgia, serif;"
                                    >
                                        {{ post.title }}
                                    </h3>
                                    <p class="mb-4 line-clamp-3 leading-relaxed text-muted-foreground">
                                        {{ post.excerpt }}
                                    </p>
                                    <div class="flex items-center space-x-4 text-sm text-muted-foreground">
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
                <h2 class="mb-8 text-3xl font-medium" style="font-family: Georgia, serif;">Artikel Terbaru</h2>
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <article v-for="post in posts.data" :key="post.id" class="group cursor-pointer">
                        <Link :href="`/blog/${post.slug}`" class="block">
                            <div
                                class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 group-hover:-translate-y-1 hover:shadow-md"
                            >
                                <div class="relative h-48 overflow-hidden">
                                    <img
                                        :src="post.featured_image_url || `https://via.placeholder.com/400x300/C96442/FAF9F5?text=${appName}`"
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
                                        <div class="flex items-center space-x-3 text-xs text-muted-foreground">
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
                                        class="mb-3 line-clamp-2 text-xl font-medium text-foreground transition-colors group-hover:text-primary"
                                        style="font-family: Georgia, serif;"
                                    >
                                        {{ post.title }}
                                    </h3>
                                    <p class="mb-4 line-clamp-3 leading-relaxed text-muted-foreground">
                                        {{ post.excerpt }}
                                    </p>
                                    <div class="flex items-center space-x-4 text-sm text-muted-foreground">
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
                    <nav class="flex flex-wrap items-center justify-center gap-2">
                        <template v-for="link in posts.links" :key="`${link.label}:${link.url ?? ''}`">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    'rounded-xl px-4 py-2 text-sm font-medium transition-all',
                                    link.active
                                        ? 'bg-primary text-primary-foreground shadow-sm'
                                        : 'border border-border bg-card text-foreground hover:bg-secondary',
                                ]"
                            >
                                {{ formatPaginationLabel(link.label) }}
                            </Link>
                            <span v-else class="cursor-not-allowed rounded-xl bg-muted px-4 py-2 text-sm text-muted-foreground opacity-50">
                                {{ formatPaginationLabel(link.label) }}
                            </span>
                        </template>
                    </nav>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-16 text-center">
                <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-secondary">
                    <Search class="h-12 w-12 text-muted-foreground" />
                </div>
                <h3 class="mb-3 text-2xl font-medium text-foreground" style="font-family: Georgia, serif;">Tidak ada artikel ditemukan</h3>
                <p class="text-muted-foreground">Silakan kembali lagi nanti untuk artikel terbaru.</p>
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
