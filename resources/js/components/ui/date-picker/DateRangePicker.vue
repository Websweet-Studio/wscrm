<template>
  <div class="flex items-center space-x-2">
    <DatePicker
      :model-value="startDate"
      @update:model-value="updateStartDate"
      :placeholder="startPlaceholder"
      :max-date="endDate || maxDate"
      :min-date="minDate"
      :disabled="disabled"
      :button-class="startDateClass"
    />
    <span class="text-muted-foreground">sampai</span>
    <DatePicker
      :model-value="endDate"
      @update:model-value="updateEndDate"
      :placeholder="endPlaceholder"
      :min-date="startDate || minDate"
      :max-date="maxDate"
      :disabled="disabled"
      :button-class="endDateClass"
    />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import DatePicker from './DatePicker.vue'

interface Props {
  modelValue?: { start: string; end: string }
  startPlaceholder?: string
  endPlaceholder?: string
  disabled?: boolean
  minDate?: string
  maxDate?: string
  startDateClass?: string
  endDateClass?: string
}

interface Emits {
  (e: 'update:modelValue', value: { start: string; end: string }): void
}

const props = withDefaults(defineProps<Props>(), {
  startPlaceholder: 'Tanggal mulai',
  endPlaceholder: 'Tanggal berakhir',
  disabled: false,
  startDateClass: '',
  endDateClass: '',
})

const emit = defineEmits<Emits>()

const startDate = computed(() => props.modelValue?.start || '')
const endDate = computed(() => props.modelValue?.end || '')

const updateStartDate = (date: string) => {
  emit('update:modelValue', {
    start: date,
    end: endDate.value
  })
}

const updateEndDate = (date: string) => {
  emit('update:modelValue', {
    start: startDate.value,
    end: date
  })
}
</script>