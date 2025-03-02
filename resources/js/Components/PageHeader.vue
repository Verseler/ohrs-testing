<script setup lang="ts"></script>

<template>
    <div class="flex items-center mt-2 mb-12 md:mt-8 gap-x-3">
        <div class="px-3 py-3.5 text-white rounded-md bg-primary-500"><slot name="icon" /></div>
        <h1 class="text-3xl font-bold"><slot name="title" /></h1>
    </div>
</template>
