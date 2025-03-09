<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div class="grid min-h-screen place-content-center">
        <div class='space-x-4'>
        <Link :href="route('reservation.form', { host_office_id: 1 })">
            <Button>Regional Executive Office - Region 10</Button>
        </LInk>
        <Button disabled>Other Office</Button>
        <Button disabled>Other Office2</Button>
    </div>
    </div>
</template>
