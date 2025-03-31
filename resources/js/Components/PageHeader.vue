<script setup lang="ts"></script>

<template>
    <div class="flex items-center mt-2 mb-12 md:mt-8 gap-x-1.5 md:gap-x-3">
        <div class="md:px-3 px-1.5 py-2 md:py-3.5 text-white rounded-md bg-primary-500"><slot name="icon" /></div>
        <h1 class="text-2xl font-bold md:text-3xl"><slot name="title" /></h1>
    </div>
</template>
