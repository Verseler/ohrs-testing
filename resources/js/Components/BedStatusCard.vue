<script setup lang="ts">
import { Severity } from "@/types";

type BedStatusCardProps = {
    severity: Severity;
    value: string;
};

const { severity = "info", value } = defineProps<BedStatusCardProps>();
</script>

<template>
    <div
        class="grid p-2 text-xs text-center border rounded size-16 place-content-center"
        :class="{
            'border-primary-500 bg-primary-100 text-primary-500':
                severity === 'success',
            'border-red-500 bg-red-100 text-red-500': severity === 'danger',
            'border-neutral-300 bg-neutral-50 text-neutral-500':
                severity === 'secondary',
            'border-blue-500 bg-blue-100 text-blue-500': severity === 'info',
        }"
    >
        <span class="text-wrap">
            {{ value }}
        </span>
    </div>
</template>
