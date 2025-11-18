<script setup lang="ts">
import CustomerNavUser from '@/components/CustomerNavUser.vue';
import NavMain from '@/components/NavMain.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import customer from '@/routes/customer';
import { type NavItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, Globe, Home, LayoutGrid, Receipt, Server, Settings, ShoppingCart } from 'lucide-vue-next';
import AppSidebarLogo from './AppSidebarLogo.vue';

const page = usePage();
const customerBadges = page.props.customerBadges || {};

const isImpersonating = () => {
    return page.props.session?.is_impersonating || false;
};

const stopImpersonation = () => {
    router.post('/customer/stop-impersonation');
};

const mainNavItems: NavItem[] = [
    {
        title: 'Halaman Depan',
        href: '/',
        icon: Home,
    },
    {
        title: 'Dashboard',
        href: customer.dashboard().url,
        icon: LayoutGrid,
    },
    {
        title: 'Hosting',
        href: customer.hosting.index().url,
        icon: Server,
    },
    {
        title: 'Domain',
        href: customer.domains.index().url,
        icon: Globe,
    },
    {
        title: 'My Orders',
        href: customer.orders.index().url,
        icon: ShoppingCart,
        badge: customerBadges.pending_orders || 0,
    },
    {
        title: 'Invoices',
        href: customer.invoices.index().url,
        icon: Receipt,
        badge: customerBadges.unpaid_invoices || 0,
    },
    {
        title: 'Settings',
        href: customer.settings.index().url,
        icon: Settings,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="customer.dashboard().url">
                            <AppSidebarLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>

            <!-- Impersonation Banner -->
            <div v-if="isImpersonating()" class="mx-3 my-2">
                <div class="rounded-md border border-orange-200 bg-orange-100 p-2 dark:border-orange-800 dark:bg-orange-900">
                    <div class="flex items-center gap-2">
                        <div class="h-1.5 w-1.5 animate-pulse rounded-full bg-orange-500"></div>
                        <div class="flex-1">
                            <div class="text-xs font-medium text-orange-800 dark:text-orange-200">Mode Admin</div>
                            <div class="text-xs text-orange-700 dark:text-orange-300">Anda login sebagai customer</div>
                        </div>
                    </div>
                    <button
                        @click="stopImpersonation"
                        class="mt-2 w-full rounded border border-orange-300 bg-orange-200 px-2 py-1 text-xs text-orange-800 transition-colors hover:bg-orange-300 dark:border-orange-700 dark:bg-orange-800 dark:text-orange-200 dark:hover:bg-orange-700"
                    >
                        Kembali ke Admin
                    </button>
                </div>
            </div>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <CustomerNavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
