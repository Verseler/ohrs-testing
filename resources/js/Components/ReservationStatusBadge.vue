<script setup lang="ts">
import { ReservationStatus } from "@/Pages/Admin/Reservation/reservation.types";
import {
    CheckCircleIcon,
    ClockIcon,
    XCircleIcon,
    BedIcon,
    LogOutIcon,
    LucideIcon,
} from "lucide-vue-next";

type ReservationStatusBadgeProps = {
    status: ReservationStatus;
};

const { status } = defineProps<ReservationStatusBadgeProps>();

type StatusConfig = {
    label: string;
    icon: LucideIcon;
    color: string;
    nextSteps: string[];
};

const statusConfigs: Record<ReservationStatus, StatusConfig> = {
    pending: {
        label: "Pending Reservation",
        icon: ClockIcon,
        color: "bg-yellow-100 border-yellow-300 text-yellow-800",
        nextSteps: ["confirmed", "canceled"],
    },
    confirmed: {
        label: "Confirmed Reservation",
        icon: CheckCircleIcon,
        color: "bg-green-100 border-green-300 text-green-800",
        nextSteps: ["checked_in", "canceled"],
    },
    checked_in: {
        label: "Checked In",
        icon: BedIcon,
        color: "bg-blue-100 border-blue-300 text-blue-800",
        nextSteps: ["checked_out"],
    },
    checked_out: {
        label: "Checked Out",
        icon: LogOutIcon,
        color: "bg-neutral-100 border-neutral-300 text-neutral-800",
        nextSteps: [],
    },
    canceled: {
        label: "Canceled Reservation",
        icon: XCircleIcon,
        color: "bg-red-100 border-red-300 text-red-800",
        nextSteps: [],
    },
};
</script>

<template>
    <div class="flex justify-center mt-4">
        <div
            class="inline-flex items-center px-5 py-3 text-lg font-medium border-2 rounded-lg"
            :class="statusConfigs[status].color"
        >
            <component :is="statusConfigs[status].icon" class="w-6 h-6 mr-3" />
            Current Status: {{ statusConfigs[status].label }}
        </div>
    </div>
</template>
