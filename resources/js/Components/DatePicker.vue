<script setup lang="ts">
import { CalendarIcon } from "lucide-vue-next";
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";

type DatePickerFieldProps = {
    maxValue?: Date | string;
    minValue?: Date | string;
    disabled?: boolean;
    invalid?: boolean;
    class?: string;
};

const {
    maxValue,
    minValue,
    disabled = false,
    invalid = false,
    class: className,
} = defineProps<DatePickerFieldProps>();

const max = computed(() => {
    if (!maxValue) return;

    if (typeof maxValue === "string") {
        return new Date(maxValue).toISOString().split("T")[0];
    }

    return maxValue.toISOString().split("T")[0];
});

const min = computed(() => {
    if (!minValue) return;

    if (typeof minValue === "string") {
        return new Date(minValue).toISOString().split("T")[0];
    }

    return minValue.toISOString().split("T")[0];
});

const selectedDate = defineModel<Date | string | null>();

const currentDate = ref(new Date());
const isCalendarOpen = ref(false);
const daysContainerRef = ref<HTMLElement | null>(null);
const datepickerContainerRef = ref<HTMLElement | null>(null);

// Compute the days for the current month
const days = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    return Array.from({ length: daysInMonth }, (_, i) => {
        const day = i + 1;
        return {
            label: day,
            value: `${year}-${String(month + 1).padStart(2, "0")}-${String(
                day
            ).padStart(2, "0")}`,
        };
    });
});

// Watch for changes and re-render the calendar
watch([currentDate, isCalendarOpen], async () => {
    await nextTick(); // Ensure DOM updates are applied
    renderCalendar();
});

const renderCalendar = () => {
    // Ensure daysContainerRef is attached
    if (daysContainerRef.value) {
        // Your calendar rendering logic
    }
};

const handlePrevMonth = () => {
    currentDate.value = new Date(
        currentDate.value.setMonth(currentDate.value.getMonth() - 1)
    );
};

const handleNextMonth = () => {
    currentDate.value = new Date(
        currentDate.value.setMonth(currentDate.value.getMonth() + 1)
    );
};

const handleToggleCalendar = () => {
    isCalendarOpen.value = !isCalendarOpen.value;
};

const handleClickOutside = (event: MouseEvent) => {
    if (
        datepickerContainerRef.value &&
        datepickerContainerRef.value &&
        !datepickerContainerRef.value.contains(event.target as Node)
    ) {
        isCalendarOpen.value = false;
    }
};

const isDisabled = (day: string) => {
    const formattedDate = new Date(day);
    const minDate = min.value ? new Date(min.value) : null;
    const maxDate = max.value ? new Date(max.value) : null;

    return (
        (minDate && formattedDate < minDate) ||
        (maxDate && formattedDate > maxDate)
    );
};

const isSelected = (day: string | Date) => {
    const selectedDateObj = selectedDate.value ?? null;

    if (typeof selectedDateObj === "string") {
        return selectedDateObj === day;
    }

    return selectedDate.value && selectedDateObj === day;
};

const selectDate = (day: number) => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth() + 1;
    const formattedDate = `${year}-${String(month).padStart(2, "0")}-${String(
        day
    ).padStart(2, "0")}`;

    if (min.value && formattedDate < min.value) return;
    if (max.value && formattedDate > max.value) return;

    selectedDate.value = formattedDate;
};

