<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link, router, usePage } from '@inertiajs/vue3';
import { LayoutGrid, LogOut, Menu, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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
</script>

<template>
    <nav class="sticky top-0 z-40" style="background-color: color-mix(in srgb, var(--kpub-bg) 88%, transparent); backdrop-filter: blur(8px); border-bottom: 1px solid var(--kpub-line);">
        <div class="mx-auto max-w-6xl px-4 py-4 sm:px-6">
            <div class="flex items-center justify-between">
                <Link href="/" class="flex items-center space-x-2 transition-opacity hover:opacity-80">
                    <img v-if="appLogo" :src="appLogo" :alt="appName" class="h-8 w-8 object-contain dark:hidden" />
                    <img v-if="appLogoDark" :src="appLogoDark" :alt="appName" class="hidden h-8 w-8 object-contain dark:block" />
                    <span class="text-lg font-semibold tracking-tight sm:text-xl" style="color: var(--kpub-ink);">{{ appName }}</span>
                </Link>

                <div class="hidden items-center gap-1 md:flex">
                    <Link href="/hosting" class="rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-black/5">Hosting &amp; VPS</Link>
                    <Link href="/domains" class="rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-black/5">Domain</Link>
                    <Link href="/demo-web" class="rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-black/5">Demo Web</Link>
                    <Link href="/blog" class="rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-black/5">Blog</Link>
                    <template v-if="isAdmin">
                        <Button size="sm" asChild class="ml-2" style="background-color: var(--kpub-primary); color: var(--kpub-primary-ink); border-radius: 8px;">
                            <Link href="/dashboard" class="flex items-center gap-2">
                                <LayoutGrid class="h-4 w-4" />
                                Dashboard Admin
                            </Link>
                        </Button>
                        <Button variant="ghost" size="sm" @click="handleLogout" class="flex items-center gap-2 text-red-600 hover:text-red-700 hover:bg-red-50">
                            <LogOut class="h-4 w-4" />
                            Logout
                        </Button>
                    </template>
                    <template v-else-if="isCustomer">
                        <Button size="sm" asChild class="ml-2" style="background-color: var(--kpub-primary); color: var(--kpub-primary-ink); border-radius: 8px;">
                            <Link href="/customer/dashboard" class="flex items-center gap-2">
                                <LayoutGrid class="h-4 w-4" />
                                Dashboard
                            </Link>
                        </Button>
                        <Button variant="ghost" size="sm" @click="handleLogout" class="flex items-center gap-2 text-red-600 hover:text-red-700 hover:bg-red-50">
                            <LogOut class="h-4 w-4" />
                            Logout
                        </Button>
                    </template>
                    <template v-else>
                        <Button variant="outline" size="sm" asChild class="ml-2" style="background-color: transparent; color: var(--kpub-ink); border: 1px solid var(--kpub-line); border-radius: 8px;">
                            <Link href="/customer/login">Masuk</Link>
                        </Button>
                        <Button size="sm" asChild style="background-color: var(--kpub-primary); color: var(--kpub-primary-ink); border-radius: 8px;">
                            <Link href="/customer/register">Daftar</Link>
                        </Button>
                    </template>
                </div>

                <Button variant="ghost" size="sm" class="md:hidden" @click="mobileMenuOpen = !mobileMenuOpen" aria-label="Buka menu" :aria-expanded="mobileMenuOpen">
                    <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
                    <X v-else class="h-5 w-5" />
                </Button>
            </div>

            <div v-if="mobileMenuOpen" class="fixed inset-0 z-50 md:hidden">
                <div class="fixed inset-0 bg-black/30" @click="mobileMenuOpen = false"></div>
                <Transition
                    enter-active-class="transition-transform duration-300 ease-out"
                    leave-active-class="transition-transform duration-200 ease-in"
                    enter-from-class="-translate-x-full"
                    enter-to-class="translate-x-0"
                    leave-from-class="translate-x-0"
                    leave-to-class="-translate-x-full"
                >
                    <div v-if="mobileMenuOpen" class="fixed inset-y-0 left-0 w-[70vw] max-w-[350px] bg-white shadow-xl">
                        <div class="flex h-full flex-col">
                            <div class="flex items-center justify-between border-b p-4" style="border-color: var(--kpub-line);">
                                <div class="flex items-center space-x-2">
                                    <img v-if="appLogo" :src="appLogo" :alt="appName" class="h-6 w-6 object-contain" />
                                    <span class="text-sm font-semibold">{{ appName }}</span>
                                </div>
                                <Button variant="ghost" size="sm" @click="mobileMenuOpen = false" aria-label="Tutup menu">
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>
                            <div class="flex-1 space-y-1 p-4">
                                <Link href="/hosting" class="block rounded-md px-3 py-2.5 text-base font-medium hover:bg-black/5" @click="mobileMenuOpen = false">Hosting &amp; VPS</Link>
                                <Link href="/domains" class="block rounded-md px-3 py-2.5 text-base font-medium hover:bg-black/5" @click="mobileMenuOpen = false">Domain</Link>
                                <Link href="/demo-web" class="block rounded-md px-3 py-2.5 text-base font-medium hover:bg-black/5" @click="mobileMenuOpen = false">Demo Web</Link>
                                <Link href="/blog" class="block rounded-md px-3 py-2.5 text-base font-medium hover:bg-black/5" @click="mobileMenuOpen = false">Blog</Link>
                                <template v-if="isAdmin">
                                    <Link href="/dashboard" class="block rounded-md px-3 py-2.5 text-base font-medium hover:bg-black/5" @click="mobileMenuOpen = false">Dashboard Admin</Link>
                                    <button class="block w-full rounded-md px-3 py-2.5 text-left text-base font-medium text-red-600 hover:bg-red-50" @click="handleLogout(); mobileMenuOpen = false;">Logout</button>
                                </template>
                                <template v-else-if="isCustomer">
                                    <Link href="/customer/dashboard" class="block rounded-md px-3 py-2.5 text-base font-medium hover:bg-black/5" @click="mobileMenuOpen = false">Dashboard</Link>
                                    <button class="block w-full rounded-md px-3 py-2.5 text-left text-base font-medium text-red-600 hover:bg-red-50" @click="handleLogout(); mobileMenuOpen = false;">Logout</button>
                                </template>
                                <template v-else>
                                    <Link href="/customer/login" class="block rounded-md px-3 py-2.5 text-base font-medium hover:bg-black/5" @click="mobileMenuOpen = false">Masuk</Link>
                                    <Link href="/customer/register" class="mt-2 block rounded-md px-3 py-2.5 text-center text-base font-semibold" style="background-color: var(--kpub-primary); color: var(--kpub-primary-ink);" @click="mobileMenuOpen = false">Daftar</Link>
                                </template>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </nav>
</template>
