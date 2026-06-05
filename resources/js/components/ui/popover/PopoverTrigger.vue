<script setup lang="ts">
import { cn } from '@/lib/utils'
import {
  PopoverTrigger,
  type PopoverTriggerProps,
} from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<PopoverTriggerProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props
  return delegated
})
</script>

<template>
  <PopoverTrigger
    data-slot="popover-trigger"
    v-bind="delegatedProps"
    :class="cn(props.class)"
  >
    <slot />
  </PopoverTrigger>
</template>