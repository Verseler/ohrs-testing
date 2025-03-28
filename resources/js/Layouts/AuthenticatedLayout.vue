<script setup>
import Sidebar from "@/Components/Sidebar/Sidebar.vue";
import { Toaster } from "@/Components/ui/sonner";
import { useFlashError, useFlashSuccess } from "@/Composables/useFlash";

useFlashError();
useFlashSuccess();
</script>

<template>
    <div class="flex min-h-screen bg-white">
        <Sidebar />

        <main class="flex-1 p-4 overflow-y-auto ms-72 md:px-6 md:pb-10">
            <slot />
        </main>

        <Toaster />
    </div>
</template>
