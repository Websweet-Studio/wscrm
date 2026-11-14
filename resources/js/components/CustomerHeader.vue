<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { LayoutGrid, LogOut, Menu, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const page = usePage();
const isAdmin = page.props.auth?.user !== null; // User yang login melalui guard 'web' adalah admin
const isCustomer = page.props.auth?.customer !== null; // Customer yang login melalui guard 'customer'
const mobileMenuOpen = ref(false);

const brandingSettings = computed(() => page.props.brandingSettings || {});
const appName = computed(() => brandingSettings.value.app_name || 'WSCRM');
const appLogo = computed(() => brandingSettings.value.app_logo);
const appLogoDark = computed(() => brandingSettings.value.app_logo_dark || brandingSettings.value.app_logo);

const handleLogout = () => {
    if (isAdmin) {
        router.post('/logout');
    } else if (isCustomer) {
        router.post('/customer/logout');
    }
};

const navItems = [
    { href: '/hosting', label: 'Hosting & VPS' },
    { href: '/domains', label: 'Domain' },
    { href: '/demo-web', label: 'Demo Web' },
    { href: '/blog', label: 'Blog' },
];

const dashboardHref = computed(() => (isAdmin ? '/dashboard' : '/customer/dashboard'));

const currentPath = computed(() => page.url.split('?')[0]);
const isActive = (href: string) => currentPath.value === href || currentPath.value.startsWith(`${href}/`);

// Kunci scroll latar selama panel mobile terbuka.
watch(mobileMenuOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});
</script>

<template>
    <header class="sticky top-0 z-40 border-b" style="border-color: var(--kpub-line); background-color: rgba(255, 255, 255, 0.88); backdrop-filter: blur(10px);">
        <div class="kcontainer flex h-16 items-center justify-between gap-6">
            <Link href="/" class="flex shrink-0 items-center gap-2" aria-label="Beranda">
                <img v-if="appLogo" :src="appLogo" :alt="appName" class="h-8 w-8 object-contain dark:hidden" />
                <img v-if="appLogoDark" :src="appLogoDark" :alt="appName" class="hidden h-8 w-8 object-contain dark:block" />
                <span class="kdisplay text-lg" style="letter-spacing: -0.01em;">{{ appName }}</span>
            </Link>

            <nav class="hidden items-center gap-7 md:flex" aria-label="Navigasi utama">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="text-sm transition-colors"
                    :style="isActive(item.href) ? 'color: var(--kpub-ink); font-weight: 500;' : 'color: var(--kpub-muted);'"
                    :aria-current="isActive(item.href) ? 'page' : undefined"
                    @mouseenter="($event.currentTarget as HTMLElement).style.color = 'var(--kpub-ink)'"
                    @mouseleave="($event.currentTarget as HTMLElement).style.color = isActive(item.href) ? 'var(--kpub-ink)' : 'var(--kpub-muted)'"
                >
                    {{ item.label }}
                </Link>
            </nav>

            <div class="hidden items-center gap-2 md:flex">
                <template v-if="isAdmin || isCustomer">
                    <Link :href="dashboardHref" class="kbtn kbtn-outline">
                        <LayoutGrid class="h-4 w-4" />
                        Dashboard
                    </Link>
                    <button class="kbtn kbtn-ghost" @click="handleLogout">
                        <LogOut class="h-4 w-4" />
                        Keluar
                    </button>
                </template>
                <template v-else>
                    <Link href="/login" class="kbtn kbtn-outline">Masuk</Link>
                    <Link href="/customer/register" class="kbtn kbtn-primary">Daftar</Link>
                </template>
            </div>

            <button
                class="inline-flex h-10 w-10 items-center justify-center rounded-full md:hidden"
                style="color: var(--kpub-ink);"
                :aria-expanded="mobileMenuOpen"
                :aria-label="mobileMenuOpen ? 'Tutup menu' : 'Buka menu'"
                aria-controls="kpub-mobile-menu"
                @click="mobileMenuOpen = !mobileMenuOpen"
            >
                <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
                <X v-else class="h-5 w-5" />
            </button>
        </div>

        <!-- Panel mobile: satu lembar penuh, bukan drawer bertumpuk -->
        <Teleport to="body">
            <div v-if="mobileMenuOpen" id="kpub-mobile-menu" class="kpub fixed inset-x-0 top-16 bottom-0 z-30 overflow-y-auto md:hidden" style="background-color: var(--kpub-bg);">
                <nav class="kcontainer flex min-h-full flex-col gap-1 pt-4 pb-8" aria-label="Navigasi mobile">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="border-b py-3.5 text-lg"
                        style="border-color: var(--kpub-line); font-family: var(--kpub-display);"
                        @click="mobileMenuOpen = false"
                    >
                        {{ item.label }}
                    </Link>

                    <div class="mt-6 flex flex-col gap-2">
                        <template v-if="isAdmin || isCustomer">
                            <Link :href="dashboardHref" class="kbtn kbtn-primary w-full" @click="mobileMenuOpen = false">Dashboard</Link>
                            <button class="kbtn kbtn-outline w-full" @click="handleLogout(); mobileMenuOpen = false">Keluar</button>
                        </template>
                        <template v-else>
                            <Link href="/customer/register" class="kbtn kbtn-primary w-full" @click="mobileMenuOpen = false">Daftar</Link>
                            <Link href="/login" class="kbtn kbtn-outline w-full" @click="mobileMenuOpen = false">Masuk</Link>
                        </template>
                    </div>
                </nav>
            </div>
        </Teleport>
    </header>
</template>
