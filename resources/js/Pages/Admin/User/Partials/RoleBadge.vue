<script setup lang="ts">
import Badge from "@/Components/ui/badge/Badge.vue";
import type { UserRole } from "@/types";
import { BadgeCheck } from "lucide-vue-next";

const { role } = defineProps<{ role: UserRole }>();
</script>

<template>
    <Badge
        variant="outline"
        class="h-6 px-2 font-normal gap-x-1 place-content-center min-w-16 border-neutral-200"
        :severity="
            role === 'admin'
                ? 'info'
                : role === 'super_admin'
                ? 'success'
                : 'success'
        "
    >
        <BadgeCheck class="size-3.5" />
        {{ role }}
    </Badge>
</template>
