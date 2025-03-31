<script setup lang="ts">
import Badge from "@/Components/ui/badge/Badge.vue";
import type { ReservationStatus } from "@/Pages/Admin/Reservation/reservation.types";
import { computed } from "vue";

const { status } = defineProps<{ status: ReservationStatus }>();

const statusSeverity = computed(() => {
    switch (status) {
        case "checked_in":
            return "info";
        case "checked_out":
            return "secondary";
        case "canceled":
            return "danger";
        case "confirmed":
            return "success";
        default:
            return "warning"; // status: pending
    }
});

const statusValue = computed(() => {
    switch (status) {
        case "checked_in":
            return "checked-in";
        case "checked_out":
            return "checked-out";
        default:
            return status;
    }
});
</script>

<template>
    <Badge :severity="statusSeverity" class="capitalize w-max">
        {{ statusValue }}
    </Badge>
</template>
