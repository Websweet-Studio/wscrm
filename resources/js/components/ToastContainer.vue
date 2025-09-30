<template>
  <div class="fixed top-4 right-4 z-50 space-y-2">
    <div
      v-for="toast in toasts"
      :key="toast.id"
      :class="cn(
        'pointer-events-auto relative flex w-full max-w-sm items-center justify-between space-x-4 overflow-hidden rounded-md border p-4 shadow-lg transition-all',
        'animate-in slide-in-from-top-full duration-300',
        toastVariants({ variant: toast.variant })
      )"
    >
      <div class="grid gap-1 flex-1">
        <div v-if="toast.title" class="text-sm font-semibold">
          {{ toast.title }}
        </div>
        <div v-if="toast.description" class="text-sm opacity-90">
          {{ toast.description }}
        </div>
      </div>
      <button
        @click="removeToast(toast.id)"
        class="ml-4 p-1 rounded-full hover:bg-black/10 transition-colors"
      >
        <svg
          class="w-4 h-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { cva } from 'class-variance-authority'
import { cn } from '@/lib/utils'
import { useToast } from '@/composables/useToast'

const { toasts, removeToast } = useToast()

const toastVariants = cva(
  'border bg-background text-foreground',
  {
    variants: {
      variant: {
        default: 'border-gray-200 bg-white text-gray-900',
        destructive: 'border-red-200 bg-red-50 text-red-800',
        success: 'border-green-200 bg-green-50 text-green-800',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  }
)
</script>