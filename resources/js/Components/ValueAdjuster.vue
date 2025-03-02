<script setup>
import Button from "@/Components/ui/button/Button.vue";
import { Minus, Plus } from "lucide-vue-next";

defineProps({
    onDecrease: {
        type: Function,
        require: true,
    },
    onIncrease: {
        type: Function,
        require: true,
    },
    value: {
        type: Number,
        require: true,
    },
});
</script>

<template>
    <div class="flex gap-x-1">
        <Button
            type="button"
            class="w-16"
            @click="onDecrease"
            :disabled="value <= 1"
        >
            <Minus />
        </Button>
        <div
            class="grid flex-1 text-2xl border rounded shadow-sm place-content-center border-neutral-300 text-neutral-500"
        >
            {{ value }}
        </div>
        <Button type="button" class="w-16" @click="onIncrease">
            <Plus />
        </Button>
    </div>
</template>
