<script setup lang="ts">
import { ref, computed, watch } from "vue";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/Components/ui/popover";
import { Button } from "@/Components/ui/button";
import {
    CalendarIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from "lucide-vue-next";

interface MonthPickerProps {
    modelValue?: Date | null;
    placeholder?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<MonthPickerProps>(), {
    modelValue: null,
    placeholder: "Select month",
    disabled: false,
});

const emit = defineEmits<{
    (e: "update:modelValue", value: Date): void;
}>();

// State
const isOpen = ref(false);
const currentYear = ref(new Date().getFullYear());
const months = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
];

// Initialize with the provided date or null
const selectedMonth = ref<Date | null>(props.modelValue);

// When props.modelValue changes, update selectedMonth
watch(
    () => props.modelValue,
    (newValue) => {
        selectedMonth.value = newValue;
        if (newValue) {
            currentYear.value = newValue.getFullYear();
        }
    },
    { immediate: true }
);

// Computed display value
const displayValue = computed(() => {
    if (!selectedMonth.value) return props.placeholder;

    const month = months[selectedMonth.value.getMonth()];
    const year = selectedMonth.value.getFullYear();
    return `${month} ${year}`;
});

// Check if a month is the selected month
function isSelectedMonth(monthIndex: number): boolean {
    if (!selectedMonth.value) return false;

    return (
        selectedMonth.value.getMonth() === monthIndex &&
        selectedMonth.value.getFullYear() === currentYear.value
    );
}

// Select a month
function selectMonth(monthIndex: number): void {
    const date = new Date(currentYear.value, monthIndex, 1);
    selectedMonth.value = date;
    emit("update:modelValue", date);
    isOpen.value = false;
}

// Navigation functions
function prevYear(): void {
    currentYear.value--;
}

function nextYear(): void {
    currentYear.value++;
}
</script>

<template>
    <div class="month-picker">
        <Popover v-model:open="isOpen" class="relative">
            <PopoverTrigger as-child>
                <Button
                    variant="outline"
                    class="justify-start w-full pr-3 hover:text-primary-800 hover:bg-primary-50 border-primary-500"
                    :class="{
                        'text-neutral-500': !selectedMonth,
                        'text-primary-700': selectedMonth,
                    }"
                >
                    {{ displayValue }}
                    <CalendarIcon class="size-4 ml-1.5 text-primary-700" />
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto p-0">
                <div class="p-3">
                    <div class="flex items-center justify-between mb-2">
                        <Button
                            variant="outline"
                            size="icon"
                            class="size-7"
                            @click="prevYear"
                        >
                            <ChevronLeftIcon class="w-4 h-4" />
                            <span class="sr-only">Previous Year</span>
                        </Button>
                        <div class="font-medium">{{ currentYear }}</div>
                        <Button
                            variant="outline"
                            size="icon"
                            class="size-7"
                            @click="nextYear"
                        >
                            <ChevronRightIcon class="w-4 h-4" />
                            <span class="sr-only">Next Year</span>
                        </Button>
                    </div>
                    <!-- Month button options -->
                    <div class="grid grid-cols-3 gap-2">
                        <Button
                            v-for="(month, index) in months"
                            :key="month"
                            variant="ghost"
                            class="font-normal h-9"
                            :class="{
                                'bg-primary-500 text-white hover:bg-primary-600 hover:text-white':
                                    isSelectedMonth(index),
                            }"
                            @click="selectMonth(index)"
                        >
                            {{ month }}
                        </Button>
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    </div>
</template>
