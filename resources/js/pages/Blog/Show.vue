<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Calendar, Check, Clock, Eye, Facebook, Link as LinkIcon, Linkedin, Twitter, User } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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

const otherPosts = computed(() => props.recentPosts.filter((item) => item.id !== props.post.id).slice(0, 4));

const copied = ref(false);

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
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

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(window.location.href);
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    } catch {
        copied.value = false;
    }
};
</script>

<template>
    <Head :title="`${post.title} - Blog WebSweetStudio`" />

    <CustomerPublicLayout :title="`${post.title} - Blog WebSweetStudio`">
        <div class="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6 lg:py-14">
            <!-- Breadcrumb -->
            <nav class="mb-8 flex items-center gap-2 text-xs tracking-wide text-muted-foreground uppercase">
                <Link href="/blog" class="transition-colors hover:text-primary">Blog</Link>
                <span class="text-border">/</span>
                <span class="text-foreground/70">{{ post.category.name }}</span>
            </nav>

            <div class="grid grid-cols-1 gap-10 lg:grid-cols-12 lg:gap-14">
                <!-- Main Content -->
                <div class="lg:col-span-8">
                    <article>
                        <!-- Featured Image -->
                        <figure v-if="post.featured_image" class="mb-8 overflow-hidden rounded-2xl ring-1 ring-border">
                            <img :src="post.featured_image_url" :alt="post.title" class="aspect-[16/9] w-full object-cover" />
                        </figure>

                        <!-- Meta -->
                        <div class="mb-4 flex flex-wrap items-center gap-x-3 gap-y-2">
                            <span
                                class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium"
                                :style="{
                                    backgroundColor: post.category.color + '12',
                                    color: post.category.color,
                                    borderColor: post.category.color + '33',
                                }"
                            >
                                {{ post.category.name }}
                            </span>
                            <Badge variant="outline" class="rounded-full border-border px-3 py-1 text-xs font-medium text-muted-foreground">
                                {{ getTypeText(post.type) }}
                            </Badge>
                        </div>

                        <!-- Title -->
                        <h1 class="mb-4 text-3xl leading-[1.15] font-medium tracking-tight text-foreground sm:text-4xl lg:text-[2.6rem]" style="font-family: Georgia, serif">
                            {{ post.title }}
                        </h1>

                        <!-- Excerpt -->
                        <p class="mb-6 max-w-[62ch] text-lg leading-relaxed text-muted-foreground">
                            {{ post.excerpt }}
                        </p>

                        <!-- Meta bar -->
                        <div class="flex flex-wrap items-center justify-between gap-4 border-y border-border py-4">
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-muted-foreground">
                                <span class="flex items-center gap-2 text-foreground/80">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-secondary text-[11px] font-semibold text-secondary-foreground">
                                        {{ post.author.name.charAt(0).toUpperCase() }}
                                    </span>
                                    {{ post.author.name }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <Calendar class="h-4 w-4" />
                                    {{ post.formatted_date || formatDate(post.created_at) }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <Clock class="h-4 w-4" />
                                    {{ post.reading_time }}
                                </span>
                                <span v-if="post.views_count" class="hidden items-center gap-1.5 sm:flex">
                                    <Eye class="h-4 w-4" />
                                    {{ post.views_count }} dilihat
                                </span>
                            </div>

                            <!-- Share -->
                            <div class="flex items-center gap-2">
                                <span class="text-xs tracking-wide text-muted-foreground uppercase">Bagikan</span>
                                <button
                                    v-for="item in [
                                        { key: 'facebook', icon: Facebook, label: 'Facebook' },
                                        { key: 'twitter', icon: Twitter, label: 'X' },
                                        { key: 'linkedin', icon: Linkedin, label: 'LinkedIn' },
                                    ]"
                                    :key="item.key"
                                    type="button"
                                    :aria-label="`Bagikan ke ${item.label}`"
                                    class="flex h-9 w-9 items-center justify-center rounded-full border border-border text-muted-foreground transition-colors hover:border-primary/40 hover:text-primary"
                                    @click="sharePost(item.key)"
                                >
                                    <component :is="item.icon" class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    aria-label="Salin tautan"
                                    class="flex h-9 w-9 items-center justify-center rounded-full border transition-colors"
                                    :class="copied ? 'border-primary/40 text-primary' : 'border-border text-muted-foreground hover:border-primary/40 hover:text-primary'"
                                    @click="copyLink"
                                >
                                    <Check v-if="copied" class="h-4 w-4" />
                                    <LinkIcon v-else class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="prose prose-lg mt-10 max-w-none dark:prose-invert">
                            <div v-html="post.content"></div>
                        </div>

                        <!-- Author -->
                        <div class="mt-12 flex items-center gap-4 rounded-2xl border border-border bg-card p-5">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary/10 text-base font-semibold text-primary">
                                {{ post.author.name.charAt(0).toUpperCase() }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs tracking-wide text-muted-foreground uppercase">Ditulis oleh</p>
                                <h3 class="font-medium text-foreground">{{ post.author.name }}</h3>
                                <p class="text-sm text-muted-foreground">Tim Websweet Studio</p>
                            </div>
                        </div>
                    </article>

                    <!-- Related Posts -->
                    <section v-if="relatedPosts.length > 0" class="mt-14">
                        <h2 class="mb-6 text-xl font-medium tracking-tight text-foreground">Artikel Terkait</h2>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <Link
                                v-for="relatedPost in relatedPosts"
                                :key="relatedPost.id"
                                :href="`/blog/${relatedPost.slug}`"
                                class="group flex flex-col overflow-hidden rounded-2xl border border-border bg-card transition-colors hover:border-primary/30"
                            >
                                <div v-if="relatedPost.featured_image" class="aspect-[16/9] overflow-hidden">
                                    <img
                                        :src="relatedPost.featured_image_url"
                                        :alt="relatedPost.title"
                                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
                                    />
                                </div>
                                <div class="flex flex-1 flex-col p-5">
                                    <span class="mb-2 text-xs font-medium" :style="{ color: relatedPost.category.color }">
                                        {{ relatedPost.category.name }}
                                    </span>
                                    <h3 class="mb-2 line-clamp-2 leading-snug font-medium text-foreground transition-colors group-hover:text-primary">
                                        {{ relatedPost.title }}
                                    </h3>
                                    <p class="mb-4 line-clamp-2 text-sm leading-relaxed text-muted-foreground">{{ relatedPost.excerpt }}</p>
                                    <span class="mt-auto text-xs text-muted-foreground">
                                        {{ relatedPost.formatted_date || formatDate(relatedPost.created_at) }} · {{ relatedPost.reading_time }}
                                    </span>
                                </div>
                            </Link>
                        </div>
                    </section>
                </div>

                <!-- Sidebar -->
                <aside class="space-y-6 lg:col-span-4 lg:sticky lg:top-24 lg:self-start">
                    <!-- Recent Posts -->
                    <div v-if="otherPosts.length > 0" class="rounded-2xl border border-border bg-card p-6">
                        <h2 class="mb-5 text-sm font-medium tracking-wide text-foreground uppercase">Artikel Lainnya</h2>
                        <div class="space-y-5">
                            <Link v-for="recentPost in otherPosts" :key="recentPost.id" :href="`/blog/${recentPost.slug}`" class="group flex gap-3">
                                <div v-if="recentPost.featured_image" class="h-14 w-14 shrink-0 overflow-hidden rounded-xl">
                                    <img
                                        :src="recentPost.featured_image_url"
                                        :alt="recentPost.title"
                                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    />
                                </div>
                                <div class="min-w-0">
                                    <h3 class="line-clamp-2 text-sm leading-snug font-medium text-foreground transition-colors group-hover:text-primary">
                                        {{ recentPost.title }}
                                    </h3>
                                    <p class="mt-1 text-xs text-muted-foreground">{{ recentPost.formatted_date || formatDate(recentPost.created_at) }}</p>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <!-- CTA -->
                    <div class="rounded-2xl border border-border bg-card p-6">
                        <h2 class="mb-2 text-base font-medium text-foreground">Cek harga domain terkini</h2>
                        <p class="mb-5 text-sm leading-relaxed text-muted-foreground">
                            Harga perpanjangan ditampilkan sejak awal, jadi tidak ada biaya kejutan di tahun berikutnya.
                        </p>
                        <Button as-child class="w-full">
                            <Link href="/domains" class="flex items-center justify-center gap-2">
                                Lihat daftar harga
                                <ArrowRight class="h-4 w-4" />
                            </Link>
                        </Button>
                        <Link
                            href="/blog"
                            class="mt-4 flex items-center justify-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-primary"
                        >
                            Semua artikel
                            <ArrowRight class="h-3.5 w-3.5" />
                        </Link>
                    </div>

                    <!-- Jelajahi layanan -->
                    <div class="rounded-2xl border border-border bg-card p-6">
                        <h2 class="mb-4 text-sm font-medium tracking-wide text-foreground uppercase">Jelajahi</h2>
                        <ul class="space-y-1">
                            <li v-for="item in [
                                { href: '/domains', label: 'Domain & harga' },
                                { href: '/hosting', label: 'Hosting & VPS' },
                                { href: '/demo-web', label: 'Demo website' },
                            ]" :key="item.href">
                                <Link
                                    :href="item.href"
                                    class="group flex items-center justify-between rounded-lg px-2 py-2 text-sm text-muted-foreground transition-colors hover:bg-secondary/60 hover:text-foreground"
                                >
                                    {{ item.label }}
                                    <ArrowRight class="h-3.5 w-3.5 opacity-0 transition-opacity group-hover:opacity-100" />
                                </Link>
                            </li>
                        </ul>
                    </div>
                </aside>
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
</style>
