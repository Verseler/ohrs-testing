<script setup lang="ts">
import { formatCurrency, formatDateString, obscureName } from "@/lib/utils";
import type { Reservation } from "@/Pages/Admin/Reservation/reservation.types";
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
} from "@/Components/ui/card";
import { getStatusConfig } from "@/Pages/Guest/CheckReservationStatus/helper";
import { usePoll, Head, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { Button } from "@/Components/ui/button";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { Separator } from "@/Components/ui/separator";
import GuestLayout from "@/Layouts/GuestLayout.vue";

usePoll(15000);

type ReservationStatusResult = {
    reservation: Reservation & {
        guests_count: number;
    };
};

const { reservation } = defineProps<ReservationStatusResult>();

const form = useForm({
    reservation_code: reservation.code,
});

const statusConfig = computed(() =>
    getStatusConfig(reservation?.general_status || "pending")
);

const requestEditConfirmation = ref(false);
const requestCancelConfirmation = ref(false);
const requestRebookConfirmation = ref(false);

function openRequestEditConfirmation() {
    requestEditConfirmation.value = true;
}

function openRequestCancelConfirmation() {
    requestCancelConfirmation.value = true;
}

function openRequestRebookConfirmation() {
    requestRebookConfirmation.value = true;
}

function requestModify(action: string) {
    form.post(route("reservation.requestModify", { action }));
}
</script>

<template>
    <Head title="Reservation Result" />

    <GuestLayout>
        <Card
            v-if="reservation"
            class="max-w-xl mx-auto mt-20 mb-2 rounded-none shadow-none md:rounded-xl md:shadow-sm"
        >
            <!-- Status Header -->
            <CardHeader
                class="flex flex-col p-6 gap-y-2 rounded-tl-xl rounded-tr-xl"
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

                    <div v-if="reservation?.general_status !== 'canceled'">
                        <h3 class="text-sm font-medium text-gray-500">
                            Number of Guests
                        </h3>
                        <p class="mt-1">
                            {{ reservation?.guests_count }}
                        </p>
                    </div>

                    <div v-if="reservation?.general_status !== 'canceled'">
                        <h3 class="text-sm font-medium text-gray-500">
                            Check-in Date
                        </h3>
                        <p class="mt-1">
                            {{
                                formatDateString(
                                    reservation?.min_check_in_date || ""
                                )
                            }}
                        </p>
                    </div>

                    <div v-if="reservation?.general_status !== 'canceled'">
                        <h3 class="text-sm font-medium text-gray-500">
                            Check-out Date
                        </h3>
                        <p class="mt-1">
                            {{
                                formatDateString(
                                    reservation?.max_check_out_date || ""
                                )
                            }}
                        </p>
                    </div>

                    <div
                        v-if="
                            reservation.general_status !== 'pending' &&
                            reservation.general_status !== 'canceled' &&
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
                            reservation.general_status !== 'pending' &&
                            reservation.general_status !== 'canceled'
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
                            {{ reservation.hostel_office.name }}
                        </p>
                    </div>
                </div>
            </CardContent>

            <Separator
                v-if="reservation.general_status !== 'canceled'"
                class="my-4"
            />

            <CardFooter class="justify-end gap-3 py-4">
                <Button
                    v-if="reservation.general_status === 'pending'"
                    @click="openRequestEditConfirmation"
                    variant="outline"
                    class="text-blue-600 border-blue-500 hover:bg-blue-50 hover:text-blue-600"
                >
                    Edit Reservation
                </Button>

                <Button
                    v-if="reservation.general_status === 'confirmed'"
                    @click="openRequestRebookConfirmation"
                    variant="outline"
                    class="text-yellow-600 border-yellow-500 hover:bg-yellow-50 hover:text-yellow-600"
                >
                    Rebook Reservation
                </Button>

                <Button
                    v-if="
                        reservation.general_status === 'pending' ||
                        reservation.general_status === 'confirmed'
                    "
                    @click="openRequestCancelConfirmation"
                    variant="outline"
                    class="text-red-600 border-red-500 hover:bg-red-50 hover:text-red-600"
                >
                    Cancel Reservation
                </Button>
            </CardFooter>
        </Card>

        <Alert
            :open="requestEditConfirmation"
            @update:open="requestEditConfirmation = $event"
            :onConfirm="() => requestModify('edit')"
            title="Are you sure you want to edit the reservation?"
            description="A code will be sent to your email for confirmation."
            confirm-label="Confirm"
            severity="danger"
        />
        <Alert
            :open="requestCancelConfirmation"
            @update:open="requestCancelConfirmation = $event"
            :onConfirm="() => requestModify('cancel')"
            title="Are you sure you want to cancel the reservation?"
            description="A code will be sent to your email for confirmation."
            confirm-label="Confirm"
            severity="danger"
        />
        <Alert
            :open="requestRebookConfirmation"
            @update:open="requestRebookConfirmation = $event"
            :onConfirm="() => requestModify('rebook')"
            title="Are you sure you want to rebook the reservation?"
            description="A code will be sent to your email for confirmation."
            confirm-label="Confirm"
            severity="danger"
        />
    </GuestLayout>
</template>