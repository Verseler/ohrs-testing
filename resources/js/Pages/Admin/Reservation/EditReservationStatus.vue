<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { XCircleIcon, Home, CalendarCheck, PenIcon } from "lucide-vue-next";
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
import { PageProps } from "@/types";
import { Message } from "@/Components/ui/message";
import { SidebarTrigger } from "@/Components/ui/sidebar";
import LinkButton from "@/Components/LinkButton.vue";

usePoll(10000);

type EditReservationStatusProps = {
    reservation: Reservation & {
        min_check_in_date: string;
        max_check_out_date: string;
    };
};

const { reservation } = defineProps<EditReservationStatusProps>();

const page = usePage<PageProps>();

const cancelable = computed(
    () =>
        reservation.general_status !== "checked_in" &&
        reservation.general_status !== "checked_out"
);

//In chronological order
const status: ReservationStatus[] = [
    "pending",
    "confirmed",
    "checked_in",
    "checked_out",
];

const nextStatus = computed(() => {
    const currentIndex = status.indexOf(reservation.general_status);
    return status[currentIndex + 1];
});

const bookedBy = computed(
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
        route("reservation.editAllStatus", {
            reservation_id: reservation.id,
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
                        <SidebarTrigger class="me-2" />
                    </BreadcrumbItem>

                    <BreadcrumbItem>
                        <BreadcrumbLink :href="route('dashboard')">
                            <Home class="size-4" />
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbLink :href="route('reservation.list')">Reservation Management</BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>Edit Reservation Status</BreadcrumbPage>
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

        <div class="px-4 py-8 2xl:max-w-4xl">
          <div class="flex justify-end items-center mb-2">
            <LinkButton
                :href="route('reservation.editStatusForm', { id: reservation.id })"
            >
                <PenIcon />
                Specific Guest
            </LinkButton>
          </div>

            <!-- Reservation Details -->
            <Card
                class="p-6 mb-6 bg-white rounded-lg border border-gray-200 shadow-sm"
            >
                <CardTitle class="mb-4 text-2xl font-bold text-center">
                    Reservation Status
                </CardTitle>

                <CardContent class="text-center">
                    <p class="text-lg font-medium">
                        {{ bookedBy }}
                    </p>
                    <p class="text-gray-600">
                        {{ formatDateString(reservation.min_check_in_date) }} -
                        {{ formatDateString(reservation.max_check_out_date) }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        Reservation CODE: {{ reservation.code }}
                    </p>
                    <ReservationStatusBadge :status="reservation.general_status" />
                </CardContent>
            </Card>

            <Message v-if="page.props.errors.status" severity="danger">
                {{ page.props.errors.status }}
            </Message>
            <Message v-if="page.props.errors.reverse_id" severity="danger">
                {{ page.props.errors.reservation_id }}
            </Message>

            <!-- Change Status Button -->
            <Card
                v-if="
                    reservation.general_status !== 'checked_out' &&
                    reservation.general_status !== 'canceled'
                "
                class="p-6 mb-6 text-center bg-white rounded-lg border border-gray-200 shadow-sm"
            >
                <CardTitle class="mb-4 text-2xl font-bold text-center">
                    Update Status to
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
                        Cancel
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
            confirm-label="Confirm"
        />
    </AuthenticatedLayout>
</template>
