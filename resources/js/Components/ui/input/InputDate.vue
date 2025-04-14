<script setup lang="ts">
type InputDateProps = {
    invalid?: boolean;
    disabled?: boolean;
};

const { disabled, invalid = false } = defineProps<InputDateProps>();

const model = defineModel<string | Date>();
</script>

<template>
    <input
        v-model="model"
        type="date"
        id="check-in-date"
        placeholder="pick a date"
        class="block w-full h-12 bg-transparent rounded cursor-pointer border-primary-700"
        onclick="this.showPicker()"
        :class="[
            invalid ? 'border-red-500 text-red-500' : 'border-green-700',
            disabled && 'opacity-50',
        ]"
    />
</template>
