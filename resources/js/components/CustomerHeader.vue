<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Menu, X } from 'lucide-vue-next';
import { ref } from 'vue';

const page = usePage();
const isAdmin = page.props.auth?.user !== null; // User yang login melalui guard 'web' adalah admin
const isCustomer = page.props.auth?.customer !== null; // Customer yang login melalui guard 'customer'
const mobileMenuOpen = ref(false);
</script>

<template>
    <!-- Navigation -->
    <nav class="container mx-auto px-4 py-4 sm:px-6">
        <div class="flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center space-x-2">
                <img src="/1.png" alt="WebSweetStudio" class="h-8 w-8 object-contain" />
                <span class="text-lg font-bold text-gray-900 sm:text-xl dark:text-white">WebsweetStudio.com</span>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden items-center space-x-2 md:flex">
                <Button variant="ghost" size="sm" asChild>
                    <Link href="/hosting">Hosting</Link>
                </Button>
                <Button variant="ghost" size="sm" asChild>
                    <Link href="/domains">Domain</Link>
                </Button>
                <template v-if="isAdmin">
                    <Button variant="outline" size="sm" asChild>
                        <Link href="/dashboard" class="flex items-center gap-2">
                            <LayoutGrid class="h-4 w-4" />
                            Dashboard Admin
                        </Link>
                    </Button>
                </template>
                <template v-else-if="isCustomer">
                    <Button variant="outline" size="sm" asChild>
                        <Link href="/customer/dashboard" class="flex items-center gap-2">
                            <LayoutGrid class="h-4 w-4" />
                            Dashboard
                        </Link>
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
            <Button
                variant="ghost"
                size="sm"
                class="md:hidden"
                @click="mobileMenuOpen = !mobileMenuOpen"
            >
                <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
                <X v-else class="h-5 w-5" />
            </Button>
        </div>

        <!-- Mobile Navigation -->
        <div
            v-if="mobileMenuOpen"
            class="mt-4 space-y-2 border-t pt-4 md:hidden"
        >
            <Button variant="ghost" class="w-full justify-start" asChild>
                <Link href="/hosting">Hosting</Link>
            </Button>
            <Button variant="ghost" class="w-full justify-start" asChild>
                <Link href="/domains">Domain</Link>
            </Button>
            <template v-if="isAdmin">
                <Button variant="outline" class="w-full justify-start" asChild>
                    <Link href="/dashboard" class="flex items-center gap-2">
                        <LayoutGrid class="h-4 w-4" />
                        Dashboard Admin
                    </Link>
                </Button>
            </template>
            <template v-else-if="isCustomer">
                <Button variant="outline" class="w-full justify-start" asChild>
                    <Link href="/customer/dashboard" class="flex items-center gap-2">
                        <LayoutGrid class="h-4 w-4" />
                        Dashboard
                    </Link>
                </Button>
            </template>
            <template v-else>
                <Button variant="outline" class="w-full justify-start" asChild>
                    <Link href="/customer/login">Masuk</Link>
                </Button>
                <Button class="w-full justify-start" asChild>
                    <Link href="/customer/register">Daftar</Link>
                </Button>
            </template>
        </div>
    </nav>
</template>