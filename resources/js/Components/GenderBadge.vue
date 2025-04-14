<script setup lang="ts">
import Badge from "@/Components/ui/badge/Badge.vue";
import type { Gender } from '@/Pages/Guest/guest.types';
import { Mars, Venus, VenusAndMars } from "lucide-vue-next";

const { gender } = defineProps<{ gender: Gender | Omit<Gender, 'any'> }>();
</script>

<template>
    <Badge
        variant="outline"
        class="gap-x-1 place-content-center px-2 h-6 font-normal min-w-16 border-neutral-200"
        :severity="
            gender === 'male'
                ? 'info'
                : gender === 'female'
                ? 'danger'
                : 'success'
        "
    >
        <Mars v-if="gender === 'female'" class="size-3.5 min-w-3" />
        <Venus v-else-if="gender === 'male'" class="size-3.5 min-w-3" />
        <VenusAndMars v-else class="size-3.5" />
        {{ gender }}
    </Badge>
</template>
