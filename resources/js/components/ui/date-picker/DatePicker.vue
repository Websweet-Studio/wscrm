<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { useVModel } from '@vueuse/core'
import {
  addDays,
  addMonths,
  endOfMonth,
  endOfWeek,
  format,
  isAfter,
  isBefore,
  isSameDay,
  isSameMonth,
  parseISO,
  startOfMonth,
  startOfWeek,
  subMonths,
} from 'date-fns'
import { Calendar, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { computed, nextTick, onBeforeUnmount, onMounted, ref, useAttrs, watch } from 'vue'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps<{
  defaultValue?: string
  modelValue?: string
  error?: boolean
  class?: HTMLAttributes['class']
}>()

const emits = defineEmits<{
  (e: 'update:modelValue', payload: string): void
}>()

const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.defaultValue ?? '',
})

const attrs = useAttrs()
const root = ref<HTMLElement | null>(null)
const inputEl = ref<HTMLInputElement | null>(null)
const open = ref(false)

const placeholder = computed(() => (attrs.placeholder as string) || 'Pilih tanggal')
const minDateString = computed(() => (attrs['min-date'] as string) || (attrs.min as string) || '')
const maxDateString = computed(() => (attrs['max-date'] as string) || (attrs.max as string) || '')

const parseDate = (value: string) => {
  if (!value) return null
  try {
    const parsed = parseISO(value.includes(' ') ? value.split(' ')[0] : value)
    return Number.isNaN(parsed.getTime()) ? null : parsed
  } catch {
    return null
  }
}

const selectedDate = computed(() => parseDate(modelValue.value))
const minDate = computed(() => parseDate(minDateString.value))
const maxDate = computed(() => parseDate(maxDateString.value))

const viewMonth = ref<Date>(selectedDate.value ? startOfMonth(selectedDate.value) : startOfMonth(new Date()))

watch(
  () => open.value,
  async (value) => {
    if (!value) return
    viewMonth.value = selectedDate.value ? startOfMonth(selectedDate.value) : startOfMonth(new Date())
    await nextTick()
    inputEl.value?.focus()
  },
)

const displayValue = computed(() => {
  if (!selectedDate.value) return ''
  return format(selectedDate.value, 'yyyy-MM-dd')
})

const monthLabel = computed(() => format(viewMonth.value, 'MMMM yyyy'))

const weekdayLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']

const calendarDays = computed(() => {
  const start = startOfWeek(startOfMonth(viewMonth.value), { weekStartsOn: 1 })
  const end = endOfWeek(endOfMonth(viewMonth.value), { weekStartsOn: 1 })
  const days: Date[] = []
  let current = start
  while (current <= end) {
    days.push(current)
    current = addDays(current, 1)
  }
  return days
})

const isDisabled = (date: Date) => {
  if (minDate.value && isBefore(date, minDate.value)) return true
  if (maxDate.value && isAfter(date, maxDate.value)) return true
  return false
}

const setDate = (date: Date) => {
  if (isDisabled(date)) return
  modelValue.value = format(date, 'yyyy-MM-dd')
  open.value = false
}

const toggle = async () => {
  if (attrs.disabled !== undefined && attrs.disabled !== false) return
  open.value = !open.value
  if (open.value) {
    await nextTick()
    inputEl.value?.focus()
  }
}

const onDocumentPointerDown = (event: MouseEvent | PointerEvent) => {
  if (!open.value) return
  const el = root.value
  if (!el) return
  const target = event.target as Node | null
  if (target && el.contains(target)) return
  open.value = false
}

const onKeyDown = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    open.value = false
  }
}

onMounted(() => {
  document.addEventListener('pointerdown', onDocumentPointerDown, { capture: true })
  document.addEventListener('keydown', onKeyDown)
})

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', onDocumentPointerDown, { capture: true } as any)
  document.removeEventListener('keydown', onKeyDown)
})

const inputAttrs = computed(() => {
  const {
    class: _class,
    placeholder: _placeholder,
    min: _min,
    max: _max,
    'min-date': _minDate,
    'max-date': _maxDate,
    name: _name,
    form: _form,
    required: _required,
    ...rest
  } = attrs as Record<string, any>

  return rest
})

const hiddenAttrs = computed(() => {
  const name = (attrs.name as string) || undefined
  const form = (attrs.form as string) || undefined
  return { name, form }
})
</script>

<template>
  <div ref="root" class="relative">
    <div class="relative">
      <input
        ref="inputEl"
        v-bind="inputAttrs"
        :value="displayValue"
        type="text"
        readonly
        data-slot="date-picker"
        :aria-invalid="props.error ? 'true' : undefined"
        :placeholder="placeholder"
        :class="cn(
          'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground bg-input border-border flex h-9 w-full min-w-0 rounded-md border px-9 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
          'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
          'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
          (attrs as any).class,
          props.class,
        )"
        @click="toggle"
      >
      <button
        type="button"
        class="absolute inset-y-0 left-0 flex w-9 items-center justify-center text-muted-foreground"
        @click="toggle"
      >
        <Calendar class="h-4 w-4" />
      </button>
    </div>

    <div
      v-if="open"
      class="absolute z-50 mt-2 w-[280px] overflow-hidden rounded-md border border-border bg-popover text-popover-foreground shadow-md"
    >
      <div class="flex items-center justify-between border-b border-border px-2 py-2">
        <button type="button" class="rounded-md p-2 text-muted-foreground hover:bg-muted/60 hover:text-foreground" @click="viewMonth = subMonths(viewMonth, 1)">
          <ChevronLeft class="h-4 w-4" />
        </button>
        <div class="text-sm font-medium">{{ monthLabel }}</div>
        <button type="button" class="rounded-md p-2 text-muted-foreground hover:bg-muted/60 hover:text-foreground" @click="viewMonth = addMonths(viewMonth, 1)">
          <ChevronRight class="h-4 w-4" />
        </button>
      </div>

      <div class="grid grid-cols-7 gap-1 px-2 pb-2 pt-2">
        <div v-for="label in weekdayLabels" :key="label" class="pb-1 text-center text-[11px] font-medium text-muted-foreground">
          {{ label }}
        </div>

        <button
          v-for="day in calendarDays"
          :key="day.toISOString()"
          type="button"
          :disabled="isDisabled(day)"
          :class="cn(
            'flex h-9 w-9 items-center justify-center rounded-md text-sm transition-colors',
            isDisabled(day) ? 'cursor-not-allowed opacity-40' : 'hover:bg-muted/60',
            isSameDay(day, selectedDate || new Date(0)) ? 'bg-primary text-primary-foreground hover:bg-primary' : '',
            !isSameMonth(day, viewMonth) ? 'text-muted-foreground' : '',
          )"
          @click="setDate(day)"
        >
          {{ format(day, 'd') }}
        </button>
      </div>
    </div>

    <input v-bind="hiddenAttrs" type="hidden" :value="displayValue" />
  </div>
</template>
