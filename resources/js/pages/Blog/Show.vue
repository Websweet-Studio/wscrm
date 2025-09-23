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
    Share2,
    ArrowLeft,
    Facebook,
    Twitter,
    Linkedin,
    Link as LinkIcon
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
    post: BlogPost;
    relatedPosts: BlogPost[];
    recentPosts: BlogPost[];
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

const sharePost = (platform: string) => {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(props.post.title);

    let shareUrl = '';

    switch (platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
            break;
        case 'linkedin':
            shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
            break;
    }

    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
};

const copyLink = () => {
    navigator.clipboard.writeText(window.location.href);
    // You could add a toast notification here
};
</script>

<template>
    <Head :title="`${post.title} - Blog WebSweetStudio`" />

    <CustomerPublicLayout :title="`${post.title} - Blog WebSweetStudio`">
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

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-3">
                    <article class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        <!-- Featured Image -->
                        <div v-if="post.featured_image" class="h-64 md:h-96 overflow-hidden">
                            <img
                                :src="post.featured_image_url"
                                :alt="post.title"
                                class="w-full h-full object-cover"
                            />
                        </div>

                        <div class="p-6 md:p-8">
                            <!-- Meta Info -->
                            <div class="flex flex-wrap items-center gap-4 mb-6">
                                <Badge
                                    :style="{ backgroundColor: post.category.color + '20', color: post.category.color }"
                                    class="text-sm"
                                >
                                    {{ post.category.name }}
                                </Badge>
                                <Badge :class="getTypeColor(post.type)" class="text-sm">
                                    {{ getTypeText(post.type) }}
                                </Badge>
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    <div class="flex items-center space-x-1">
                                        <User class="h-4 w-4" />
                                        <span>{{ post.author.name }}</span>
                                    </div>
                                    <div class="flex items-center space-x-1">
                                        <Calendar class="h-4 w-4" />
                                        <span>{{ post.formatted_date || formatDate(post.created_at) }}</span>
                                    </div>
                                    <div class="flex items-center space-x-1">
                                        <Clock class="h-4 w-4" />
                                        <span>{{ post.reading_time }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Title -->
                            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
                                {{ post.title }}
                            </h1>

                            <!-- Excerpt -->
                            <p class="text-xl text-gray-600 dark:text-gray-400 mb-8 leading-relaxed">
                                {{ post.excerpt }}
                            </p>

                            <!-- Article Stats -->
                            <div class="flex items-center justify-between mb-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center space-x-6">
                                    <div class="flex items-center space-x-2">
                                        <Eye class="h-5 w-5 text-gray-400" />
                                        <span class="text-sm font-medium">{{ post.views_count }} views</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Heart class="h-5 w-5 text-gray-400" />
                                        <span class="text-sm font-medium">{{ post.likes_count }} likes</span>
                                    </div>
                                </div>

                                <!-- Share Buttons -->
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400 mr-2">Bagikan:</span>
                                    <Button @click="sharePost('facebook')" variant="outline" size="sm">
                                        <Facebook class="h-4 w-4" />
                                    </Button>
                                    <Button @click="sharePost('twitter')" variant="outline" size="sm">
                                        <Twitter class="h-4 w-4" />
                                    </Button>
                                    <Button @click="sharePost('linkedin')" variant="outline" size="sm">
                                        <Linkedin class="h-4 w-4" />
                                    </Button>
                                    <Button @click="copyLink" variant="outline" size="sm">
                                        <LinkIcon class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="prose prose-lg dark:prose-invert max-w-none">
                                <div v-html="post.content"></div>
                            </div>

                            <!-- Author Info -->
                            <div class="mt-12 p-6 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <h3 class="text-lg font-semibold mb-3">Tentang Penulis</h3>
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-lg">
                                            {{ post.author.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold">{{ post.author.name }}</h4>
                                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                                            Penulis konten di WebSweetStudio
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>

                    <!-- Related Posts -->
                    <div v-if="relatedPosts.length > 0" class="mt-12">
                        <h2 class="text-2xl font-bold mb-6">Artikel Terkait</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <Card v-for="relatedPost in relatedPosts" :key="relatedPost.id" class="overflow-hidden hover:shadow-lg transition-shadow">
                                <div v-if="relatedPost.featured_image" class="h-48 overflow-hidden">
                                    <img
                                        :src="relatedPost.featured_image_url"
                                        :alt="relatedPost.title"
                                        class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                    />
                                </div>
                                <CardContent class="p-6">
                                    <div class="flex items-center justify-between mb-3">
                                        <Badge
                                            :style="{ backgroundColor: relatedPost.category.color + '20', color: relatedPost.category.color }"
                                            class="text-xs"
                                        >
                                            {{ relatedPost.category.name }}
                                        </Badge>
                                        <Badge :class="getTypeColor(relatedPost.type)" class="text-xs">
                                            {{ getTypeText(relatedPost.type) }}
                                        </Badge>
                                    </div>
                                    <h3 class="text-lg font-semibold mb-2 line-clamp-2">{{ relatedPost.title }}</h3>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">{{ relatedPost.excerpt }}</p>
                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                                        <div class="flex items-center space-x-2">
                                            <User class="h-3 w-3" />
                                            <span>{{ relatedPost.author.name }}</span>
                                        </div>
                                        <span>{{ relatedPost.reading_time }}</span>
                                    </div>
                                    <Link :href="`/blog/${relatedPost.slug}`" class="block">
                                        <Button variant="outline" size="sm" class="w-full">Baca Selengkapnya</Button>
                                    </Link>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
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
                                    v-for="recentPost in recentPosts"
                                    :key="recentPost.id"
                                    :href="`/blog/${recentPost.slug}`"
                                    class="block group"
                                >
                                    <div class="flex space-x-3">
                                        <div v-if="recentPost.featured_image" class="flex-shrink-0">
                                            <img
                                                :src="recentPost.featured_image_url"
                                                :alt="recentPost.title"
                                                class="w-16 h-16 object-cover rounded group-hover:scale-105 transition-transform"
                                            />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-sm font-medium line-clamp-2 group-hover:text-primary transition-colors">
                                                {{ recentPost.title }}
                                            </h4>
                                            <div class="flex items-center space-x-2 mt-1 text-xs text-gray-500">
                                                <Calendar class="h-3 w-3" />
                                                <span>{{ recentPost.formatted_date || formatDate(recentPost.created_at) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Back to Blog -->
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

/* Prose styling for article content */
.prose h1 {
    font-size: 1.875rem;
    font-weight: 700;
    margin-top: 2rem;
    margin-bottom: 1rem;
}
.prose h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}
.prose h3 {
    font-size: 1.25rem;
    font-weight: 700;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}
.prose p {
    margin-bottom: 1rem;
    line-height: 1.625;
}
.prose ul {
    list-style-type: disc;
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}
.prose ol {
    list-style-type: decimal;
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}
.prose li {
    margin-bottom: 0.25rem;
}
.prose a {
    color: rgb(37 99 235);
    text-decoration: underline;
}
.prose a:hover {
    color: rgb(29 78 216);
}
.prose blockquote {
    border-left: 4px solid rgb(209 213 219);
    padding-left: 1rem;
    font-style: italic;
    margin: 1rem 0;
}
.prose code {
    background-color: rgb(243 244 246);
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}
.prose pre {
    background-color: rgb(243 244 246);
    padding: 1rem;
    border-radius: 0.25rem;
    overflow-x: auto;
    margin: 1rem 0;
}
</style>