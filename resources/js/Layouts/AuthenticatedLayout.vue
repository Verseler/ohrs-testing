<script setup>
import Sidebar from "@/Components/Sidebar/Sidebar.vue";
import { Toaster } from "@/Components/ui/sonner";
import { useFlashError, useFlashSuccess } from "@/Composables/useFlash";
import { SidebarProvider } from "@/Components/ui/sidebar";

useFlashError();
useFlashSuccess();
</script>

<template>
    <SidebarProvider>
        <Sidebar />

        <main class="w-full p-4 overflow-y-auto md:px-6 md:pb-10">
            <slot />
        </main>

        <Toaster />
    </SidebarProvider>
</template>
