<script setup lang="ts">
import type { ReservationWithBeds } from "@/Pages/Admin/Reservation/reservation.types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { CalendarCheck, Home } from "lucide-vue-next";
import PageHeader from "@/Components/PageHeader.vue";
import { Head, usePage } from "@inertiajs/vue3";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbSeparator,
    BreadcrumbPage,
} from "@/Components/ui/breadcrumb";
import BackLink from "@/Components/BackLink.vue";
import { onMounted } from "vue";
import { SharedData } from "@/types";
import { toast } from "vue-sonner";
import ReservationOverview from "@/Pages/Admin/WaitingList/Partials/ReservationOverview.vue";
import AssignGuestList from "@/Pages/Admin/WaitingList/Partials/AssignGuestList.vue";
import { Bed } from "@/Pages/Admin/Room/room.types";

type GuestBedAssignmentProps = {
    reservation: ReservationWithBeds;
    availableBeds: Bed[];
};

const { reservation, availableBeds } = defineProps<GuestBedAssignmentProps>();

const page = usePage<SharedData>();

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
</script>

<template>
    <Head title="BedAssignment" />

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
            <AssignGuestList :reservation="reservation" :availableBeds='availableBeds' class="flex-1" />
            <ReservationOverview :reservation="reservation" />
        </div>
    </AuthenticatedLayout>
</template>
