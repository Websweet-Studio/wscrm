<script setup lang="ts">
import { PopoverRoot, type PopoverRootEmits, type PopoverRootProps } from 'reka-ui'

defineOptions({
  inheritAttrs: false,
})

defineProps<PopoverRootProps>()
defineEmits<PopoverRootEmits>()
</script>

<template>
  <PopoverRoot v-bind="$props" @update:open="$emit('update:open', $event)">
    <slot />
  </PopoverRoot>
</template>