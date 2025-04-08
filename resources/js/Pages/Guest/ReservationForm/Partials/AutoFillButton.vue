<script setup lang="ts">
import { ArrowDown } from "lucide-vue-next";
import { Button } from "@/Components/ui/button";

const { onClick } = defineProps<{ onClick: () => void }>();
</script>

<template>
    <div
        class="absolute flex items-center justify-between h-8 px-2 left-32 top-10 gap-x-10"
    >
        <Button
            type="button"
            @click="onClick"
            class="bg-blue-500 hover:bg-blue-600 animate-bounce"
        >
            <ArrowDown />Add employee as first guest
        </Button>
    </div>
</template>
