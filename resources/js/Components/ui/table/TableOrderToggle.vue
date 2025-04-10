<script setup lang="ts">
import Button from "@/Components/ui/button/Button.vue";
import { ArrowDownNarrowWide, ArrowUpWideNarrow } from "lucide-vue-next";


const model = defineModel();

function toggle() {
    if (model.value === "asc") {
        model.value = "desc";
    } else {
        model.value = "asc";
    }
}
</script>

<template>
    <Button
        @click="toggle"
        size="icon"
        variant="outline"
        class="w-28 text-neutral-500 md:w-9"
    >
        <ArrowDownNarrowWide v-if="model === 'asc'" />
        <ArrowUpWideNarrow v-else />
        <span class='block md:hidden'>Order by</span>
    </Button>
</template>
