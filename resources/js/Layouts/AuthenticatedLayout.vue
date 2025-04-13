<script setup>
import Sidebar from "@/Components/Sidebar/Sidebar.vue";
import { Toaster } from "@/Components/ui/sonner";
import { useFlashError, useFlashErrorOnMount, useFlashSuccess, useFlashSuccessOnMount } from "@/Composables/useFlash";
import { SidebarProvider } from "@/Components/ui/sidebar";

useFlashError();
useFlashSuccess();
useFlashErrorOnMount();
useFlashSuccessOnMount();
</script>

<template>
    <SidebarProvider>
        <Sidebar />

        <main class="overflow-y-auto p-4 w-full md:px-6 md:pb-10">
            <slot />
        </main>

        <Toaster />
    </SidebarProvider>
</template>
