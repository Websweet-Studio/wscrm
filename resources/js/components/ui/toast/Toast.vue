<template>
  <ToastProvider>
    <ToastRoot
      v-model:open="isOpen"
      :duration="duration"
      :class="cn(
        'group pointer-events-auto relative flex w-full items-center justify-between space-x-4 overflow-hidden rounded-md border p-6 pr-8 shadow-lg transition-all',
        'data-[swipe=cancel]:translate-x-0 data-[swipe=end]:translate-x-[var(--radix-toast-swipe-end-x)] data-[swipe=move]:translate-x-[var(--radix-toast-swipe-move-x)] data-[swipe=move]:transition-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[swipe=end]:animate-out data-[state=closed]:fade-out-80 data-[state=closed]:slide-out-to-right-full data-[state=open]:slide-in-from-top-full data-[state=open]:sm:slide-in-from-bottom-full',
        toastVariants({ variant }),
        className
      )"
      v-bind="forwarded"
    >
      <div class="grid gap-1">
        <ToastTitle v-if="title" :class="cn('text-sm font-semibold')">
          {{ title }}
        </ToastTitle>
        <ToastDescription v-if="description" :class="cn('text-sm opacity-90')">
          {{ description }}
        </ToastDescription>
      </div>
      <ToastClose />
    </ToastRoot>
    <ToastViewport />
  </ToastProvider>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
  ToastProvider,
  ToastRoot,
  ToastTitle,
  ToastDescription,
  ToastClose,
  ToastViewport,
  type ToastRootEmits,
} from 'reka-ui'
import { cva, type VariantProps } from 'class-variance-authority'
import { cn } from '@/lib/utils'

const toastVariants = cva(
  'group pointer-events-auto relative flex w-full items-center justify-between space-x-4 overflow-hidden rounded-md border p-6 pr-8 shadow-lg transition-all data-[swipe=cancel]:translate-x-0 data-[swipe=end]:translate-x-[var(--radix-toast-swipe-end-x)] data-[swipe=move]:translate-x-[var(--radix-toast-swipe-move-x)] data-[swipe=move]:transition-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[swipe=end]:animate-out data-[state=closed]:fade-out-80 data-[state=closed]:slide-out-to-right-full data-[state=open]:slide-in-from-top-full data-[state=open]:sm:slide-in-from-bottom-full',
  {
    variants: {
      variant: {
        default: 'border bg-background text-foreground',
        destructive:
          'destructive group border-destructive bg-destructive text-destructive-foreground',
        success: 'border-green-200 bg-green-50 text-green-800',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  }
)

export interface ToastProps {
  title?: string
  description?: string
  variant?: VariantProps<typeof toastVariants>['variant']
  duration?: number
  open?: boolean
  className?: string
}

const props = withDefaults(defineProps<ToastProps>(), {
  variant: 'default',
  duration: 5000,
  open: false,
})

const emits = defineEmits<ToastRootEmits>()

const isOpen = computed({
  get: () => props.open,
  set: (value) => emits('update:open', value),
})

const forwarded = computed(() => {
  const { title, description, variant, duration, open, className, ...rest } = props
  return rest
})
</script>