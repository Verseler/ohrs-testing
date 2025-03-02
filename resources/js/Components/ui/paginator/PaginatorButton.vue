<script setup lang="ts">
import Button from "@/Components/ui/button/Button.vue";
import { Link } from "@inertiajs/vue3";
import {
    ChevronLeft,
    ChevronsLeft,
    ChevronRight,
    ChevronsRight,
} from "lucide-vue-next";

type PaginatorButtonProps = {
    variant?: "prev" | "next" | "start" | "end";
    href: string;
    disabled?: boolean;
};

const {
    variant = "next",
    href,
    disabled,
} = defineProps<PaginatorButtonProps>();
</script>

<template>
    <Link :href="href">
        <Button
            size="icon"
            variant="ghost"
            class="transition-colors hover:text-primary-500 active:bg-primary-100 hover:bg-primary-50 hover:border-primary-300 hover:border"
            :disabled="disabled"
        >
            <ChevronsLeft v-if="variant === 'start'" />
            <ChevronLeft v-else-if="variant === 'prev'" />
            <ChevronsRight v-else-if="variant === 'end'" />
            <ChevronRight v-else />
        </Button>
    </Link>
</template>
