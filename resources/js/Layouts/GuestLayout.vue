<script setup>
import Header from "@/Components/Header.vue";
import { Toaster } from "@/Components/ui/sonner";
import {
    useFlashError,
    useFlashSuccess,
    useFlashErrorOnMount,
    useFlashSuccessOnMount,
} from "@/Composables/useFlash";

useFlashError();
useFlashSuccess();
useFlashSuccessOnMount();
useFlashErrorOnMount();
</script>

<template>
    <main class="w-full min-h-screen">
        <Header />

        <slot />

        <Toaster />
    </main>
</template>
