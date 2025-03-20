<script setup lang="ts">
import Header from '@/Components/Header.vue';
import { formatDateString } from '@/lib/utils';
import type { Reservation, ReservationStatus } from '@/Pages/Admin/Reservation/reservation.types';
import type { SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { CheckCircleIcon, ClockIcon, XCircleIcon } from 'lucide-vue-next';
import { computed } from 'vue';


type ReservationStatusResult = {
    reservation: Reservation;
    canLogin: boolean;
};

const { reservation, canLogin } = defineProps<ReservationStatusResult>();

const page = usePage<SharedData>();

const statusConfig = computed(() => {
    if (!reservation) return null;

    const configs: Record<ReservationStatus, { icon: any, color: string, borderColor: string, title: string, description: string }> = {
        pending: {
            icon: ClockIcon,
            color: "bg-yellow-100 text-yellow-800",
            borderColor: "border-yellow-200",
            title: "Pending",
            description: "Your reservation is currently being processed. Please check back for updates.",
        },
        confirmed: {
            icon: CheckCircleIcon,
            color: "bg-green-100 text-green-800",
            borderColor: "border-green-200",
            title: "Confirmed",
            description: "Your reservation is confirmed. We look forward to your stay.",
        },
        checked_in: {
            icon: CheckCircleIcon,
            color: "bg-blue-100 text-blue-800",
            borderColor: "border-blue-200",
            title: "Checked In",
            description: "Your stay has commenced. We hope you enjoy your time with us.",
        },
        checked_out: {
            icon: CheckCircleIcon,
            color: "bg-blue-100 text-blue-800",
            borderColor: "border-blue-200",
            title: "Checked Out",
            description: "Your stay has concluded. We appreciate you choosing our hostel.",
        },
        canceled: {
            icon: XCircleIcon,
            color: "bg-red-100 text-red-800",
            borderColor: "border-red-200",
            title: "Canceled",
            description: "This reservation has been canceled.",
        },
    };

    return configs[reservation.status];
});
</script>

<template>
     <Head title="Reservation Result" />

<div class="w-full min-h-screen">
    <Header :can-login="canLogin" :user="page.props.auth.user" />

    <!-- Reservation Status Result -->
    <div
         v-if="reservation"
        class="overflow-hidden mx-auto mt-20 mb-2 max-w-xl bg-white rounded-lg border border-gray-200 shadow-sm"
    >
                <!-- Status Header -->
                <div
                    class="flex justify-between items-center p-6"
                    :class="statusConfig?.color"
                >
                    <div class="flex items-center">
                        <component
                            :is="statusConfig?.icon"
                            class="mr-2 w-6 h-6"
                        />
                        <h2 class="text-lg font-semibold">
                            {{ statusConfig?.title }}
                        </h2>
                    </div>
                </div>

                <!-- Status Description -->
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <p>{{ statusConfig?.description }}</p>
                </div>

                <!-- Limited Reservation Details -->
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">
                                Number of Guests
                            </h3>
                            <p class="mt-1">
                                {{ reservation?.guests?.length }}
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">
                                Check-in Date
                            </h3>
                            <p class="mt-1">
                                {{ formatDateString(reservation?.check_in_date || '') }}
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">
                                Check-out Date
                            </h3>
                            <p class="mt-1">
                                {{ formatDateString(reservation?.check_out_date || '') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            </div>
</template>
