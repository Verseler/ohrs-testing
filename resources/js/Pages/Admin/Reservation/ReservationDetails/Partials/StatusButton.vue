<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import {
    BedIcon,
    CheckCircleIcon,
    ClockIcon,
    LogOutIcon,
    XCircleIcon,
} from "lucide-vue-next";
import type { ReservationStatus } from "@/Pages/Admin/Reservation/reservation.types";
import type { LucideIcon } from "@/types";

type StatusButtonProps = {
    nextStatus: ReservationStatus;
};

const { nextStatus } = defineProps<StatusButtonProps>();

type ButtonConfig = {
    label: string;
    icon: LucideIcon;
    color: string;
    description: string;
};

// Button configurations for next steps
const buttonConfigs: Record<ReservationStatus, ButtonConfig> = {
    pending: {
        label: "Pending Reservation",
        icon: ClockIcon,
        color: "bg-yellow-500 hover:bg-yellow-600 text-white",
        description: "Reservation is pending approval",
    },
    confirmed: {
        label: "Confirm Reservation",
        icon: CheckCircleIcon,
        color: "bg-green-500 hover:bg-green-600 text-white",
        description: "Reservation has been approved",
    },
    checked_in: {
        label: "Check In Guest",
        icon: BedIcon,
        color: "bg-blue-500 hover:bg-blue-600 text-white",
        description: "Guest has arrived at the hostel",
    },
    checked_out: {
        label: "Check Out Guest",
        icon: LogOutIcon,
        color: "bg-purple-500 hover:bg-purple-600 text-white",
        description: "Guest has departed from the hostel",
    },
    canceled: {
        label: "Cancel Reservation",
        icon: XCircleIcon,
        color: "bg-red-500 hover:bg-red-600 text-white",
        description: "Cancel this reservation",
    },
};
</script>

<template>
    <Button
        class="w-full h-12 text-lg"
        :class="buttonConfigs[nextStatus].color"
    >
        <component :is="buttonConfigs[nextStatus].icon" class="mr-2 !size-5" />
        {{ buttonConfigs[nextStatus].label }}
    </Button>
</template>
