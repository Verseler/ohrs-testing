<script setup lang="ts">
import BackLink from "@/Components/BackLink.vue";
import PageHeader from "@/Components/PageHeader.vue";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { Home, Info, Pen } from "lucide-vue-next";
import type { ReservationWithBeds } from "@/Pages/Admin/Reservation/reservation.types";
import type { Bed } from "@/Pages/Admin/Room/room.types";
import { Gender, GuestBeds } from "@/Pages/Guest/guest.types";
import { computed, ref } from "vue";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Message } from "@/Components/ui/message";
import GuestDetailCard from "@/Pages/Admin/Reservation/EditBedAssignment/Partials/GuestDetailCard.vue";
import InputLabel from "@/Components/ui/input/InputLabel.vue";
import { InputError } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { SidebarTrigger } from "@/Components/ui/sidebar";
import GenderBadge from "@/Components/GenderBadge.vue";
import { roomScheduledEligibleGender } from "../../WaitingList/helpers";

type EditBedAssignmentProps = {
    reservation: ReservationWithBeds & {
        guest_beds: GuestBeds[];
    };
    availableBeds: Bed[];
};

const { reservation } = defineProps<EditBedAssignmentProps>();

const form = useForm({
    reservation_id: reservation.id,
    selected_guest_id: null,
    selected_bed_id: null,
});

const selectedGuest = computed<GuestBeds | null>(() => {
    const guest = reservation.guest_beds.find(
        (guest_bed) => guest_bed.guest_id == form.selected_guest_id
    );

    return guest ?? null;
});

// confirmation dialog
const confirmation = ref(false);

function showConfirmation() {
    confirmation.value = true;
}

function submit() {
    form.put(route("reservation.editAssignBed"));
}
</script>

<template>
    <Head title="Edit Bed Assignment" />

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
                        <BreadcrumbLink :href="route('reservation.list')">
                            Reservation Management
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbLink :href="route('reservation.list')">
                            Reservation
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>Bed Assignment</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink
                :href="route('reservation.show', { id: reservation.id })"
            />
        </div>

        <PageHeader>
            <template #icon><Pen /></template>
            <template #title>Bed Assignment</template>
        </PageHeader>

        <!-- Main content -->
        <div class="grid max-w-6xl grid-cols-1 gap-6 md:grid-cols-3">
            <form @submit.prevent="showConfirmation" class="col-span-2">
                <Message severity="info" class="flex items-center mb-3 gap-x-2">
                    <Info class="size-4" />
                    Please select a guest to update their bed assignment
                </Message>

                <div>
                    <InputLabel> Select Guest </InputLabel>
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
                                    v-for="guestBed in reservation.guest_beds"
                                    :value="guestBed.guest_id"
                                    :key="guestBed.guest_id"
                                >
                                    {{ guestBed.guest.first_name }}
                                    {{ guestBed.guest.last_name }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <InputError v-if="!form.errors.selected_guest_id">
                        {{ form.errors.selected_guest_id }}
                    </InputError>
                </div>

                <div class="my-2">
                    <InputLabel v-if="form.selected_guest_id">
                        Select new bed
                    </InputLabel>
                    <Select
                        v-if="form.selected_guest_id"
                        v-model="form.selected_bed_id"
                    >
                        <SelectTrigger
                            class="h-12 rounded-sm shadow-none border-primary-700"
                            :invalid="!!form.errors.selected_bed_id"
                        >
                            <SelectValue placeholder="Select a new bed" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>Beds</SelectLabel>
                                <SelectItem
                                    v-for="bed in availableBeds"
                                    :value="bed.id"
                                    :key="bed.id"
                                >
                                    <div class="flex items-center justify-between">
                                        <div>
                                            {{ bed.room.name }}
                                            {{ bed.name }}
                                        </div>
                                        <GenderBadge
                                            v-if="
                                                roomScheduledEligibleGender(bed)
                                            "
                                            :gender="
                                                roomScheduledEligibleGender(bed)
                                            "
                                        />
                                        <GenderBadge
                                            v-else
                                            :gender="bed.room.eligible_gender"
                                        />
                                    </div>
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <InputError v-if="!!form.errors.selected_bed_id">
                        {{ form.errors.selected_bed_id }}
                    </InputError>
                </div>

                <Button
                    v-if="selectedGuest"
                    type="submit"
                    class="w-full mt-2 text-base min-h-12"
                >
                    Submit
                </Button>
            </form>

            <GuestDetailCard
                v-if="selectedGuest"
                class="col-span-1 min-w-80"
                :firstName="selectedGuest.guest.first_name"
                :lastName="selectedGuest.guest.last_name"
                :gender="selectedGuest.guest.gender as Gender"
                :roomName="selectedGuest.bed.room.name"
                :bedName="selectedGuest.bed.name"
            />
        </div>

        <Alert
            :open="confirmation"
            @update:open="confirmation = $event"
            :onConfirm="submit"
            title="Are you sure you want to save changes?"
            description="Confirming the changes will update the guest's bed assignment. Please confirm your action to proceed."
        />
    </AuthenticatedLayout>
</template>
