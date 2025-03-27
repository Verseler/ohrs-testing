<script setup lang="ts">
import type { ReservationWithBeds } from "@/Pages/Admin/Reservation/reservation.types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { CalendarCheck, Home, Info, XCircle } from "lucide-vue-next";
import PageHeader from "@/Components/PageHeader.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbSeparator,
    BreadcrumbPage,
} from "@/Components/ui/breadcrumb";
import BackLink from "@/Components/BackLink.vue";
import { onMounted, ref, watch } from "vue";
import type { PageProps } from "@/types";
import { toast } from "vue-sonner";
import ReservationOverview from "@/Pages/Admin/WaitingList/Partials/ReservationOverview.vue";
import AssignGuestList from "@/Pages/Admin/WaitingList/Partials/AssignGuestList.vue";
import { Bed } from "@/Pages/Admin/Room/room.types";
import { Message } from "@/Components/ui/message";
import { Button } from "@/Components/ui/button";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { usePoll } from "@inertiajs/vue3";

usePoll(5000);

type GuestBedAssignmentProps = {
    reservation: ReservationWithBeds;
    availableBeds: Bed[];
};

const { reservation, availableBeds } = defineProps<GuestBedAssignmentProps>();

const page = usePage<PageProps>();

const cancelConfirmation = ref(false);

function showCancelConfirmation() {
    cancelConfirmation.value = true;
}

function cancelReservation() {
    router.put(route("reservation.cancel", { id: reservation.id }));
}

// Display flash success or error message as sonner or toast
onMounted(() => {
    if (page.props.flash.success) {
        toast.success(page.props.flash.success, {
            style: {
                background: "#22c55e",
                color: "white",
            },
            position: "top-center",
        });

        setTimeout(() => {
            page.props.flash.success = null;
        }, 300);
    }
});

watch(
    () => page.props.flash.error,
    () => {
        if (page.props.flash.error) {
            toast.error(page.props.flash.error, {
                style: {
                    background: "#ef4444",
                    color: "white",
                },
                position: "top-center",
            });

            setTimeout(() => {
                page.props.flash.error = null;
            }, 300);
        }
    }
);
</script>

<template>
    <Head title="Bed Assignment" />

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
        <div class="flex max-w-6xl gap-6">
            <AssignGuestList
                :reservation="reservation"
                :availableBeds="availableBeds"
                class="flex-1"
            />
            <div>
                <ReservationOverview :reservation="reservation" />

                <Message
                    severity="info"
                    class="flex items-center max-w-lg mt-6 mb-2 text-sm gap-x-2"
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
