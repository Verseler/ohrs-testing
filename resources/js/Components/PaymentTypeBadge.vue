<script setup lang="ts">
import Badge from "@/Components/ui/badge/Badge.vue";
import type { PaymentType, ReservationStatus } from "@/Pages/Admin/Reservation/reservation.types";
import { computed } from "vue";

const { paymentType } = defineProps<{ paymentType: PaymentType }>();

const paymentTypeSeverity = computed(() => {
    switch (paymentType) {
        case "pay_later":
            return "warning";
        case "full_payment":
        default:
            return "info"; 
    }
});

const paymentTypeValue = computed(() => {
    switch (paymentType) {
        case "pay_later":
            return "Pay Later";
        case "full_payment":
            return "Full Payment";
        default:
            return paymentType;
    }
});
</script>

<template>
    <Badge :severity="paymentTypeSeverity" class="capitalize w-max">
        {{ paymentTypeValue }}
    </Badge>
</template>
