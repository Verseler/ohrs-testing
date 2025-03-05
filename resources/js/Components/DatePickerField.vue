<script setup lang="ts">
import { Calendar } from '@/Components/ui/calendar'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import {
  DateFormatter,
  type DateValue,
  getLocalTimeZone,
} from '@internationalized/date'
import { CalendarIcon } from 'lucide-vue-next'

const df = new DateFormatter('en-US', {
  dateStyle: 'long',
})

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
    invalid,
} = defineProps<DatePickerFieldProps>();

const model = defineModel<DateValue>();
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
        <div
        class="relative flex items-center px-4 py-2 cursor-pointer gap-x-3"
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
                    {{ model ? df.format(model.toDate(getLocalTimeZone())) : "Pick a date" }}
                </p>
            </div>
        </div>
    </PopoverTrigger>
    <PopoverContent class="w-auto p-0">
      <Calendar v-model:="model" :max-value="maxValue" :min-value="minValue" initial-focus />
    </PopoverContent>
  </Popover>
</template>
