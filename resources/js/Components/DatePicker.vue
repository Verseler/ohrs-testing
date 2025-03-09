<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { ChevronLeftIcon, ChevronRightIcon, CalendarIcon } from 'lucide-vue-next';

// Assuming DateValue is a Date object or null/undefined
type DateValue = Date | null | undefined;

type DatePickerFieldProps = {
  maxValue?: DateValue;
  minValue?: DateValue;
  disabled?: boolean;
  label: string;
  invalid?: boolean;
};

const {
  maxValue,
  minValue,
  label,
  disabled = false,
  invalid = false,
} = defineProps<DatePickerFieldProps>();

const model = defineModel<DateValue>();

// State
const isOpen = ref(false);
const pickerRef = ref<HTMLDivElement | null>(null);
const currentMonth = ref(new Date(Date.now() - (new Date()).getTimezoneOffset() * 60 * 1000).getUTCMonth());
const currentYear = ref(new Date(Date.now() - (new Date()).getTimezoneOffset() * 60 * 1000).getUTCFullYear());

// Initialize from model value if provided
if (model.value) {
  currentMonth.value = model.value.getUTCMonth();
  currentYear.value = model.value.getUTCFullYear();
}

// Watch for external changes to model value
watch(() => model.value, (newValue) => {
  if (newValue) {
    currentMonth.value = newValue.getUTCMonth();
    currentYear.value = newValue.getUTCFullYear();
  }
}, { immediate: true });

// Calendar helpers
const daysOfWeek = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

const currentMonthName = computed(() => {
  return new Date(currentYear.value, currentMonth.value, 1).toLocaleString('default', { month: 'long', timeZone: 'UTC' });
});

const calendarDays = computed(() => {
  const days = [];
  const firstDay = new Date(currentYear.value, currentMonth.value, 1).getUTCDay();
  const daysInMonth = new Date(currentYear.value, currentMonth.value + 1, 0).getUTCDate();

  // Add empty cells for days before the first day of the month
  for (let i = 0; i < firstDay; i++) {
    days.push(0);
  }

  // Add days of the month
  for (let i = 1; i <= daysInMonth; i++) {
    days.push(i);
  }

  return days;
});

// Format the selected date for display
const formattedDate = computed(() => {
  if (!model.value) return '';

  return model.value.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    timeZone: 'UTC',
  });
});

// Methods
const toggleCalendar = () => {
  if (disabled) return;
  isOpen.value = !isOpen.value;
};

const closeCalendar = () => {
  isOpen.value = false;
};

const prevMonth = () => {
  if (disabled) return;

  if (currentMonth.value === 0) {
    currentMonth.value = 11;
    currentYear.value--;
  } else {
    currentMonth.value--;
  }
};

const nextMonth = () => {
  if (disabled) return;

  if (currentMonth.value === 11) {
    currentMonth.value = 0;
    currentYear.value++;
  } else {
    currentMonth.value++;
  }
};

const selectDate = (day: number) => {
  if (disabled) return;

  const selectedDate = new Date(Date.UTC(currentYear.value, currentMonth.value, day));

  // Check if date is within min/max range
  if (isDateDisabled(selectedDate)) return;

  model.value = selectedDate;
  closeCalendar();
};

const selectToday = () => {
  if (disabled) return;

  const today = new Date();

  // Check if today is within min/max range
  if (isDateDisabled(new Date(today.toUTCString()))) return;

  currentMonth.value = today.getUTCMonth();
  currentYear.value = today.getUTCFullYear();
  selectDate(today.getUTCDate());
};

const clearDate = () => {
  if (disabled) return;
  model.value = null;
  closeCalendar();
};

const isSelectedDate = (day: number): boolean => {
  if (!model.value) return false;

  return (
    model.value.getUTCDate() === day &&
    model.value.getUTCMonth() === currentMonth.value &&
    model.value.getUTCFullYear() === currentYear.value
  );
};