// Add and remove event listener for clicks outside
watch(
    () => isCalendarOpen.value,
    (newValue) => {
        if (newValue) {
            document.addEventListener("click", handleClickOutside);
        } else {
            document.removeEventListener("click", handleClickOutside);
        }
    }
);

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
    <div class="relative w-full mx-auto" ref="datepickerContainerRef">
        <div class="relative">
            <input
                v-model="selectedDate"
                id="datepicker"
                type="text"
                placeholder="Pick a date"
                class="w-full h-12 pl-3.5 max-w-[510px] pr-4 bg-white border rounded outline-none appearance-none focus:ring-0 focus:outline-primary-500 border-stroke text-dark dark:border-dark-3 dark:bg-dark-2 dark:text-white"
                :class="[
                    invalid ? 'border-red-500' : 'border-green-700',
                    className,
                ]"
                readonly
                @click="handleToggleCalendar"
                :disabled="disabled"
                :max="max"
                :min="min"
            />
            <CalendarIcon
                @click="handleToggleCalendar"
                class="absolute inset-y-0 my-auto font-normal right-3 size-4"
                :class="{
                    'text-red-500': invalid,
                    'text-primary-700': !invalid,
                }"
            />
        </div>
        <div
            v-if="isCalendarOpen"
            ref="datepickerContainer"
            id="datepicker-container"
            class="absolute z-10 flex flex-col w-full p-4 bg-white border shadow-sm rounded-xl dark:bg-dark-2 dark:shadow-box-dark"
        >
            <div class="flex items-center justify-between pb-4">
                <button
                    id="prevMonth"
                    type="button"
                    @click="handlePrevMonth"
                    class="flex h-[38px] w-[38px] cursor-pointer items-center justify-center rounded-[7px] border-[.5px] border-stroke bg-gray-2 text-dark hover:border-primary hover:bg-primary-500 hover:text-white dark:border-dark-3 dark:bg-dark dark:text-white"
                >
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        class="fill-current"
                    >
                        <path
                            d="M16.2375 21.4875C16.0125 21.4875 15.7875 21.4125 15.6375 21.225L7.16249 12.6C6.82499 12.2625 6.82499 11.7375 7.16249 11.4L15.6375 2.77498C15.975 2.43748 16.5 2.43748 16.8375 2.77498C17.175 3.11248 17.175 3.63748 16.8375 3.97498L8.96249 12L16.875 20.025C17.2125 20.3625 17.2125 20.8875 16.875 21.225C16.65 21.375 16.4625 21.4875 16.2375 21.4875Z"
                        />
                    </svg>
                </button>
                <span
                    id="currentMonth"
                    class="text-base font-semibold capitalize text-dark dark:text-white"
                >
                    {{
                        currentDate.toLocaleDateString("en-US", {
                            month: "long",
                            year: "numeric",
                        })
                    }}
                </span>
                <button
                    type="button"
                    id="nextMonth"
                    class="flex h-[38px] w-[38px] cursor-pointer items-center justify-center rounded-[7px] border-[.5px] border-stroke bg-gray-2 text-dark hover:border-primary hover:bg-primary-500 hover:text-white dark:border-dark-3 dark:bg-dark dark:text-white"
                    @click="handleNextMonth"
                >
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        class="fill-current"
                    >
                        <path
                            d="M7.7625 21.4875C7.5375 21.4875 7.35 21.4125 7.1625 21.2625C6.825 20.925 6.825 20.4 7.1625 20.0625L15.0375 12L7.1625 3.97498C6.825 3.63748 6.825 3.11248 7.1625 2.77498C7.5 2.43748 8.025 2.43748 8.3625 2.77498L16.8375 11.4C17.175 11.7375 17.175 12.2625 16.8375 12.6L8.3625 21.225C8.2125 21.375 7.9875 21.4875 7.7625 21.4875Z"
                        />
                    </svg>
                </button>
            </div>
            <div
                id="days-of-week"
                class="grid grid-cols-7 pt-4 pb-2 text-sm font-medium capitalize sm:text-base dark:text-dark-6"
            >
                <span class="flex items-center justify-center size-9">
                    Mo
                </span>

                <span class="flex items-center justify-center size-9">
                    Tu
                </span>

                <span class="flex items-center justify-center size-9">
                    We
                </span>

                <span class="flex items-center justify-center size-9">
                    Th
                </span>

                <span class="flex items-center justify-center size-9">
                    Fr
                </span>

                <span class="flex items-center justify-center size-9">
                    Sa
                </span>

                <span class="flex items-center justify-center size-9">
                    Su
                </span>
            </div>

            <div
                ref="daysContainer"
                id="days-container"
                class="grid grid-cols-7 sm:text-base"
            >
                <template v-for="day in days" :key="day">
                    <div
                        v-if="day"
                        class="flex size-9 tex-xs select-none items-center cursor-pointer justify-center rounded-[7px] border-[.5px] border-transparent"
                        :class="{
                            'bg-primary-500 text-white': isSelected(day.value),
                            'hover:bg-primary-50':
                                !isSelected(day.value) &&
                                !isDisabled(day.value),
                            'text-gray-400 cursor-not-allowed opacity-50':
                                isDisabled(day.value),
                        }"
                        @click="selectDate(day.label)"
                    >
                        {{ day.label }}
                    </div>

                    <div
                        class="size-9 rounded-[7px] border-[.5px] border-transparent"
                        v-else
                    />
                </template>
            </div>
        </div>
    </div>
</template>
