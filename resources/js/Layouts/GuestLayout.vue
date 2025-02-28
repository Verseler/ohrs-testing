<script setup lang='ts'>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex flex-col items-center min-h-screen pt-14 md:pt-6 md:bg-neutral-100 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="size-28" />
            </Link>
        </div>

        <div
            class="w-full p-4 mt-6 overflow-hidden bg-white md:p-6 md:shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
