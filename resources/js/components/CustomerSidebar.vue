<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import customer from '@/routes/customer';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, Globe, LayoutGrid, Receipt, Server, Settings, ShoppingCart } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();
const customerBadges = page.props.customerBadges || {};

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
        title: 'My Services',
        href: customer.services.index().url,
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
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
