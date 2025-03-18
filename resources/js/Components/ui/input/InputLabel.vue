<script setup lang="ts">
import { Label } from "@/Components/ui/label";

type InputLabelProps = {
    optional?: boolean;
};

const { optional = false } = defineProps<InputLabelProps>();
</script>

<template>
    <Label><slot /></Label>
    <span v-if="optional === false" class="text-red-500">*</span>
</template>
