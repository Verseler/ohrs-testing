<script setup lang="ts">
import { Severity } from "@/types";

type StatusCardProps = {
    severity: Severity;
};

const { severity } = defineProps<StatusCardProps>();
</script>

<template>
    <div
        class="flex items-center w-full h-12 gap-2 p-4 border rounded-md"
        :class="{
            'border-primary-500 bg-primary-50 text-primary-500':
                severity === 'success',
            'border-red-500 bg-red-50 text-red-500': severity === 'danger',
            'border-yellow-500 bg-yellow-50 text-yellow-500':
                severity === 'warning',
            'border-neutral-400 bg-neutral-100 text-neutral-500':
                severity === 'secondary',
        }"
    >
        <span><slot name="icon" /></span>
        <span><slot name="label" /></span>
        <span class="ml-auto text-lg font-medium text-neutral-600">
            <slot name="value" />
        </span>
    </div>
</template>
