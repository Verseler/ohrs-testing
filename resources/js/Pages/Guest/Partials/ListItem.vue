<script setup lang="ts">
import type { LucidIconType } from "@/Components/Sidebar/sidebar.type";

type ListItemProps = {
    icon: LucidIconType;
    label: string;
    value: string;
};

const { icon, label, value } = defineProps<ListItemProps>();
</script>

<template>
    <div class="flex items-start gap-4">
        <icon class="mt-0.5 w-5 h-5 text-neutral-500" />
        <div>
            <div class="font-medium">{{ label }}</div>
            <div class="text-neutral-500">
                {{ value }}
            </div>
        </div>
    </div>
</template>
