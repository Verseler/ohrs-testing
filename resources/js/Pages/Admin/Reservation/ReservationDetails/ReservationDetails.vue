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
import { Head } from "@inertiajs/vue3";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbSeparator,
    BreadcrumbPage,
} from "@/Components/ui/breadcrumb";
import BackLink from "@/Components/BackLink.vue";
import ReservationInformation from "@/Pages/Admin/Reservation/ReservationDetails/Partials/ReservationInformation.vue";
import GuestInformation from "@/Pages/Admin/Reservation/ReservationDetails/Partials/GuestInformation.vue";
import ReservedBeds from "@/Pages/Admin/Reservation/ReservationDetails/Partials/ReservedBeds.vue";
import LinkButton from "@/Components/LinkButton.vue";
import ReservationCode from "./Partials/ReservationCode.vue";
import { onMounted } from "vue";
import { usePoll } from "@inertiajs/vue3";
import { showError, showSuccess } from "@/Composables/useFlash";
import { SidebarTrigger } from "@/Components/ui/sidebar";

usePoll(5000);

type ReservationDetailsProps = {
    reservation: ReservationWithBeds & {
        confirmed_count: number;
        checked_in_count: number;
        checked_out_count: number;
        canceled_count: number;
    };
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
                        <SidebarTrigger class="me-2" />
                    </BreadcrumbItem>

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
                    <div class="flex gap-4 items-center">
                        <ReservationCode>
                            {{ reservation.code }}
                        </ReservationCode>
                    </div>

                    <div class="flex flex-wrap gap-2 justify-end mt-4">
                        <!-- Change reservation status -->

                        <LinkButton
                        v-if="
                                reservation.confirmed_count > 0 ||
                                reservation.checked_in_count > 0
                            "
                            class="flex-1 bg-pink-500 hover:bg-pink-600"
                            :href="
                                route(
                                    'reservation.editAllStatusForm',
                                    reservation.id
                                )
                            "
                        >
                            <Pencil class="mr-1" />
                            Change Status
                        </LinkButton>

                        <!-- Payment Exemption -->
                        <LinkButton
                            v-if="canExempt"
                            class="flex-1 max-w-sm bg-violet-500 hover:bg-violet-600"
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

                        <!-- Input or record a payment -->
                        <LinkButton
                            v-if="reservation.remaining_balance > 0"
                            class="flex-1 max-w-sm"
                            :href="
                                route('reservation.paymentForm', reservation.id)
                            "
                        >
                            <CreditCard class="mr-1" />
                            Record Payment
                        </LinkButton>

                        <!-- View Payment History -->
                        <LinkButton
                            class="flex-1 max-w-sm bg-yellow-500 hover:bg-yellow-600"
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

                        <!-- Update reservation checkout -->
                        <LinkButton
                            v-if="reservation.confirmed_count > 0 ||
                                reservation.checked_in_count > 0"
                            class="flex-1 bg-red-500 hover:bg-red-600"
                            :href="
                                route(
                                    'reservation.updateCheckoutForm',
                                    reservation.id
                                )
                            "
                        >
                            <CalendarPlus class="mr-1" />
                            Update Checkout Date
                        </LinkButton>

                        <!-- Edit a bed assignment -->
                        <LinkButton
                            v-if="reservation.confirmed_count > 0 ||
                                reservation.checked_in_count > 0"
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
                    </div>
                </div>

                <!-- Main content -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                   <ReservationInformation :reservation="reservation" />

                    <GuestInformation :reservation="reservation" />

                    <ReservedBeds
                        v-if="reservation.reserved_beds_with_guests && reservation.reserved_beds_with_guests.length > 0"
                        :reservation="reservation"
                        class="lg:col-span-2"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
