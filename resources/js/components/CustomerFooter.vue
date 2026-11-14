<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const brandingSettings = computed(() => page.props.brandingSettings || {});
const appName = computed(() => brandingSettings.value.app_name || 'WSCRM');
const appLogo = computed(() => brandingSettings.value.app_logo);
const companyEmail = computed(() => brandingSettings.value.company_email || '');
const companyPhone = computed(() => brandingSettings.value.company_phone || '');
const companyAddress = computed(() => brandingSettings.value.company_address || '');
const footerText = computed(() => brandingSettings.value.footer_text || `© ${new Date().getFullYear()} ${appName.value}. Semua hak dilindungi.`);

const layanan = [
    { href: '/hosting', label: 'Hosting & VPS' },
    { href: '/domains', label: 'Domain' },
    { href: '/demo-web', label: 'Demo Web' },
    { href: '/blog', label: 'Blog' },
];

const akun = [
    { href: '/login', label: 'Masuk' },
    { href: '/customer/register', label: 'Daftar' },
    { href: '/customer/terms', label: 'Syarat & ketentuan' },
];
</script>

<template>
    <footer class="border-t" style="border-color: var(--kpub-line);">
        <div class="kcontainer py-14">
            <div class="grid gap-10 md:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
                <div>
                    <div class="flex items-center gap-2">
                        <img v-if="appLogo" :src="appLogo" :alt="appName" class="h-8 w-8 object-contain" />
                        <span class="kdisplay text-lg">{{ appName }}</span>
                    </div>
                    <p class="mt-3 max-w-xs text-sm" style="color: var(--kpub-muted);">
                        Hosting, domain, dan pembuatan website untuk bisnis di Indonesia.
                    </p>
                </div>

                <nav aria-label="Layanan">
                    <h3 class="text-sm font-medium" style="font-family: var(--kpub-body);">Layanan</h3>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li v-for="item in layanan" :key="item.href">
                            <Link :href="item.href" class="transition-colors hover:opacity-70" style="color: var(--kpub-muted);">{{ item.label }}</Link>
                        </li>
                    </ul>
                </nav>

                <nav aria-label="Akun">
                    <h3 class="text-sm font-medium" style="font-family: var(--kpub-body);">Akun</h3>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li v-for="item in akun" :key="item.href">
                            <Link :href="item.href" class="transition-colors hover:opacity-70" style="color: var(--kpub-muted);">{{ item.label }}</Link>
                        </li>
                    </ul>
                </nav>

                <div>
                    <h3 class="text-sm font-medium" style="font-family: var(--kpub-body);">Kontak</h3>
                    <ul class="mt-4 space-y-2.5 text-sm" style="color: var(--kpub-muted);">
                        <li v-if="companyEmail">
                            <a :href="`mailto:${companyEmail}`" class="klink" style="color: var(--kpub-muted);">{{ companyEmail }}</a>
                        </li>
                        <li v-if="companyPhone">
                            <a :href="`tel:${companyPhone.replace(/[^0-9+]/g, '')}`" class="klink" style="color: var(--kpub-muted);">{{ companyPhone }}</a>
                        </li>
                        <li v-if="companyAddress" class="knum whitespace-pre-line leading-relaxed">{{ companyAddress }}</li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col gap-2 border-t pt-6 text-xs sm:flex-row sm:items-center sm:justify-between" style="border-color: var(--kpub-line); color: var(--kpub-faint);">
                <p>{{ footerText }}</p>
                <p>Dibayar lewat transfer bank, diverifikasi manual.</p>
            </div>
        </div>
    </footer>
</template>
