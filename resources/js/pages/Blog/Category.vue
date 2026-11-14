<script setup lang="ts">
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

interface BlogCategory {
    id: number;
    name: string;
    slug: string;
    color: string;
    icon?: string;
    description?: string;
    published_posts_count: number;
}

interface Penulis {
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
    author: Penulis;
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

const formatDate = (dateString: string) =>
    new Date(dateString).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });

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

const formatPaginationLabel = (label: string) =>
    label
        .replace(/<[^>]*>/g, ' ')
        .replace(/&laquo;/g, '«')
        .replace(/&raquo;/g, '»')
        .replace(/&nbsp;/g, ' ')
        .replace(/&amp;/g, '&')
        .replace(/\s+/g, ' ')
        .trim();
</script>

<template>
    <CustomerPublicLayout :title="`${category.name} - Blog WebSweetStudio`">
        <section class="border-b" style="border-color: var(--kpub-line);">
            <div class="kcontainer pt-12 pb-10 md:pt-16">
                <Link href="/blog" class="inline-flex items-center gap-1.5 text-sm transition-colors hover:opacity-70" style="color: var(--kpub-muted);">
                    <ArrowLeft class="h-3.5 w-3.5" />
                    Semua artikel
                </Link>
                <h1 class="mt-4 text-3xl sm:text-4xl md:text-[2.75rem] md:leading-[1.1]">{{ category.name }}</h1>
                <p v-if="category.description" class="mt-3 max-w-2xl text-base" style="color: var(--kpub-muted);">{{ category.description }}</p>
                <p class="knum mt-3 text-sm" style="color: var(--kpub-muted);">{{ posts.total }} tulisan dalam kategori ini</p>
            </div>
        </section>

        <section class="kcontainer py-12 md:py-16">
            <div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_260px]">
                <div class="min-w-0">
                    <div v-if="posts.data.length" class="divide-y border-t" style="border-color: var(--kpub-line);">
                        <article v-for="post in posts.data" :key="post.id" class="border-b py-6" style="border-color: var(--kpub-line);">
                            <div class="grid gap-5 sm:grid-cols-[160px_minmax(0,1fr)] sm:items-start">
                                <Link :href="`/blog/${post.slug}`" class="order-2 block overflow-hidden rounded-[10px] sm:order-1" style="background-color: var(--kpub-surface);">
                                    <img
                                        :src="post.featured_image_url || '/1.png'"
                                        :alt="post.title"
                                        class="aspect-[16/10] w-full object-cover"
                                        loading="lazy"
                                    />
                                </Link>
                                <div class="order-1 sm:order-2">
                                    <div class="knum flex flex-wrap items-center gap-x-3 gap-y-1 text-xs" style="color: var(--kpub-muted);">
                                        <span>{{ getTypeText(post.type) }}</span>
                                        <span>{{ post.formatted_date || formatDate(post.created_at) }}</span>
                                        <span>{{ post.reading_time }}</span>
                                    </div>
                                    <h2 class="mt-2 text-xl sm:text-2xl">
                                        <Link :href="`/blog/${post.slug}`" class="transition-colors hover:opacity-70">{{ post.title }}</Link>
                                    </h2>
                                    <p class="mt-2 line-clamp-2 text-sm leading-relaxed" style="color: var(--kpub-muted);">{{ post.excerpt }}</p>
                                    <div class="knum mt-3 flex items-center gap-4 text-xs" style="color: var(--kpub-faint);">
                                        <span>{{ post.author.name }}</span>
                                        <span>{{ post.views_count }} dibaca</span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div v-else class="py-16 text-center">
                        <h2 class="text-2xl">Belum ada tulisan di kategori ini</h2>
                        <p class="mt-2 text-sm" style="color: var(--kpub-muted);">Coba lihat kategori lain atau kembali ke daftar artikel.</p>
                        <Link href="/blog" class="kbtn kbtn-outline mt-6">Semua artikel</Link>
                    </div>

                    <nav v-if="posts.links && posts.links.length > 3" class="mt-10 flex flex-wrap items-center justify-center gap-1.5" aria-label="Halaman kategori">
                        <template v-for="link in posts.links" :key="`${link.label}:${link.url ?? ''}`">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="knum inline-flex min-w-9 items-center justify-center rounded-full px-3 py-1.5 text-sm transition-colors"
                                :style="
                                    link.active
                                        ? 'background-color: var(--kpub-ink); color: #ffffff;'
                                        : 'border: 1px solid var(--kpub-line); color: var(--kpub-ink);'
                                "
                                :aria-current="link.active ? 'page' : undefined"
                            >
                                {{ formatPaginationLabel(link.label) }}
                            </Link>
                            <span v-else class="knum inline-flex min-w-9 items-center justify-center px-3 py-1.5 text-sm" style="color: var(--kpub-faint);">
                                {{ formatPaginationLabel(link.label) }}
                            </span>
                        </template>
                    </nav>
                </div>

                <aside class="space-y-8">
                    <div v-if="categories.length">
                        <h2 class="text-sm font-medium tracking-wide uppercase" style="color: var(--kpub-muted);">Kategori</h2>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <Link
                                v-for="c in categories"
                                :key="c.id"
                                :href="`/blog/category/${c.slug}`"
                                class="kbtn !px-3 !py-1.5 !text-xs"
                                :style="
                                    c.id === category.id
                                        ? 'background-color: var(--kpub-ink); color: #ffffff;'
                                        : 'border: 1px solid var(--kpub-line); color: var(--kpub-ink);'
                                "
                            >
                                {{ c.name }} <span class="knum opacity-70">{{ c.published_posts_count }}</span>
                            </Link>
                        </div>
                    </div>

                    <div v-if="recentPosts.length">
                        <h2 class="text-sm font-medium tracking-wide uppercase" style="color: var(--kpub-muted);">Terbaru</h2>
                        <div class="mt-4 border-t" style="border-color: var(--kpub-line);">
                            <Link
                                v-for="recent in recentPosts"
                                :key="recent.id"
                                :href="`/blog/${recent.slug}`"
                                class="group block border-b py-4"
                                style="border-color: var(--kpub-line);"
                            >
                                <h3 class="line-clamp-2 text-sm transition-colors group-hover:opacity-70">{{ recent.title }}</h3>
                                <div class="knum mt-1 text-xs" style="color: var(--kpub-faint);">{{ recent.formatted_date || formatDate(recent.created_at) }}</div>
                            </Link>
                        </div>
                    </div>
                </aside>
            </div>
        </section>
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
