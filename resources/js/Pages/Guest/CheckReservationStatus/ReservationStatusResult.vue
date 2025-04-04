<script setup lang="ts">
import Header from "@/Components/Header.vue";
import { formatCurrency, formatDateString, obscureName } from "@/lib/utils";
import type {
    Reservation,
    ReservationStatus,
} from "@/Pages/Admin/Reservation/reservation.types";
import { usePoll } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import { CheckCircleIcon, ClockIcon, XCircleIcon } from "lucide-vue-next";
import { Card, CardContent, CardHeader } from "@/Components/ui/card";
import { computed } from "vue";

type ReservationStatusResult = {
    reservation: Reservation;
};

const { reservation } = defineProps<ReservationStatusResult>();

usePoll(15000);

const statusConfig = computed(() => {
    if (!reservation) return null;

    const configs: Record<
        ReservationStatus,
        {
            icon: any;
            color: string;
            borderColor: string;
            title: string;
            description: string;
        }
    > = {
        pending: {
            icon: ClockIcon,
            color: "bg-yellow-100 text-yellow-800",
            borderColor: "border-yellow-200",
            title: "Pending",
            description:
                "Your reservation is waiting for approval. Please check back for updates.",
        },
        confirmed: {
            icon: CheckCircleIcon,
            color: "bg-green-100 text-green-800",
            borderColor: "border-green-200",
            title: "Confirmed",
            description:
                "Your reservation is confirmed. We look forward to your stay.",
        },
        checked_in: {
            icon: CheckCircleIcon,
            color: "bg-blue-100 text-blue-800",
            borderColor: "border-blue-200",
            title: "Checked In",
            description:
                "Your stay has commenced. We hope you enjoy your time with us.",
        },
        checked_out: {
            icon: CheckCircleIcon,
            color: "bg-blue-100 text-blue-800",
            borderColor: "border-blue-200",
            title: "Checked Out",
            description:
                "Your stay has concluded. We appreciate you choosing our hostel.",
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

    <d class="w-full min-h-screen">
        <Header />

        <!-- Reservation Status Result -->
        <Card
            v-if="reservation"
            class="max-w-xl mx-auto mt-20 mb-2 overflow-hidden border-none rounded-none shadow-none md:rounded-xl md:border md:shadow"
        >
            <!-- Status Header -->
            <CardHeader
                class="flex flex-col p-6 gap-y-2"
                :class="statusConfig?.color"
            >
                <div class="flex items-center">
                    <component :is="statusConfig?.icon" class="w-6 h-6 mr-2" />
                    <h2 class="text-lg font-semibold">
                        {{ statusConfig?.title }}
                    </h2>
                </div>

                <!-- Status Description -->
                <p class="pl-8">{{ statusConfig?.description }}</p>
            </CardHeader>

            <!-- Limited Reservation Details -->
            <CardContent class="p-6 space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">
                            Booked By
                        </h3>
                        <p class="mt-1">
                            {{
                                obscureName(
                                    reservation.first_name,
                                    reservation.last_name
                                )
                            }}
                        </p>
                    </div>

                    <div>
                        <h3
                            v-if="reservation?.status !== 'canceled'"
                            class="text-sm font-medium text-gray-500"
                        >
                            Number of Guests
                        </h3>
                        <p
                            v-if="reservation?.status !== 'canceled'"
                            class="mt-1"
                        >
                            {{ reservation?.guests?.length }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500">
                            Check-in Date
                        </h3>
                        <p class="mt-1">
                            {{
                                formatDateString(
                                    reservation?.check_in_date || ""
                                )
                            }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500">
                            Check-out Date
                        </h3>
                        <p class="mt-1">
                            {{
                                formatDateString(
                                    reservation?.check_out_date || ""
                                )
                            }}
                        </p>
                    </div>

                    <div
                        v-if="
                            reservation.status !== 'pending' &&
                            reservation.status !== 'canceled' &&
                            reservation.total_billings
                        "
                    >
                        <h3 class="text-sm font-medium text-gray-500">
                            Total Billings
                        </h3>
                        <p class="mt-1">
                            ₱{{ formatCurrency(reservation.total_billings) }}
                        </p>
                    </div>

                    <div
                        v-if="
                            reservation.status !== 'pending' &&
                            reservation.status !== 'canceled'
                        "
                    >
                        <h3 class="text-sm font-medium text-gray-500">
                            Remaining Balance
                        </h3>
                        <p class="mt-1">
                            ₱{{ formatCurrency(reservation.remaining_balance) }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500">
                            Hostel Location
                        </h3>
                        <p class="mt-1">
                            Region {{ reservation.hostel_office.region.name }} -
                            {{ reservation.hostel_office.name }}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </d>
</template>
