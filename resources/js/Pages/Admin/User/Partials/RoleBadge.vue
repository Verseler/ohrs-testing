<script setup lang="ts">
import Badge from "@/Components/ui/badge/Badge.vue";
import { BadgeCheck } from "lucide-vue-next";
import type { UserRole } from "@/types";
import { computed } from "vue";

const { role } = defineProps<{ role: UserRole }>();

const severityValue = computed(() => {
    switch (role) {
        case "admin":
            return "info";
        case "super_admin":
        default:
            return "success";
    }
});
</script>

<template>
    <Badge
        variant="outline"
        class="h-6 px-2 font-normal gap-x-1 place-content-center min-w-16 border-neutral-200"
        :severity="severityValue"
    >
        <BadgeCheck class="size-3.5" />
        {{ role && role === "super_admin" ? "super admin" : "admin" }}
    </Badge>
</template>
