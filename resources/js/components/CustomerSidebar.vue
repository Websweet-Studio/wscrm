<script setup lang="ts">
import CustomerNavUser from '@/components/CustomerNavUser.vue';
import NavMain from '@/components/NavMain.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Sidebar, SidebarContent, SidebarFooter, SidebarGroup, SidebarGroupLabel, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import customer from '@/routes/customer';
import { type NavItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Headphones, Home, LayoutGrid, Settings, ShoppingCart } from 'lucide-vue-next';
import AppSidebarLogo from './AppSidebarLogo.vue';
import { computed, ref } from 'vue';

const page = usePage();
const customerBadges = page.props.customerBadges || {};
const { state } = useSidebar();
const supportOpen = ref(false);

const whatsappNumber = computed(() => {
    const raw = ((page.props as any)?.brandingSettings?.company_whatsapp as string | undefined) || '';
    const digits = raw.replace(/[^0-9]/g, '');
    return digits || null;
});

const whatsappLink = computed(() => {
    if (!whatsappNumber.value) return null;
    const text = encodeURIComponent('Halo Admin, saya butuh bantuan.');
    return `https://wa.me/${whatsappNumber.value}?text=${text}`;
});

const getCustomerUrl = (getter: () => string | undefined, fallback: string) => {
    try {
        return getter() || fallback;
    } catch {
        return fallback;
    }
};

const customerRoutes = customer as any;

const isImpersonating = () => {
    return page.props.session?.is_impersonating || false;
};

const stopImpersonation = () => {
    router.post('/customer/stop-impersonation');
};

const mainNavItems: NavItem[] = [
    {
        title: 'Beranda',
        href: '/',
        icon: Home,
    },
    {
        title: 'Dashboard',
        href: getCustomerUrl(() => customerRoutes?.dashboard?.().url, '/customer/dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'Pesanan',
        href: getCustomerUrl(() => customerRoutes?.orders?.index?.().url, '/customer/orders'),
        icon: ShoppingCart,
        badge: customerBadges.pending_orders || 0,
    },
    {
        title: 'Pengaturan',
        href: getCustomerUrl(() => customerRoutes?.settings?.index?.().url, '/customer/settings'),
        icon: Settings,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="floating" class="shadow-[rgba(0,0,0,0.06)_0px_12px_40px]">
        <SidebarHeader class="border-b border-sidebar-border/60">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="getCustomerUrl(() => customerRoutes?.dashboard?.().url, '/customer/dashboard')">
                            <AppSidebarLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>

            <!-- Impersonation Banner -->
            <div v-if="isImpersonating()" class="mx-3 my-2">
                <div class="rounded-md border border-orange-200 bg-orange-50 p-2">
                    <div class="flex items-center gap-2">
                        <div class="h-1.5 w-1.5 animate-pulse rounded-full bg-orange-500"></div>
                        <div class="flex-1">
                            <div class="text-xs font-medium text-orange-800">Mode Admin</div>
                            <div class="text-xs text-orange-700">Anda login sebagai customer</div>
                        </div>
                    </div>
                    <button
                        @click="stopImpersonation"
                        class="mt-2 w-full rounded border border-orange-300 bg-orange-100 px-2 py-1 text-xs text-orange-800 transition-colors hover:bg-orange-200"
                    >
                        Kembali ke Admin
                    </button>
                </div>
            </div>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />

            <SidebarGroup class="px-2 py-0">
                <SidebarGroupLabel>Support</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton :tooltip="'Support'" @click="supportOpen = true">
                            <Headphones class="h-4 w-4" />
                            <span>Support</span>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter class="border-t border-sidebar-border/60">
            <CustomerNavUser :minimized="state === 'collapsed'" />
        </SidebarFooter>
    </Sidebar>
    <Dialog v-model:open="supportOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Support</DialogTitle>
                <DialogDescription>
                    Jika ada kendala atau butuh bantuan, silakan hubungi WhatsApp admin.
                </DialogDescription>
            </DialogHeader>
            <div v-if="!whatsappNumber" class="rounded-md border border-border/60 bg-muted/20 p-3 text-sm text-muted-foreground">
                Nomor WhatsApp admin belum diatur.
            </div>
            <DialogFooter class="gap-2">
                <Button type="button" variant="outline" @click="supportOpen = false">Tutup</Button>
                <Button v-if="whatsappLink" asChild>
                    <a :href="whatsappLink" target="_blank" rel="noopener noreferrer">Chat WhatsApp Admin</a>
                </Button>
                <Button v-else disabled>Chat WhatsApp Admin</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
    <slot />
</template>
