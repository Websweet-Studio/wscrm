<script setup lang="ts">
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

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

/** Satu unggulan besar, sisanya jadi daftar ringkas di sebelahnya. */
const utama = computed(() => props.featuredPosts[0] ?? null);
const unggulanLain = computed(() => props.featuredPosts.slice(1, 4));

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
        <section class="kcontainer pt-16 pb-16 md:pt-20 md:pb-24">
            <div class="max-w-2xl">
                <h1 class="text-4xl sm:text-5xl md:text-[3.25rem] md:leading-[1.08]">Catatan dari dapur kami.</h1>
                <p class="mt-4 text-base sm:text-lg" style="color: var(--kpub-muted);">
                    Panduan teknis, pengumuman layanan, dan hal-hal yang kami pelajari sambil mengurus hosting.
                </p>
            </div>

            <!-- Unggulan: gambar besar menempel di atas, teks di bawahnya -->
            <div v-if="utama" class="mt-12 grid gap-8 lg:grid-cols-[1.15fr_0.85fr]">
                <article>
                    <Link :href="`/blog/${utama.slug}`" class="group block">
                        <div class="kcard overflow-hidden">
                            <div class="aspect-[16/9] overflow-hidden" style="background-color: var(--kpub-surface);">
                                <img
                                    :src="utama.featured_image_url || '/1.png'"
                                    :alt="utama.title"
                                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]"
                                    loading="eager"
                                />
                            </div>
                            <div class="p-6 sm:p-8">
                                <div class="knum flex flex-wrap items-center gap-x-4 gap-y-1 text-xs" style="color: var(--kpub-muted);">
                                    <span class="rounded-full px-2 py-0.5" style="background-color: var(--kpub-green-soft); color: var(--kpub-green);">
                                        {{ getTypeText(utama.type) }}
                                    </span>
                                    <span>{{ utama.category.name }}</span>
                                    <span>{{ utama.formatted_date || formatDate(utama.created_at) }}</span>
                                    <span>{{ utama.reading_time }}</span>
                                </div>
                                <h2 class="mt-3 text-2xl sm:text-3xl">{{ utama.title }}</h2>
                                <p class="mt-3 line-clamp-3 text-sm leading-relaxed" style="color: var(--kpub-muted);">{{ utama.excerpt }}</p>
                            </div>
                        </div>
                    </Link>
                </article>

                <div class="flex flex-col">
                    <h2 v-if="unggulanLain.length" class="text-sm font-medium tracking-wide uppercase" style="color: var(--kpub-muted);">Juga unggulan</h2>
                    <div v-if="unggulanLain.length" class="mt-4 divide-y border-t" style="border-color: var(--kpub-line);">
                        <Link
                            v-for="post in unggulanLain"
                            :key="post.id"
                            :href="`/blog/${post.slug}`"
                            class="group block border-b py-4"
                            style="border-color: var(--kpub-line);"
                        >
                            <div class="knum text-xs" style="color: var(--kpub-muted);">{{ post.formatted_date || formatDate(post.created_at) }} · {{ post.reading_time }}</div>
                            <h3 class="mt-1.5 text-lg transition-colors group-hover:opacity-70">{{ post.title }}</h3>
                        </Link>
                    </div>

                    <!-- Indeks kategori: angka nyata dari database -->
                    <div v-if="categories.length" class="mt-8">
                        <h2 class="text-sm font-medium tracking-wide uppercase" style="color: var(--kpub-muted);">Kategori</h2>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <Link
                                v-for="c in categories"
                                :key="c.id"
                                :href="`/blog/category/${c.slug}`"
                                class="kbtn kbtn-outline !px-3 !py-1.5 !text-xs"
                            >
                                {{ c.name }} <span class="knum" style="color: var(--kpub-muted);">{{ c.published_posts_count }}</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Artikel terpilih (disematkan) -->
            <div v-if="pinnedPosts.length" class="mt-16">
                <h2 class="text-xl sm:text-2xl">Artikel terpilih</h2>
                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="post in pinnedPosts"
                        :key="post.id"
                        :href="`/blog/${post.slug}`"
                        class="kcard group flex flex-col p-5 transition-colors"
                    >
                        <div class="knum flex items-center gap-3 text-xs" style="color: var(--kpub-muted);">
                            <span>{{ post.category.name }}</span>
                            <span>{{ post.formatted_date || formatDate(post.created_at) }}</span>
                        </div>
                        <h3 class="mt-2 line-clamp-2 text-lg transition-colors group-hover:opacity-70">{{ post.title }}</h3>
                        <p class="mt-2 line-clamp-2 flex-1 text-sm" style="color: var(--kpub-muted);">{{ post.excerpt }}</p>
                        <div class="knum mt-4 text-xs" style="color: var(--kpub-faint);">{{ post.reading_time }}</div>
                    </Link>
                </div>
            </div>

            <!-- Daftar artikel terbaru -->
            <div v-if="posts.data.length" class="mt-16">
                <div class="flex items-baseline justify-between gap-4">
                    <h2 class="text-xl sm:text-2xl">Artikel terbaru</h2>
                    <p class="knum text-sm" style="color: var(--kpub-muted);">{{ posts.total }} tulisan</p>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <article v-for="post in posts.data" :key="post.id">
                        <Link :href="`/blog/${post.slug}`" class="kcard group flex h-full flex-col overflow-hidden">
                            <div class="aspect-[16/10] overflow-hidden" style="background-color: var(--kpub-surface);">
                                <img
                                    :src="post.featured_image_url || '/1.png'"
                                    :alt="post.title"
                                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]"
                                    loading="lazy"
                                />
                            </div>
                            <div class="flex flex-1 flex-col p-5">
                                <div class="knum flex items-center gap-3 text-xs" style="color: var(--kpub-muted);">
                                    <span>{{ post.category.name }}</span>
                                    <span>{{ post.formatted_date || formatDate(post.created_at) }}</span>
                                </div>
                                <h3 class="mt-2 line-clamp-2 text-lg transition-colors group-hover:opacity-70">{{ post.title }}</h3>
                                <p class="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed" style="color: var(--kpub-muted);">{{ post.excerpt }}</p>
                                <div class="knum mt-4 flex items-center justify-between text-xs" style="color: var(--kpub-faint);">
                                    <span>{{ post.author.name }}</span>
                                    <span>{{ post.reading_time }}</span>
                                </div>
                            </div>
                        </Link>
                    </article>
                </div>

                <nav v-if="posts.links && posts.links.length > 3" class="mt-12 flex flex-wrap items-center justify-center gap-1.5" aria-label="Halaman artikel">
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

            <div v-else class="mt-16 border-t pt-16 text-center" style="border-color: var(--kpub-line);">
                <h2 class="text-2xl">Belum ada tulisan</h2>
                <p class="mt-2 text-sm" style="color: var(--kpub-muted);">Kami sedang menyiapkan artikel pertama. Mampir lagi nanti.</p>
                <Link href="/hosting" class="kbtn kbtn-outline mt-6">Lihat layanan hosting</Link>
            </div>
        </section>
    </CustomerPublicLayout>
</template>
