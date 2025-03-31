<script setup lang="ts">
import Badge from "@/Components/ui/badge/Badge.vue";

const { available } = defineProps<{ available: boolean }>();
</script>

<template>
    <Badge :severity="available ? 'danger' : 'success'">
        {{ available ? "Fully Occupied" : "Available" }}
    </Badge>
</template>
