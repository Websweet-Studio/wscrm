<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link, router, usePage } from '@inertiajs/vue3';
import { LayoutGrid, LogOut, Menu, X } from 'lucide-vue-next';
import { ref } from 'vue';

const page = usePage();
const isAdmin = page.props.auth?.user !== null; // User yang login melalui guard 'web' adalah admin
const isCustomer = page.props.auth?.customer !== null; // Customer yang login melalui guard 'customer'
const mobileMenuOpen = ref(false);

const handleLogout = () => {
    if (isAdmin) {
        router.post('/logout');
    } else if (isCustomer) {
        router.post('/customer/logout');
    }
};
</script>

<template>
    <!-- Navigation -->
    <nav class="sticky top-0 z-40 bg-white shadow-sm dark:bg-gray-900 dark:shadow-gray-800/20">
        <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:py-8">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <Link href="/" class="flex items-center space-x-2 transition-opacity hover:opacity-80">
                    <img src="/1.png" alt="WebSweetStudio" class="h-8 w-8 object-contain" />
                    <span class="text-lg font-bold text-gray-900 sm:text-xl dark:text-white">WebsweetStudio.com</span>
                </Link>

                <!-- Desktop Navigation -->
                <div class="hidden items-center space-x-2 md:flex">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href="/hosting">Hosting</Link>
                    </Button>
                    <Button variant="ghost" size="sm" asChild>
                        <Link href="/domains">Domain</Link>
                    </Button>
                    <Button variant="ghost" size="sm" asChild>
                        <Link href="/blog">Blog</Link>
                    </Button>
                    <template v-if="isAdmin">
                        <Button variant="outline" size="sm" asChild>
                            <Link href="/dashboard" class="flex items-center gap-2">
                                <LayoutGrid class="h-4 w-4" />
                                Dashboard Admin
                            </Link>
                        </Button>
                        <Button variant="ghost" size="sm" @click="handleLogout" class="flex items-center gap-2 text-red-600 hover:text-red-700">
                            <LogOut class="h-4 w-4" />
                            Logout
                        </Button>
                    </template>
                    <template v-else-if="isCustomer">
                        <Button variant="outline" size="sm" asChild>
                            <Link href="/customer/dashboard" class="flex items-center gap-2">
                                <LayoutGrid class="h-4 w-4" />
                                Dashboard
                            </Link>
                        </Button>
                        <Button variant="ghost" size="sm" @click="handleLogout" class="flex items-center gap-2 text-red-600 hover:text-red-700">
                            <LogOut class="h-4 w-4" />
                            Logout
                        </Button>
                    </template>
                    <template v-else>
                        <Button variant="outline" size="sm" asChild>
                            <Link href="/customer/login">Masuk</Link>
                        </Button>
                        <Button size="sm" asChild>
                            <Link href="/customer/register">Daftar</Link>
                        </Button>
                    </template>
                </div>

                <!-- Mobile Menu Button -->
                <Button variant="ghost" size="sm" class="md:hidden" @click="mobileMenuOpen = !mobileMenuOpen">
                    <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
                    <X v-else class="h-5 w-5" />
                </Button>
            </div>

            <!-- Mobile Navigation Overlay -->
            <div v-if="mobileMenuOpen" class="fixed inset-0 z-50 md:hidden">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/30 backdrop-blur-md" @click="mobileMenuOpen = false"></div>

                <!-- Menu Content -->
                <Transition
                    enter-active-class="transition-transform duration-500 ease-out"
                    leave-active-class="transition-transform duration-400 ease-in"
                    enter-from-class="-translate-x-full"
                    enter-to-class="translate-x-0"
                    leave-from-class="translate-x-0"
                    leave-to-class="-translate-x-full"
                >
                    <div v-if="mobileMenuOpen" class="fixed inset-y-0 left-0 w-[70vw] max-w-[350px] bg-white shadow-xl dark:bg-gray-900">
                        <div class="flex h-full flex-col">
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b p-4">
                                <div class="flex items-center space-x-2">
                                    <img src="/1.png" alt="WebSweetStudio" class="h-6 w-6 object-contain" />
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">Menu</span>
                                </div>
                                <Button variant="ghost" size="sm" @click="mobileMenuOpen = false">
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>

                            <!-- Navigation Links -->
                            <div class="flex-1 space-y-2 p-4">
                                <Button variant="ghost" class="w-full justify-start" asChild>
                                    <Link href="/hosting" @click="mobileMenuOpen = false">Hosting</Link>
                                </Button>
                                <Button variant="ghost" class="w-full justify-start" asChild>
                                    <Link href="/domains" @click="mobileMenuOpen = false">Domain</Link>
                                </Button>
                                <Button variant="ghost" class="w-full justify-start" asChild>
                                    <Link href="/blog" @click="mobileMenuOpen = false">Blog</Link>
                                </Button>
                                <template v-if="isAdmin">
                                    <Button variant="outline" class="w-full justify-start" asChild>
                                        <Link href="/dashboard" class="flex items-center gap-2" @click="mobileMenuOpen = false">
                                            <LayoutGrid class="h-4 w-4" />
                                            Dashboard Admin
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        class="w-full justify-start text-red-600 hover:text-red-700"
                                        @click="
                                            handleLogout;
                                            mobileMenuOpen = false;
                                        "
                                    >
                                        <LogOut class="mr-2 h-4 w-4" />
                                        Logout
                                    </Button>
                                </template>
                                <template v-else-if="isCustomer">
                                    <Button variant="outline" class="w-full justify-start" asChild>
                                        <Link href="/customer/dashboard" class="flex items-center gap-2" @click="mobileMenuOpen = false">
                                            <LayoutGrid class="h-4 w-4" />
                                            Dashboard
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        class="w-full justify-start text-red-600 hover:text-red-700"
                                        @click="
                                            handleLogout;
                                            mobileMenuOpen = false;
                                        "
                                    >
                                        <LogOut class="mr-2 h-4 w-4" />
                                        Logout
                                    </Button>
                                </template>
                                <template v-else>
                                    <Button variant="outline" class="w-full justify-start" asChild>
                                        <Link href="/customer/login" @click="mobileMenuOpen = false">Masuk</Link>
                                    </Button>
                                    <Button class="w-full justify-start" asChild>
                                        <Link href="/customer/register" @click="mobileMenuOpen = false">Daftar</Link>
                                    </Button>
                                </template>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </nav>
</template>
