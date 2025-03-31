<script setup lang="ts">
import { Label } from "@/Components/ui/label";

type InputLabelProps = {
    optional?: boolean;
    class?: string;
};

const { optional = false, class: className } = defineProps<InputLabelProps>();
</script>

<template>
    <Label :class="className"><slot /></Label>
    <span v-if="optional === false" class="text-red-500">*</span>
</template>
