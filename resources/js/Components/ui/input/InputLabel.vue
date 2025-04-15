<script setup lang="ts">
import { Label } from "@/Components/ui/label";

type InputLabelProps = {
    optional?: boolean;
    class?: string;
};

const { optional = false, class: className } = defineProps<InputLabelProps>();
</script>

<template>
    <div class="flex gap-x-1 items-center">
        <Label :class="className"><slot /></Label>
    <span v-if="optional === false" class="text-red-500">*</span>
    </div>
</template>
