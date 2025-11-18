<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <!-- Footer -->
    <footer class="bg-gray-900 py-6 text-gray-300 sm:py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col items-center justify-between space-y-4 md:flex-row md:space-y-0">
                <div class="flex items-center space-x-2">
                    <img src="/1.png" alt="WebSweetStudio" class="h-8 w-8 object-contain" />
                    <span class="text-lg font-bold text-white sm:text-xl">WebsweetStudio.com</span>
                </div>
                <div class="flex flex-wrap justify-center gap-4 text-sm sm:gap-6">
                    <Link href="/hosting" class="transition-colors hover:text-white">Hosting</Link>
                    <Link href="/domains" class="transition-colors hover:text-white">Domain</Link>
                    <Link href="/customer/login" class="transition-colors hover:text-white">Login Pelanggan</Link>
                    <Link href="/login" class="transition-colors hover:text-white">Login Admin</Link>
                </div>
            </div>
            <div class="mt-4 border-t border-gray-700 pt-4 text-center text-xs sm:mt-6 sm:pt-6 sm:text-sm">
                <p>&copy; 2024 WebSweetStudio. Semua hak dilindungi.</p>
            </div>
        </div>
    </footer>
</template>
