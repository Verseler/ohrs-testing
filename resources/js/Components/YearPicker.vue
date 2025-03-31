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

interface YearPickerProps {
    modelValue?: Date | null;
    placeholder?: string;
    disabled?: boolean;
    minYear?: number;
    maxYear?: number;
}

const props = withDefaults(defineProps<YearPickerProps>(), {
    modelValue: null,
    placeholder: "Select year",
    disabled: false,
    minYear: 1900,
    maxYear: 2100,
});

const emit = defineEmits<{
    (e: "update:modelValue", value: Date): void;
}>();

// State
const isOpen = ref(false);
const currentDecade = ref(Math.floor(new Date().getFullYear() / 10) * 10);
const selectedYear = ref<Date | null>(props.modelValue);

// When props.modelValue changes, update selectedYear
watch(
    () => props.modelValue,
    (newValue) => {
        selectedYear.value = newValue;
        if (newValue) {
            currentDecade.value = Math.floor(newValue.getFullYear() / 10) * 10;
        }
    },
    { immediate: true }
);

// Computed properties
const decadeStart = computed(() => currentDecade.value);
const decadeEnd = computed(() => currentDecade.value + 9);

const yearsInView = computed(() => {
    const years: number[] = [];
    for (let i = 0; i < 12; i++) {
        const year = currentDecade.value + i - 1; // Include previous year and two years of next decade
        if (year >= props.minYear && year <= props.maxYear) {
            years.push(year);
        }
    }
    return years;
});

const displayValue = computed(() => {
    if (!selectedYear.value) return props.placeholder;
    return selectedYear.value.getFullYear().toString();
});

// Methods
function isSelectedYear(year: number): boolean {
    if (!selectedYear.value) return false;
    return selectedYear.value.getFullYear() === year;
}

function selectYear(year: number): void {
    // If there's already a selected date, keep the month and day, just change the year
    const date = selectedYear.value
        ? new Date(
              year,
              selectedYear.value.getMonth(),
              selectedYear.value.getDate()
          )
        : new Date(year, 0, 1); // Default to January 1st if no date was previously selected

    selectedYear.value = date;
    emit("update:modelValue", date);
    isOpen.value = false;
}

function prevDecade(): void {
    currentDecade.value -= 10;
}

function nextDecade(): void {
    currentDecade.value += 10;
}
</script>

<template>
    <div class="year-picker">
        <Popover v-model:open="isOpen" class="relative">
            <PopoverTrigger as-child>
                <Button
                    variant="outline"
                    class="justify-between w-full pr-3 hover:text-primary-800 hover:bg-primary-50 border-primary-500"
                    :class="{
                        'text-neutral-500': !selectedYear,
                        'text-primary-700': selectedYear,
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
                            class="h-7 w-7"
                            @click="prevDecade"
                        >
                            <ChevronLeftIcon class="w-4 h-4" />
                            <span class="sr-only">Previous Decade</span>
                        </Button>
                        <div class="font-medium">
                            {{ decadeStart }} - {{ decadeEnd }}
                        </div>
                        <Button
                            variant="outline"
                            size="icon"
                            class="h-7 w-7"
                            @click="nextDecade"
                        >
                            <ChevronRightIcon class="w-4 h-4" />
                            <span class="sr-only">Next Decade</span>
                        </Button>
                    </div>
                    <div class="grid grid-cols-4 gap-2">
                        <Button
                            v-for="year in yearsInView"
                            :key="year"
                            variant="ghost"
                            class="h-9"
                            :class="{
                                'bg-primary-500 text-white hover:bg-primary-600 hover:text-white':
                                    isSelectedYear(year),
                            }"
                            @click="selectYear(year)"
                        >
                            {{ year }}
                        </Button>
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    </div>
</template>
