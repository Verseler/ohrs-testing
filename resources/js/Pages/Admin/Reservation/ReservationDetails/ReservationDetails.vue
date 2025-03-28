<script setup lang="ts">
import type {
    ReservationStatus,
    ReservationWithBeds,
} from "@/Pages/Admin/Reservation/reservation.types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    CalendarCheck,
    CalendarPlus,
    Check,
    CreditCard,
    History,
    Home,
    Pencil,
} from "lucide-vue-next";
import PageHeader from "@/Components/PageHeader.vue";
import { Head } from "@inertiajs/vue3";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbSeparator,
    BreadcrumbPage,
} from "@/Components/ui/breadcrumb";
import BackLink from "@/Components/BackLink.vue";
import StatusBadge from "@/Components/StatusBadge.vue";
import ReservationInformation from "@/Pages/Admin/Reservation/ReservationDetails/Partials/ReservationInformation.vue";
import GuestInformation from "@/Pages/Admin/Reservation/ReservationDetails/Partials/GuestInformation.vue";
import ReservedBeds from "@/Pages/Admin/Reservation/ReservationDetails/Partials/ReservedBeds.vue";
import LinkButton from "@/Components/LinkButton.vue";
import ReservationCode from "./Partials/ReservationCode.vue";
import { onMounted } from "vue";
import { usePoll } from "@inertiajs/vue3";
import { showError, showSuccess } from "@/Composables/useFlash";

usePoll(5000);

type ReservationDetailsProps = {
    reservation: ReservationWithBeds;
    canExempt: boolean;
};

const { reservation, canExempt } = defineProps<ReservationDetailsProps>();

onMounted(() => {
    showSuccess();
    showError();
});
</script>

<template>
    <Head title="Reservation Details" />

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
                        <BreadcrumbLink :href="route('reservation.list')">
                            Reservation Management
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>Reservation Details</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink :href="route('reservation.list')" />
        </div>

        <PageHeader>
            <template #icon><CalendarCheck /></template>
            <template #title>Reservation Details</template>
        </PageHeader>

        <div class="max-w-6xl">
            <div class="flex flex-col gap-3">
                <!-- Header with status badge -->
                <div>
                    <div class="flex items-center gap-4">
                        <ReservationCode>
                            {{ reservation.reservation_code }}
                        </ReservationCode>

                        <StatusBadge :status="reservation.status" />
                    </div>

                    <div
                        class="flex mt-4 gap-x-2"
                        v-if="
                            reservation.status !== 'canceled' &&
                            reservation.status !== 'pending' &&
                            reservation.status !== 'checked_out'
                        "
                    >
                        <!-- Input or record a payment -->
                        <LinkButton
                            v-if="reservation.remaining_balance > 0"
                            class="flex-1"
                            :href="
                                route('reservation.paymentForm', reservation.id)
                            "
                        >
                            <CreditCard class="mr-1" />
                            Record Payment
                        </LinkButton>

                        <!-- View Payment History -->
                        <LinkButton
                            class="flex-1 bg-yellow-500 hover:bg-yellow-600"
                            :href="
                                route(
                                    'reservation.paymentHistory',
                                    reservation.id
                                )
                            "
                        >
                            <History class="mr-1" />
                            Payment History
                        </LinkButton>

                        <!-- Payment Exemption -->
                        <LinkButton
                            v-if="canExempt"
                            class="flex-1 bg-violet-500 hover:bg-violet-600"
                            :href="
                                route(
                                    'reservation.exemptPaymentForm',
                                    reservation.id
                                )
                            "
                        >
                            <CreditCard class="mr-1" />
                            Payment Exemption
                        </LinkButton>

                        <!-- Extend a reservation -->
                        <LinkButton
                            class="flex-1 bg-red-500 hover:bg-red-600"
                            :href="
                                route('reservation.extendForm', reservation.id)
                            "
                        >
                            <CalendarPlus class="mr-1" />
                            Extend Reservation
                        </LinkButton>

                        <!-- Edit a bed assignment -->
                        <LinkButton
                            class="flex-1 bg-blue-500 hover:bg-blue-600"
                            :href="
                                route(
                                    'reservation.editAssignBedForm',
                                    reservation.id
                                )
                            "
                        >
                            <Pencil class="mr-1" />
                            Bed Assignment
                        </LinkButton>

                        <!-- Change reservation status -->
                        <LinkButton
                            class="flex-1 bg-pink-500 hover:bg-pink-600"
                            :href="
                                route(
                                    'reservation.editStatusForm',
                                    reservation.id
                                )
                            "
                        >
                            <Pencil class="mr-1" />
                            Change Status
                        </LinkButton>
                    </div>

                    <!-- Change reservation status -->
                    <div
                        v-if="reservation.status === 'pending'"
                        class="flex justify-end"
                    >
                        <LinkButton
                            class="bg-primary-500 hover:bg-primary-600"
                            :href="
                                route(
                                    'reservation.assignBedsForm',
                                    reservation.id
                                )
                            "
                        >
                            <Check class="mr-1" /> Confirm Reservation
                        </LinkButton>
                    </div>
                </div>

                <!-- Main content -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <ReservationInformation :reservation="reservation" />

                    <GuestInformation :reservation="reservation" />

                    <!-- Don't show reserved beds if reservation is pending, cancelled or checked_out -->
                    <ReservedBeds
                        v-if="(['confirmed', 'checked_in'] as ReservationStatus[]).includes(reservation.status)"
                        :reservation="reservation"
                    />

                    <Card>
                        <CardHeader>
                            <CardTitle>Purpose of Stay</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p
                                v-if="reservation.purpose_of_stay"
                                class="font-medium"
                            >
                                {{ reservation.purpose_of_stay }}
                            </p>
                            <p v-else class="text-xs italic text-neutral-500">
                                Not provided
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