const isDateDisabled = (date: Date): boolean => {
  if (minValue && date < minValue) return true;
  if (maxValue && date > maxValue) return true;
  return false;
};

const isDayDisabled = (day: number): boolean => {
  if (day === 0) return true;
  return isDateDisabled(new Date(Date.UTC(currentYear.value, currentMonth.value, day)));
};

// Click outside detection
const handleClickOutside = (event: MouseEvent) => {
  if (pickerRef.value && !pickerRef.value.contains(event.target as Node)) {
    closeCalendar();
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
  <div class="date-picker-field" ref="pickerRef">
    <!-- Date input trigger -->
    <div
    @click="toggleCalendar"
    class="flex relative gap-x-3 items-center px-4 py-2 cursor-pointer hover:bg-primary-50"
    >
        <CalendarIcon
            class="size-5 text-primary-500"
            :class="{ 'text-red-500': invalid }"
        />
        <div>
            <p
                class="font-bold text-primary-500"
                :class="{ 'text-red-500': invalid }"
            >
                {{ label }}
            </p>
            <p class="text-sm leading-none text-neutral-700">
                {{ formattedDate ? formattedDate : "Pick a date" }}
            </p>
        </div>
    </div>

    <!-- Calendar popup -->
    <div
      v-if="isOpen"
      class="absolute z-10 mt-1 w-64 bg-white rounded-md border border-gray-200 shadow-lg"
    >
      <!-- Calendar header -->
      <div class="flex justify-between items-center p-2 border-b">
        <button
          type='button'
          @click="prevMonth"
          class="p-1 rounded-full hover:bg-gray-100"
          :disabled="disabled"
          :class="{ 'cursor-not-allowed opacity-50': disabled }"
        >
          <ChevronLeftIcon class="w-4 h-4" />
        </button>
        <div class="text-sm font-medium">
          {{ currentMonthName }} {{ currentYear }}
        </div>
        <button
          type='button'
          @click="nextMonth"
          class="p-1 rounded-full hover:bg-gray-100"
          :disabled="disabled"
          :class="{ 'cursor-not-allowed opacity-50': disabled }"
        >
          <ChevronRightIcon class="w-4 h-4" />
        </button>
      </div>

      <!-- Days of week -->
      <div class="grid grid-cols-7 gap-1 p-2 text-xs font-medium text-center text-gray-500">
        <div v-for="day in daysOfWeek" :key="day">{{ day }}</div>
      </div>

      <!-- Calendar days -->
      <div class="grid grid-cols-7 gap-1 p-2">
        <div
          v-for="(day, index) in calendarDays"
          :key="index"
          class="flex justify-center items-center h-8 text-xs"
        >
          <button
            type='button'
            v-if="day !== 0"
            @click="selectDate(day)"
            :disabled="disabled || isDayDisabled(day)"
            :class="[
              'w-8 h-8 rounded flex items-center justify-center',
              isSelectedDate(day) ? 'bg-primary-500 text-white' : 'hover:bg-primary-100',
              (disabled || isDayDisabled(day)) ? 'cursor-not-allowed opacity-50' : '',
            ]"
          >
            {{ day }}
          </button>
        </div>
      </div>

      <!-- Footer buttons -->
      <div class="flex justify-between p-2 border-t">
        <button
          type='button'
          @click="clearDate"
          :disabled="disabled || !model"
          class="px-2 py-1 text-xs text-gray-600 rounded hover:bg-gray-50"
          :class="{ 'cursor-not-allowed opacity-50': disabled || !model }"
        >
          Clear
        </button>

        <button
          type='button'
          @click="selectToday"
          class="px-2 py-1 text-xs rounded text-primary hover:bg-gray-50"
          :class="{ 'cursor-not-allowed opacity-50': disabled || isDateDisabled(new Date()) }"
        >
          Today
        </button>
      </div>
    </div>
  </div>
</template>
