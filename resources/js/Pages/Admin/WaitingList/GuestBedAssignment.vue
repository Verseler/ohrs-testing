<script setup lang="ts">
import type { GuestAssignment, ReservationWithBeds } from "@/Pages/Admin/Reservation/reservation.types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { CalendarCheck, Home, XCircleIcon } from "lucide-vue-next";
import PageHeader from "@/Components/PageHeader.vue";
import { Head, router } from "@inertiajs/vue3";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbSeparator,
    BreadcrumbPage,
} from "@/Components/ui/breadcrumb";
import BackLink from "@/Components/BackLink.vue";
import { ref } from "vue";
import AssignGuestList from "@/Pages/Admin/WaitingList/Partials/AssignGuestList.vue";
import { Bed } from "@/Pages/Admin/Room/room.types";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { Separator } from "@/Components/ui/separator";
import { SidebarTrigger } from "@/Components/ui/sidebar";
import ReservationOverview from "@/Pages/Admin/WaitingList/Partials/ReservationOverview.vue";
import { Button } from "@/Components/ui/button";

type GuestBedAssignmentProps = {
    reservation: ReservationWithBeds & { guestAssignment: GuestAssignment[] };
    availableBeds: Record<number, Bed[]>;
};

const { reservation, availableBeds } = defineProps<GuestBedAssignmentProps>();

const cancelConfirmation = ref(false);

function showCancelConfirmation() {
    cancelConfirmation.value = true;
}

function cancelReservation() {
    router.put(route("reservation.cancel", { id: reservation.id }));
}
</script>

<template>
    <Head title="Bed Assignment" />

    <AuthenticatedLayout>
        <div class="flex justify-between min-h-12">
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
                        <BreadcrumbLink
                            :href="route('reservation.waitingList')"
                        >
                            Waiting List
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>Bed Assignment</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink :href="route('reservation.waitingList')" />
        </div>

        <PageHeader>
            <template #icon><CalendarCheck /></template>
            <template #title>Bed Assignment</template>
        </PageHeader>

        <!-- Main content -->
        <div class="flex flex-col gap-6 max-w-7xl xl:flex-row">
            <AssignGuestList
                :reservation="reservation"
                :availableBeds="availableBeds"
                class="flex-1"
            />

            <Separator class="my-4 md:hidden" />

            <div class="mx-auto max-w-md">
                <ReservationOverview :reservation="reservation" />

                <Button
                    @click="showCancelConfirmation"
                    type="button"
                    variant="outline"
                    class="mt-4 w-full text-base text-red-500 border-red-500 min-h-12 hover:bg-red-50 hover:text-red-600"
                >
                    <XCircleIcon />
                    Cancel Reservation
                </Button>
            </div>

            <Alert
                :open="cancelConfirmation"
                @update:open="cancelConfirmation = $event"
                :onConfirm="cancelReservation"
                title="Are you sure you want to cancel reservation?"
                description="This action is unchangeable and will cancel the reservation."
                severity="danger"
                confirm-label="Confirm"
            />
        </div>
    </AuthenticatedLayout>
</template>
