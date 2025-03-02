<script setup lang="ts">
import Button from "@/Components/ui/button/Button.vue";
import { ArrowLeft } from "lucide-vue-next";
</script>

<template>
    <a onclick="history.back();return false;">
        <Button
            variant="outline"
            size="icon"
            class="border-primary-500 text-primary-500 hover:bg-primary-50 hover:text-primary-600"
        >
            <ArrowLeft />
        </Button>   
    </a>
</template>
