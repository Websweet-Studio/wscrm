<script setup lang="ts">
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Calendar, Check, Clock, Copy, Facebook, Linkedin, Twitter } from 'lucide-vue-next';
import { ref } from 'vue';

interface BlogCategory {
    id: number;
    name: string;
    slug: string;
    color: string;
    icon?: string;
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
    post: BlogPost;
    relatedPosts: BlogPost[];
    recentPosts: BlogPost[];
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

const sharePost = (platform: 'facebook' | 'twitter' | 'linkedin') => {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(props.post.title);
    const targets = {
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
        twitter: `https://twitter.com/intent/tweet?url=${url}&text=${title}`,
        linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${url}`,
    };
    window.open(targets[platform], '_blank', 'width=600,height=400');
};

const tautanTersalin = ref(false);

/** Clipboard API butuh konteks aman; tombol tetap memberi umpan balik walau gagal. */
const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(window.location.href);
        tautanTersalin.value = true;
        setTimeout(() => (tautanTersalin.value = false), 1800);
    } catch {
        tautanTersalin.value = false;
    }
};
</script>

<template>
    <CustomerPublicLayout :title="`${post.title} - Blog WebSweetStudio`">
        <article class="kcontainer pt-12 pb-16 md:pt-16 md:pb-24">
            <nav class="text-sm" style="color: var(--kpub-muted);" aria-label="Remah roti">
                <Link href="/blog" class="transition-colors hover:opacity-70">Blog</Link>
                <span aria-hidden="true"> / </span>
                <span>{{ post.category.name }}</span>
            </nav>

            <header class="mt-6 max-w-3xl">
                <div class="knum flex flex-wrap items-center gap-x-4 gap-y-1 text-xs" style="color: var(--kpub-muted);">
                    <span class="rounded-full px-2 py-0.5" style="background-color: var(--kpub-green-soft); color: var(--kpub-green);">{{ getTypeText(post.type) }}</span>
                    <span>{{ post.author.name }}</span>
                    <span>{{ post.formatted_date || formatDate(post.created_at) }}</span>
                    <span>{{ post.reading_time }}</span>
                </div>
                <h1 class="mt-4 text-3xl sm:text-4xl md:text-[2.75rem] md:leading-[1.12]">{{ post.title }}</h1>
                <p v-if="post.excerpt" class="mt-4 text-lg" style="color: var(--kpub-muted);">{{ post.excerpt }}</p>
            </header>
        </article>

        <div class="kcontainer pb-16 md:pb-24">
            <div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_280px]">
                <div class="min-w-0">
                    <figure v-if="post.featured_image" class="kcard overflow-hidden">
                        <img :src="post.featured_image_url" :alt="post.title" class="aspect-[16/9] w-full object-cover" />
                    </figure>

                    <!-- Isi artikel disimpan sebagai HTML dari editor admin -->
                    <div class="kprose mt-10" v-html="post.content" />

                    <div class="mt-10 flex flex-wrap items-center justify-between gap-4 border-t pt-6" style="border-color: var(--kpub-line);">
                        <div class="knum text-xs" style="color: var(--kpub-muted);">
                            {{ post.views_count }} dibaca · {{ post.likes_count }} suka
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs" style="color: var(--kpub-muted);">Bagikan</span>
                            <button type="button" class="kbtn kbtn-outline !px-3 !py-2" aria-label="Bagikan ke Facebook" @click="sharePost('facebook')">
                                <Facebook class="h-4 w-4" />
                            </button>
                            <button type="button" class="kbtn kbtn-outline !px-3 !py-2" aria-label="Bagikan ke X" @click="sharePost('twitter')">
                                <Twitter class="h-4 w-4" />
                            </button>
                            <button type="button" class="kbtn kbtn-outline !px-3 !py-2" aria-label="Bagikan ke LinkedIn" @click="sharePost('linkedin')">
                                <Linkedin class="h-4 w-4" />
                            </button>
                            <button type="button" class="kbtn kbtn-outline !px-3 !py-2" aria-label="Salin tautan" @click="copyLink">
                                <Check v-if="tautanTersalin" class="h-4 w-4" style="color: var(--kpub-green);" />
                                <Copy v-else class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div v-if="relatedPosts.length" class="mt-14">
                        <h2 class="text-xl sm:text-2xl">Bacaan terkait</h2>
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <Link
                                v-for="related in relatedPosts"
                                :key="related.id"
                                :href="`/blog/${related.slug}`"
                                class="kcard group flex flex-col p-5 transition-colors"
                            >
                                <div class="knum text-xs" style="color: var(--kpub-muted);">{{ related.category.name }} · {{ related.reading_time }}</div>
                                <h3 class="mt-2 line-clamp-2 text-lg transition-colors group-hover:opacity-70">{{ related.title }}</h3>
                                <p class="mt-2 line-clamp-2 text-sm" style="color: var(--kpub-muted);">{{ related.excerpt }}</p>
                            </Link>
                        </div>
                    </div>
                </div>

                <aside class="space-y-8">
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
                                <div class="knum mt-1.5 flex items-center gap-1.5 text-xs" style="color: var(--kpub-faint);">
                                    <Calendar class="h-3 w-3" />
                                    <span>{{ recent.formatted_date || formatDate(recent.created_at) }}</span>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <div class="kcard p-5">
                        <h2 class="text-base">Butuh hosting untuk artikel ini?</h2>
                        <p class="mt-2 text-sm" style="color: var(--kpub-muted);">Pindahkan website Anda ke server kami, atau mulai dari paket termurah.</p>
                        <Link href="/hosting" class="kbtn kbtn-primary mt-4 w-full">Lihat paket hosting</Link>
                    </div>

                    <Link href="/blog" class="kbtn kbtn-outline w-full">
                        <Clock class="h-4 w-4" />
                        Semua artikel
                    </Link>
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

/* Isi artikel datang sebagai HTML mentah; tipografi disamakan dengan permukaan publik. */
.kprose {
    color: var(--kpub-ink);
    font-size: 1rem;
    line-height: 1.75;
    max-width: 68ch;
}

.kprose :deep(h1),
.kprose :deep(h2),
.kprose :deep(h3),
.kprose :deep(h4) {
    font-family: var(--kpub-display);
    font-weight: 500;
    letter-spacing: -0.02em;
    line-height: 1.25;
    margin-top: 2rem;
    margin-bottom: 0.75rem;
}

.kprose :deep(h1) {
    font-size: 1.75rem;
}
.kprose :deep(h2) {
    font-size: 1.375rem;
}
.kprose :deep(h3) {
    font-size: 1.125rem;
}

.kprose :deep(p) {
    margin-bottom: 1rem;
}

.kprose :deep(a) {
    color: var(--kpub-green);
    text-decoration: underline;
    text-underline-offset: 3px;
}

.kprose :deep(ul),
.kprose :deep(ol) {
    margin: 0 0 1rem 1.25rem;
}

.kprose :deep(ul) {
    list-style: disc;
}
.kprose :deep(ol) {
    list-style: decimal;
}

.kprose :deep(li) {
    margin-bottom: 0.375rem;
}

.kprose :deep(blockquote) {
    border-left: 2px solid var(--kpub-line-strong);
    padding-left: 1rem;
    margin: 1.5rem 0;
    color: var(--kpub-muted);
}

.kprose :deep(img) {
    border-radius: var(--kpub-radius);
    margin: 1.5rem 0;
    max-width: 100%;
    height: auto;
}

.kprose :deep(code) {
    background-color: var(--kpub-surface);
    border: 1px solid var(--kpub-line);
    border-radius: 4px;
    padding: 0.1rem 0.3rem;
    font-size: 0.875em;
}

.kprose :deep(pre) {
    background-color: var(--kpub-surface);
    border: 1px solid var(--kpub-line);
    border-radius: var(--kpub-radius);
    padding: 1rem;
    overflow-x: auto;
    margin: 1.5rem 0;
}

.kprose :deep(pre code) {
    background: none;
    border: 0;
    padding: 0;
}

.kprose :deep(table) {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
    font-size: 0.9375rem;
}

.kprose :deep(th),
.kprose :deep(td) {
    border-bottom: 1px solid var(--kpub-line);
    padding: 0.5rem 0.75rem;
    text-align: left;
}

.kprose :deep(hr) {
    border: 0;
    border-top: 1px solid var(--kpub-line);
    margin: 2rem 0;
}
</style>
