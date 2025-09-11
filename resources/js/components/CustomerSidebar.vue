<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import CustomerNavUser from '@/components/CustomerNavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import customer from '@/routes/customer';
import { type NavItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, Globe, LayoutGrid, Receipt, Server, Settings, ShoppingCart } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

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
        title: 'Dashboard',
        href: customer.dashboard().url,
        icon: LayoutGrid,
    },
    {
        title: 'Hosting Plans',
        href: customer.hosting.index().url,
        icon: Server,
    },
    {
        title: 'Domains',
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
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            
            <!-- Impersonation Banner -->
            <div v-if="isImpersonating()" class="mx-3 my-2">
                <div class="bg-orange-100 border border-orange-200 rounded-md p-2 dark:bg-orange-900 dark:border-orange-800">
                    <div class="flex items-center gap-2">
                        <div class="h-1.5 w-1.5 bg-orange-500 rounded-full animate-pulse"></div>
                        <div class="flex-1">
                            <div class="text-xs font-medium text-orange-800 dark:text-orange-200">
                                Mode Admin
                            </div>
                            <div class="text-xs text-orange-700 dark:text-orange-300">
                                Anda login sebagai customer
                            </div>
                        </div>
                    </div>
                    <button
                        @click="stopImpersonation"
                        class="mt-2 w-full text-xs bg-orange-200 hover:bg-orange-300 text-orange-800 px-2 py-1 rounded border border-orange-300 dark:bg-orange-800 dark:hover:bg-orange-700 dark:text-orange-200 dark:border-orange-700 transition-colors"
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
