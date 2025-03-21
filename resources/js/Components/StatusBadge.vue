<script setup lang="ts">
import Badge from "@/Components/ui/badge/Badge.vue";
import type { ReservationStatus } from "@/Pages/Admin/Reservation/reservation.types";

const { status } = defineProps<{ status: ReservationStatus }>();
</script>

<template>
    <Badge
        :severity="
            status === 'checked_in'
                ? 'info'
                : status === 'checked_out'
                ? 'secondary'
                : status === 'canceled'
                ? 'danger'
                : status === 'confirmed'
                ? 'success'
                : 'warning' //status: pending
        "
        class='capitalize'
    >
        {{ status }}
    </Badge>
</template>
