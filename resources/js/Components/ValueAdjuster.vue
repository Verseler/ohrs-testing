<script setup lang="ts">
import Button from "@/Components/ui/button/Button.vue";
import { Minus, Plus } from "lucide-vue-next";

type ValueAdjusterProps = {
    onDecrease: () => void;
    onIncrease: () => void;
    value: number;
    max?: number;
    disableIncrease?: boolean;
    disableDecrease?: boolean;
    end?: boolean;
    size?: "sm" | "lg";
};

const {
    onIncrease,
    onDecrease,
    value,
    disableDecrease = false,
    disableIncrease = false,
    end = false,
    size = "lg",
} = defineProps<ValueAdjusterProps>();
</script>

<template>
    <div class="flex gap-x-1">
        <Button
            type="button"
            :class="{ 'opacity-90': disableDecrease, 'w-16': size === 'lg' }"
            @click="onDecrease"
            :disabled="disableDecrease"
        >
            <Minus />
        </Button>
        <div
            class="grid flex-1 text-2xl border rounded shadow-sm select-none place-content-center border-neutral-300 text-neutral-500"
            :class="{ '-order-1': end }"
        >
            {{ value }}
        </div>
        <Button
            type="button"
            :class="{ 'opacity-90': disableDecrease, 'w-16': size === 'lg' }"
            @click="onIncrease"
            :disabled="disableIncrease"
        >
            <Plus />
        </Button>
    </div>
</template>
