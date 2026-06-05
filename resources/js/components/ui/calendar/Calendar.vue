<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { ChevronLeftIcon, ChevronRightIcon } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

interface Props {
  modelValue?: string
  class?: string
  minDate?: string
  maxDate?: string
}

interface Emits {
  (e: 'update:modelValue', value: string | undefined): void
  (e: 'select', value: string): void
}

const props = withDefaults(defineProps<Props>(), {
  class: ''
})

const emit = defineEmits<Emits>()

const currentDate = new Date()
const currentMonth = ref(currentDate.getMonth())
const currentYear = ref(currentDate.getFullYear())

const monthNames = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]

const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']

// Initialize date from props
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    const date = new Date(newValue)
    if (!isNaN(date.getTime())) {
      currentMonth.value = date.getMonth()
      currentYear.value = date.getFullYear()
    }
  }
}, { immediate: true })

const calendarDays = computed(() => {
  const firstDay = new Date(currentYear.value, currentMonth.value, 1)
  const startDate = new Date(firstDay)
  
  // Start from Sunday (0)
  const dayOfWeek = firstDay.getDay()
  startDate.setDate(startDate.getDate() - dayOfWeek)
  
  const days = []
  const today = new Date()
  today.setHours(0, 0, 0, 0) // Reset time to avoid timezone issues
  
  // Parse selectedDate properly for local timezone
  const selectedDate = props.modelValue ? (() => {
    const [year, month, day] = props.modelValue.split('-').map(Number)
    return new Date(year, month - 1, day) // Create local date
  })() : null
  
  for (let i = 0; i < 42; i++) { // 6 weeks * 7 days
    const date = new Date(startDate)
    date.setDate(startDate.getDate() + i)
    date.setHours(0, 0, 0, 0) // Reset time to avoid timezone issues
    
    const isCurrentMonth = date.getMonth() === currentMonth.value
    const isToday = date.getTime() === today.getTime()
    const isSelected = selectedDate && date.getTime() === selectedDate.getTime()
    
    let isDisabled = false
    if (props.minDate) {
      const [minYear, minMonth, minDay] = props.minDate.split('-').map(Number)
      const minDate = new Date(minYear, minMonth - 1, minDay)
      isDisabled = date < minDate
    }
    if (props.maxDate) {
      const [maxYear, maxMonth, maxDay] = props.maxDate.split('-').map(Number)
      const maxDate = new Date(maxYear, maxMonth - 1, maxDay)
      isDisabled = isDisabled || date > maxDate
    }
    
    // Format date as YYYY-MM-DD without timezone conversion
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    const dateString = `${year}-${month}-${day}`
    
    days.push({
      key: date.getTime().toString(),
      day: date.getDate(),
      date: dateString,
      isCurrentMonth,
      isToday,
      isSelected,
      isDisabled
    })
  }
  
  return days
})

const selectDate = (dateObj: any) => {
  if (dateObj.isDisabled) {
    return
  }
  emit('update:modelValue', dateObj.date)
  emit('select', dateObj.date)
}

const selectToday = () => {
  const today = new Date().toISOString().split('T')[0]
  emit('update:modelValue', today)
  emit('select', today)
}

const clearDate = () => {
  emit('update:modelValue', undefined)
}

const previousMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11
    currentYear.value--
  } else {
    currentMonth.value--
  }
}

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0
    currentYear.value++
  } else {
    currentMonth.value++
  }
}
</script>

<template>
  <div :class="cn('p-3', props.class)">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between mb-4">
      <button
        type="button"
        class="h-7 w-7 rounded hover:bg-accent hover:text-accent-foreground flex items-center justify-center"
        @click="previousMonth"
      >
        <ChevronLeftIcon class="h-4 w-4" />
      </button>
      
      <div class="flex items-center space-x-1">
        <h2 class="text-sm font-semibold">
          {{ monthNames[currentMonth] }} {{ currentYear }}
        </h2>
      </div>

      <button
        type="button"
        class="h-7 w-7 rounded hover:bg-accent hover:text-accent-foreground flex items-center justify-center"
        @click="nextMonth"
      >
        <ChevronRightIcon class="h-4 w-4" />
      </button>
    </div>

    <!-- Calendar Grid -->
    <div class="space-y-1">
      <!-- Day Headers -->
      <div class="grid text-center" style="grid-template-columns: repeat(7, minmax(0, 1fr));">
        <div
          v-for="day in dayNames"
          :key="day"
          class="h-8 w-8 text-xs font-medium text-muted-foreground flex items-center justify-center"
        >
          {{ day }}
        </div>
      </div>
      
      <!-- Calendar Days -->
      <div class="grid text-center" style="grid-template-columns: repeat(7, minmax(0, 1fr));">
        <button
          v-for="date in calendarDays"
          :key="date.key"
          type="button"
          :class="cn(
            'h-8 w-8 p-0 font-normal transition-colors hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 flex items-center justify-center rounded-md text-sm',
            !date.isCurrentMonth && 'text-gray-400 opacity-50',
            date.isToday && !date.isSelected && 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
            date.isSelected && 'bg-blue-600 text-white hover:bg-blue-700 focus:bg-blue-700',
            date.isDisabled && 'opacity-30 cursor-not-allowed'
          )"
          :disabled="date.isDisabled"
          @click="selectDate(date)"
        >
          {{ date.day }}
        </button>
      </div>
    </div>

    <!-- Footer Actions -->
    <div class="flex items-center justify-between pt-3 border-t mt-3">
      <button
        type="button"
        class="px-3 py-1 text-sm rounded border hover:bg-accent hover:text-accent-foreground"
        @click="selectToday"
      >
        Hari Ini
      </button>
      
      <button 
        v-if="modelValue"
        type="button"
        class="px-3 py-1 text-sm rounded text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
        @click="clearDate"
      >
        Hapus
      </button>
    </div>
  </div>
</template>