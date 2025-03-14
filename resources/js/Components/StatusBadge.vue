<script setup lang="ts">
import Badge from '@/Components/ui/badge/Badge.vue';
import { capitalized } from '@/lib/utils';
import { ReservationStatus } from '@/Pages/ReservationManagement/reservations.types';

const { status } = defineProps<{ status: ReservationStatus }>();
</script>

<template>
    <Badge
        :severity="
            status === 'checked_in'
                ? 'success'
                : status === 'checked_out'
                ? 'secondary'
                : status === 'canceled'
                ? 'danger'
                : 'warning' //status: pending
        "
    >
    {{ capitalized(status) }}
    </Badge>
</template>
