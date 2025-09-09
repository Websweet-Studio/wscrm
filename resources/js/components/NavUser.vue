<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { usePage } from '@inertiajs/vue3';
import { ChevronsUpDown, User } from 'lucide-vue-next';
import UserMenuContent from './UserMenuContent.vue';

interface Props {
    minimized?: boolean;
}

withDefaults(defineProps<Props>(), {
    minimized: false,
});

const page = usePage();
const user = page.props.auth.user;
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
            <UserMenuContent :user="user" />
        </DropdownMenuContent>
    </DropdownMenu>
</template>
