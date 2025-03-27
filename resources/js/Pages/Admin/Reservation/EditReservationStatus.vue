<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, router } from "@inertiajs/vue3";
import { XCircleIcon, Home, CalendarCheck } from "lucide-vue-next";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import PageHeader from "@/Components/PageHeader.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Button } from "@/Components/ui/button";
import BackLink from "@/Components/BackLink.vue";
import type {
    Reservation,
    ReservationStatus,
} from "@/Pages/Admin/Reservation/reservation.types";
import { formatDateString } from "@/lib/utils";
import { Card, CardContent, CardTitle } from "@/Components/ui/card";
import ReservationStatusBadge from "@/Components/ReservationStatusBadge.vue";
import StatusButton from "@/Pages/Admin/Reservation/ReservationDetails/Partials/StatusButton.vue";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { usePoll } from "@inertiajs/vue3";

usePoll(10000);

type EditReservationStatusProps = {
    reservation: Reservation;
};

const { reservation } = defineProps<EditReservationStatusProps>();

const cancelable = computed(
    () =>
        reservation.status !== "checked_in" &&
        reservation.status !== "checked_out"
);

//In chronological order
const status: ReservationStatus[] = [
    "pending",
    "confirmed",
    "checked_in",
    "checked_out",
];

const nextStatus = computed(() => {
    const currentIndex = status.indexOf(reservation.status);
    return status[currentIndex + 1];
});

const bookBy = computed(
    () => `${reservation.first_name} ${reservation.last_name}`
);

//Confirmation Dialog
const confirmation = ref(false);
const cancelConfirmation = ref(false);

function showConfirmation() {
    confirmation.value = true;
}

function showCancelConfirmation() {
    cancelConfirmation.value = true;
}

function changeStatus(newStatus: ReservationStatus) {
    router.put(
        route("reservation.cancel", {
            id: reservation.id,
            status: newStatus,
        })
    );
}

function cancelStatus() {
    router.put(route("reservation.cancel", { id: reservation.id }));
}
</script>

<template>
    <Head title="Reservation Status" />

    <AuthenticatedLayout>
        <div class="flex justify-between">
            <Breadcrumb>
                <BreadcrumbList>
                    <BreadcrumbItem>
                        <BreadcrumbLink :href="route('dashboard')">
                            <Home class="size-4" />
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>Office Management</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink
                :href="route('reservation.show', { id: reservation.id })"
            />
        </div>

        <PageHeader>
            <template #icon><CalendarCheck /></template>
            <template #title>Edit Reservation Status</template>
        </PageHeader>

        <div class="max-w-2xl px-4 py-8">
            <!-- Reservation Details -->
            <Card
                class="p-6 mb-6 bg-white border border-gray-200 rounded-lg shadow-sm"
            >
                <CardTitle class="mb-4 text-2xl font-bold text-center">
                    Reservation Status
                </CardTitle>

                <CardContent class="text-center">
                    <p class="text-lg font-medium">
                        {{ bookBy }}
                    </p>
                    <p class="text-gray-600">
                        {{ formatDateString(reservation.check_in_date) }} -
                        {{ formatDateString(reservation.check_out_date) }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        Reservation CODE: {{ reservation.reservation_code }}
                    </p>
                    <ReservationStatusBadge :status="reservation.status" />
                </CardContent>
            </Card>

            <!-- Change Status Button -->
            <Card
                v-if="
                    reservation.status !== 'checked_out' &&
                    reservation.status !== 'canceled'
                "
                class="p-6 mb-6 text-center bg-white border border-gray-200 rounded-lg shadow-sm"
            >
                <CardTitle class="mb-4 text-2xl font-bold text-center">
                    Update Status
                </CardTitle>

                <CardContent>
                    <StatusButton
                        :nextStatus="nextStatus"
                        @click="showConfirmation()"
                    />

                    <p
                        v-if="cancelable"
                        class="my-2 text-center text-neutral-500"
                    >
                        or
                    </p>

                    <Button
                        v-if="cancelable"
                        variant="outline"
                        size="lg"
                        class="w-full h-12 text-lg text-red-500 border-red-500 hover:bg-red-50 hover:text-red-600"
                        @click="showCancelConfirmation()"
                    >
                        <XCircleIcon class="mr-1 !size-5" />
                        Cancel Reservation
                    </Button>

                    <div class="mt-3 text-sm text-center text-gray-500">
                        Click a button above to update the reservation status
                    </div>
                </CardContent>
            </Card>
        </div>

        <Alert
            :open="confirmation"
            @update:open="confirmation = $event"
            :onConfirm="() => changeStatus(nextStatus)"
            title="Are you sure you want to change the reservation status?"
            description="This action cannot be undone. Please confirm your action to update the reservation status."
            confirm-label="Confirm"
        />

        <Alert
            :open="cancelConfirmation"
            @update:open="cancelConfirmation = $event"
            :onConfirm="cancelStatus"
            title="Are you sure you want to cancel the reservation?"
            description="This action cannot be undone. Please confirm your action to cancel the reservation."
            severity="danger"
            confirm-label="Confirm Cancel"
        />
    </AuthenticatedLayout>
</template>
