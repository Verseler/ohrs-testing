<script setup lang="ts">
import { Button } from '@/Components/ui/button';
import type { Reservation } from '@/Pages/Admin/Reservation/reservation.types';
import { router } from '@inertiajs/vue3';

type ReservationItemProps = {
    reservation: Reservation;
}

const { reservation } = defineProps<ReservationItemProps>();

function checkReservation(code: string) {
    router.visit(route("reservation.checkStatus", { code: code }), {
        method: "get",
    });
}

</script>

<template>
    <div
        class="flex gap-x-2 items-center"
    >
        <p class="flex-1">
            {{ reservation.code }}
            <span
                v-if="reservation.min_check_in_date && reservation.max_check_out_date"
                class="block text-xs text-blue-500 md:inline-block">
                [{{ reservation.min_check_in_date }}
                <span class="text-neutral-500">to</span>
                {{ reservation.max_check_out_date }}]
            </span>
        </p>
        <Button
            @click="checkReservation(reservation.code)"
            variant="outline"
            class="border-primary-500 text-primary-500 hover:bg-primary-50 hover:text-primary-600"
        >
            Check Status
        </Button>
    </div>
</template>
