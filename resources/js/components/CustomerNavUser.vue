<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import customer from '@/routes/customer';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ChevronsUpDown, LogOut, User } from 'lucide-vue-next';

interface Props {
    minimized?: boolean;
}

withDefaults(defineProps<Props>(), {
    minimized: false,
});

const page = usePage();
const user = page.props.auth.customer;

const handleLogout = () => {
    router.flushAll();
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                :class="[
                    'h-auto w-full cursor-pointer data-[state=open]:bg-sidebar-accent',
                    minimized ? 'justify-center px-2 py-2' : 'justify-start px-3 py-2',
                ]"
                :title="minimized ? user.name : ''"
            >
                <template v-if="minimized">
                    <User class="size-4" />
                </template>
                <template v-else>
                    <UserInfo :user="user" />
                    <ChevronsUpDown class="ml-auto size-4" />
                </template>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent
            class="min-w-56 rounded-lg border bg-popover shadow-lg"
            :side="minimized ? 'right' : 'bottom'"
            :align="minimized ? 'start' : 'end'"
            :side-offset="4"
        >
            <DropdownMenuLabel class="p-0 font-normal">
                <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <UserInfo :user="user" :show-email="true" />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem :as-child="true">
                <Link class="block w-full cursor-pointer" :href="customer.logout().url" @click="handleLogout" as="button">
                    <LogOut class="mr-2 size-4" />
                    Log out
                </Link>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>