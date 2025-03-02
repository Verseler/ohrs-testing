<script setup lang="ts">
import { cn } from "@/lib/utils";
import Button from "@/Components/ui/button/Button.vue";
type PopoverFieldProps = {
    variant?: "default" | "danger";
};

const { variant = "default" } = defineProps<PopoverFieldProps>();
</script>

<template>
    <Button
        variant="ghost"
        :class="
            cn(
                'justify-start font-normal rounded-none',
                variant === 'danger'
                    ? ' text-red-500 rounded-none hover:text-red-600 hover:bg-red-50'
                    : 'text-neutral-700'
            )
        "
    >
        <slot />
    </Button>
</template>
