<script setup>
import { RadioGroupItem } from "@/Components/ui/radio-group";

defineProps({
    name: {
        type: String,
        require: true,
    },
    value: {
        type: String,
        require: true,
    },
    label: {
        type: String,
        require: true,
    },
    active: Boolean,
});

const model = defineModel();
</script>

<template>
    <div class="relative">
        <RadioGroupItem
            v-model="model"
            :id="value"
            :value="value"
            class="absolute transform -translate-y-1/2 left-2 top-1/2"
            :class="{
                'border-primary-600 accent-primary-500': active,
                'border-neutral-300': !active,
            }"
        />
        <label
            :for="value"
            :class="{
                'border-primary-500 text-primary-500': active,
                'border-neutral-300 text-neutral-600': !active,
            }"
            class="block select-none capitalize rounded-md cursor-pointer border min-h-10 py-2.5 pl-9 pr-4 shadow-sm"
        >
            {{ label }}
        </label>
    </div>
</template>
