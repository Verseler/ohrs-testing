<script setup lang="ts">
defineProps({
    icon: String,
});
</script>

<template>
    <div
        class="flex items-center justify-between mb-2 text-sm cursor-pointer p-ripple text-neutral-100"
    >
        <span class="font-medium"><slot /></span>
        <i :class="icon"></i>
    </div>
</template>
