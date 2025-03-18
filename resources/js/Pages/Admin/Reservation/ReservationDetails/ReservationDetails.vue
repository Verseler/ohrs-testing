<script setup lang="ts">
import type { ReservationWithBeds } from "@/Pages/Admin/Reservation/reservation.types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    CalendarCheck,
    CalendarPlus,
    CreditCard,
    History,
    Home,
    Pencil,
} from "lucide-vue-next";
import PageHeader from "@/Components/PageHeader.vue";
import { Button } from "@/Components/ui/button";
import { Head, Link } from "@inertiajs/vue3";
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

type ReservationDetailsProps = {
    reservation: ReservationWithBeds;
};

const { reservation } = defineProps<ReservationDetailsProps>();
</script>

<template>
    <Head title="Reservation Details" />

    <AuthenticatedLayout>
        <div class="flex justify-between min-h-12">
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

        <div class="max-w-4xl">
            <div class="flex flex-col gap-3">
                <!-- Header with status badge -->
                <div>
                    <div class="flex items-center gap-4">
                        <ReservationCode>
                            {{ reservation.reservation_code }}
                        </ReservationCode>

                        <StatusBadge :status="reservation.status" />
                    </div>

                    <div class="flex mt-4 gap-x-2">
                        <!-- Input or record a payment -->
                        <LinkButton
                            class="flex-1"
                            v-if="reservation.remaining_balance > 0"
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
                                    'reservation.editBedAssignmentForm',
                                    reservation.id
                                )
                            "
                        >
                            <Pencil class="mr-1" />
                            Bed Assignment
                        </LinkButton>

                        <!-- Edit a bed assignment -->
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
                            Status
                        </LinkButton>
                    </div>
                </div>

                <!-- Main content -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <ReservationInformation :reservation="reservation" />

                    <GuestInformation :reservation="reservation" />

                    <ReservedBeds :reservation="reservation" />

                    <Card>
                        <CardHeader>
                            <CardTitle>Purpose of Stay</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="font-medium">
                                {{ reservation.purpose_of_stay || "-" }}
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
