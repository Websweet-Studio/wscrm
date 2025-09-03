<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { usePage } from '@inertiajs/vue3';
import { ChevronsUpDown } from 'lucide-vue-next';
import UserMenuContent from './UserMenuContent.vue';

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="w-full justify-start px-3 py-2 h-auto data-[state=open]:bg-sidebar-accent">
                <UserInfo :user="user" />
                <ChevronsUpDown class="ml-auto size-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent
            class="min-w-56 rounded-lg"
            side="bottom"
            align="end"
            :side-offset="4"
        >
            <UserMenuContent :user="user" />
        </DropdownMenuContent>
    </DropdownMenu>
</template>
