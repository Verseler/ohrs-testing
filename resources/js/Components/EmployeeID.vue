<script setup lang="ts">
import { IdCard } from "lucide-vue-next";
import { Label } from "@/Components/ui/label";
import { defineProps } from "vue";

const { value } = defineProps<{ value: string | null | undefined }>();
</script>

<template>
    <div class="space-y-1">
        <Label class="text-neutral-700"> ID Number </Label>
        <p class="flex items-center px-2 py-2 rounded gap-x-2 bg-neutral-100">
            <IdCard class="size-5 text-neutral-500" />
            {{ value ?? "-" }}
        </p>
    </div>
</template>
