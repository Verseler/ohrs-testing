<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { Home, CalendarCheck } from "lucide-vue-next";
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
import BackLink from "@/Components/BackLink.vue";
import type {
    Reservation,
    ReservationStatus,
} from "@/Pages/Admin/Reservation/reservation.types";
import { Card, CardContent, CardTitle } from "@/Components/ui/card";
import ReservationStatusBadge from "@/Components/ReservationStatusBadge.vue";
import StatusButton from "@/Pages/Admin/Reservation/ReservationDetails/Partials/StatusButton.vue";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { usePoll } from "@inertiajs/vue3";
import { PageProps } from "@/types";
import { Message } from "@/Components/ui/message";
import { SidebarTrigger } from "@/Components/ui/sidebar";
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { InputError } from "@/Components/ui/input";
import { formatDateString } from "@/lib/utils";

usePoll(10000);

type EditReservationStatusProps = {
    reservation: Reservation;
};

const { reservation } = defineProps<EditReservationStatusProps>();

const page = usePage<PageProps>();

const form = useForm({
    reservation_id: reservation.id,
    selected_guest_id: null,
    status: undefined as ReservationStatus | undefined,
});

const selectedGuest = computed(() => {
    if (!form.selected_guest_id) return null;

    const guest = reservation.guests.find((guest) => guest.id === form.selected_guest_id);
    return guest ?? null;
});

//In chronological order
const status: ReservationStatus[] = [
    "pending",
    "confirmed",
    "checked_in",
    "checked_out",
];

const nextStatus = computed(() => {
    const currentStatus = selectedGuest.value?.stay_details.status ?? 'pending';

    const currentIndex = status.indexOf(currentStatus);
    return status[currentIndex + 1];
});

//Confirmation Dialog
const confirmation = ref(false);

function showConfirmation() {
    confirmation.value = true;
}

function changeStatus(newStatus: ReservationStatus) {
    form.status = newStatus;

    form.put(route("reservation.editStatus"));
}
</script>

<template>
    <Head title="Reservation Guest Status" />

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
                        <BreadcrumbLink :href="route('reservation.editAllStatusForm', { id: reservation.id })">Edit Reservation Status</BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>Guest Reservation Status</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink
                :href="route('reservation.editAllStatusForm', { id: reservation.id })"
            />
        </div>

        <PageHeader>
            <template #icon><CalendarCheck /></template>
            <template #title>Edit Guest Reservation Status</template>
        </PageHeader>

        <div class="px-4 py-8 2xl:max-w-4xl">
            <!-- Reservation Details -->
            <Card
                class="p-6 mb-6 bg-white rounded-lg border border-gray-200 shadow-sm"
            >
                <CardTitle class="mb-4 text-2xl font-bold text-center">
                    Reservation Status
                </CardTitle>

                <CardContent class="text-center">
                    <Select v-model="form.selected_guest_id">
                        <SelectTrigger
                            class="h-12 rounded-sm shadow-none border-primary-700"
                            :invalid="!!form.errors.selected_guest_id"
                        >
                            <SelectValue placeholder="Select a guest" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>Guests</SelectLabel>
                                <SelectItem
                                    v-for="guest in reservation.guests"
                                    :value="guest.id"
                                    :key="guest.id"
                                >
                                    {{ guest.first_name }}
                                    {{ guest.last_name }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <InputError v-if="!form.errors.selected_guest_id">
                        {{ form.errors.selected_guest_id }}
                    </InputError>


                    <p v-if="selectedGuest" class="text-gray-600">
                        {{ formatDateString(selectedGuest.stay_details.check_in_date) }} -
                        {{ formatDateString(selectedGuest.stay_details.check_out_date) }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        Reservation CODE: {{ reservation.code }}
                    </p>
                    <ReservationStatusBadge v-if="selectedGuest" :status="selectedGuest.stay_details.status" />
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
                    selectedGuest &&
                    selectedGuest.stay_details.status !== 'checked_out' &&
                    selectedGuest.stay_details.status !== 'canceled'
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
    </AuthenticatedLayout>
</template>
