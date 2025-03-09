<script setup lang="ts">
import { ref, computed } from 'vue';
import { ChevronLeftIcon, ChevronRightIcon } from 'lucide-vue-next';

// Define model with TypeScript
const model = defineModel<Date | null>('modelValue', {
  default: null
});

// State
const currentMonth = ref(new Date().getMonth());
const currentYear = ref(new Date().getFullYear());

// Initialize from model value if provided
if (model.value) {
  currentMonth.value = model.value.getMonth();
  currentYear.value = model.value.getFullYear();
}

// Calendar helpers
const daysOfWeek = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

const currentMonthName = computed(() => {
  return new Date(currentYear.value, currentMonth.value).toLocaleString('default', { month: 'long' });
});

const calendarDays = computed(() => {
  const days = [];
  const firstDay = new Date(currentYear.value, currentMonth.value, 1).getDay();
  const daysInMonth = new Date(currentYear.value, currentMonth.value + 1, 0).getDate();

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

// Methods
const prevMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11;
    currentYear.value--;
  } else {
    currentMonth.value--;
  }
};

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0;
    currentYear.value++;
  } else {
    currentMonth.value++;
  }
};

const selectDate = (day: number) => {
  model.value = new Date(currentYear.value, currentMonth.value, day);
};

const selectToday = () => {
  const today = new Date();
  currentMonth.value = today.getMonth();
  currentYear.value = today.getFullYear();
  selectDate(today.getDate());
};

const isSelectedDate = (day: number): boolean => {
  if (!model.value) return false;

  return (
    model.value.getDate() === day &&
    model.value.getMonth() === currentMonth.value &&
    model.value.getFullYear() === currentYear.value
  );
};
</script>

<template>
  <div class="p-4 bg-white border rounded-md shadow border-neutral-300">
    <!-- Calendar header -->
    <div class="flex items-center justify-between p-2">
      <button type='button' @click="prevMonth" class="p-2 rounded hover:bg-gray-100">
        <ChevronLeftIcon class="w-4 h-4" />
      </button>
      <div class="text-sm font-medium">
        {{ currentMonthName }} {{ currentYear }}
      </div>
      <button type='button' @click="nextMonth" class="p-2 rounded hover:bg-gray-100">
        <ChevronRightIcon class="w-4 h-4" />
      </button>
    </div>

    <!-- Days of week -->
    <div class="grid grid-cols-7 gap-1 p-2 text-xs font-medium text-center text-gray-500">
      <div v-for="day in daysOfWeek" :key="day">{{ day }}</div>
    </div>

    <!-- Calendar days -->
    <div class="grid grid-cols-7 gap-2 p-2">
      <div
        v-for="(day, index) in calendarDays"
        :key="index"
        class="aspect-[3/2]"
      >
        <button
          type='button'
          v-if="day !== 0"
          @click="selectDate(day)"
          :class="[
            'size-full border  rounded border-neutral-300 grid place-content-center text-xs',
            isSelectedDate(day) ? 'bg-primary-500 text-white' : 'hover:bg-primary-100'
          ]"
        >
          {{ day }}
        </button>
      </div>
    </div>
  </div>
</template>
