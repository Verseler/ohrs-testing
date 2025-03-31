<script setup lang="ts">
import type { ReservationWithBeds } from "@/Pages/Admin/Reservation/reservation.types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { CalendarCheck, Home, Info, XCircle } from "lucide-vue-next";
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
import { onMounted, ref } from "vue";
import ReservationOverview from "@/Pages/Admin/WaitingList/Partials/ReservationOverview.vue";
import AssignGuestList from "@/Pages/Admin/WaitingList/Partials/AssignGuestList.vue";
import { Bed } from "@/Pages/Admin/Room/room.types";
import { Message } from "@/Components/ui/message";
import { Button } from "@/Components/ui/button";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { usePoll } from "@inertiajs/vue3";
import { showSuccess } from "@/Composables/useFlash";
import { Separator } from "@/Components/ui/separator";
import { SidebarTrigger } from "@/Components/ui/sidebar";

usePoll(15000);

type GuestBedAssignmentProps = {
    reservation: ReservationWithBeds;
    availableBeds: Bed[];
};

const { reservation, availableBeds } = defineProps<GuestBedAssignmentProps>();

const cancelConfirmation = ref(false);

function showCancelConfirmation() {
    cancelConfirmation.value = true;
}

function cancelReservation() {
    router.put(route("reservation.cancel", { id: reservation.id }));
}

onMounted(() => showSuccess());
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
        <div class="flex flex-col max-w-6xl gap-6 md:flex-row">
            <AssignGuestList
                :reservation="reservation"
                :availableBeds="availableBeds"
                class="flex-1"
            />

            <Separator class="my-4 md:hidden" />

            <div class="max-w-md mx-auto">
                <ReservationOverview :reservation="reservation" />

                <Message
                    severity="info"
                    class="flex items-center mt-6 mb-2 text-sm gap-x-2"
                >
                    <Info class="size-6" />
                    If ever there is no available beds or beds are not enough
                    for all guests, you can cancel the reservation if needed.
                </Message>

                <Button
                    @click="showCancelConfirmation"
                    type="button"
                    variant="outline"
                    class="w-full text-base text-red-500 border-red-500 min-h-12 hover:bg-red-50 hover:text-red-600"
                >
                    <XCircle />
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
