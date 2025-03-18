<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";

type LinkButtonProps = {
    href: string;
};

const { href } = defineProps<LinkButtonProps>();
</script>

<template>
    <Button as-child>
        <Link :href="href">
            <slot />
        </Link>
    </Button>
</template>
