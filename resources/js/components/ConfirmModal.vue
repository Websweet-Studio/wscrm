<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { AlertTriangle } from 'lucide-vue-next';

interface Props {
    show: boolean;
    title?: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'default' | 'destructive';
    loading?: boolean;
}

withDefaults(defineProps<Props>(), {
    title: 'Konfirmasi',
    confirmText: 'Ya',
    cancelText: 'Batal',
    variant: 'default',
    loading: false,
});

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50" @click="emit('cancel')"></div>
        <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
            <div class="mb-4 flex items-start gap-3">
                <div v-if="variant === 'destructive'" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <AlertTriangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-semibold">{{ title }}</h2>
                    <p class="mt-2 text-sm text-muted-foreground">{{ message }}</p>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <Button variant="outline" :disabled="loading" @click="emit('cancel')" class="cursor-pointer">{{ cancelText }}</Button>
                <Button :variant="variant" :disabled="loading" @click="emit('confirm')" class="cursor-pointer">
                    {{ loading ? '...' : confirmText }}
                </Button>
            </div>
        </div>
    </div>
</template>
