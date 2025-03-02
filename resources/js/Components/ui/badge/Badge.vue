<script setup lang="ts">
import type { HTMLAttributes } from "vue";
import { cn } from "@/lib/utils";
import { type BadgeVariants, badgeVariants } from ".";

const props = defineProps<{
    variant?: BadgeVariants["variant"];
    severity?: BadgeVariants["severity"];
    class?: HTMLAttributes["class"];
}>();
</script>

<template>
    <div :class="cn(badgeVariants({ variant, severity }), props.class)">
        <slot />
    </div>
</template>
