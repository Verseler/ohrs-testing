<script setup lang="ts">
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div
        class="flex flex-col items-center pt-14 min-h-screen md:pt-6 md:bg-neutral-100 sm:justify-center sm:pt-0"
    >
        <Link href="/">
            <ApplicationLogo class="size-28" />
        </Link>

        <div
            class="overflow-hidden p-4 mt-6 w-full bg-white md:p-6 md:shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
