<script setup lang="ts">
import Badge from "@/Components/ui/badge/Badge.vue";
import { UserRoundCheck } from "lucide-vue-next";
import type { UserRole } from "@/types";
import { computed } from "vue";
import { roleLabel } from "@/lib/utils";

const { role } = defineProps<{ role: UserRole }>();

const severityValue = computed(() => {
    switch (role) {
        case "system_admin":
            return "danger";
        case "super_admin":
            return "success";
        case "admin":
        default:
            return "info";
    }
});
</script>

<template>
    <Badge
        variant="outline"
        class="h-6 px-2 font-normal gap-x-1 place-content-center min-w-16 w-max border-neutral-200"
        :severity="severityValue"
    >
        <UserRoundCheck class="size-3.5" />
        {{ roleLabel(role) }}
    </Badge>
</template>
