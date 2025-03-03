<script setup lang="ts">
import { cn } from "@/lib/utils";
import Button from "@/Components/ui/button/Button.vue";
import { Link } from "@inertiajs/vue3";

type PopoverLinkFieldProps = {
    variant?: "default" | "danger";
    href: string;
};

const { variant = "default", href } = defineProps<PopoverLinkFieldProps>();
</script>

<template>
    <Button
        type="button"
        variant="ghost"
        :class="
            cn(
                'justify-start font-normal rounded-none',
                variant === 'danger'
                    ? ' text-red-500 rounded-none hover:text-red-600 hover:bg-red-50'
                    : 'text-neutral-700'
            )
        "
        as-child
    >
        <Link :href="href">
            <slot />
        </Link>
    </Button>
</template>
