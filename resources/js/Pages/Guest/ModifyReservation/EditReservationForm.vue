<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Label } from "@/Components/ui/label";
import type {
    ReservationWithBeds,
    StayDetails,
} from "@/Pages/Admin/Reservation/reservation.types";
import { Head, router, useForm } from "@inertiajs/vue3";
import ReservationOverview from "@/Pages/Admin/WaitingList/Partials/ReservationOverview.vue";
import { InputDate } from "@/Components/ui/input";
import GenderBadge from "@/Components/GenderBadge.vue";
import { formatDate } from "@/lib/utils";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";

import { ref } from "vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";

type EditReservationFormProps = {
    reservation: ReservationWithBeds & {
        stay_details: StayDetails[];
    };
};

const { reservation } = defineProps<EditReservationFormProps>();

const form = useForm({
    reservation_id: reservation.id,
    stay_details: JSON.parse(JSON.stringify(reservation.stay_details)), //copy only the value not reference
});

function cancel() {
    router.visit(route("landingPage"), {
        replace: true,
    });
}

function submit() {
    form.put(route("reservation.edit"));
}

const confirmation = ref(false);

function showConfirmation() {
    confirmation.value = true;
}
</script>

<template>
    <Head title="Edit Reservation" />

    <GuestLayout>
        <h1
            class="container mx-auto mt-8 mb-4 text-3xl font-bold tracking-tight max-w-7xl"
        >
            Edit Reservation
        </h1>

        <div class="container flex items-start gap-4 mx-auto max-w-7xl">
            <!-- Guest List -->
            <form @submit.prevent="showConfirmation" class="flex-1 space-y-4">
                <Card
                    v-for="(stayDetails, index) in form.stay_details"
                    :key="stayDetails.id"
                >
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">
                                {{ stayDetails.guest.first_name }}
                                {{ stayDetails.guest.last_name }}
                                <GenderBadge
                                    :gender="stayDetails.guest.gender"
                                    class="ml-2"
                                />
                            </CardTitle>
                            <Badge variant="outline"
                                >Guest {{ index + 1 }}</Badge
                            >
                        </div>
                    </CardHeader>

                    <CardContent class="pt-6">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="check-in-date">Check-in Date</Label>
                                <InputDate
                                    v-model="stayDetails.check_in_date"
                                    :min="formatDate(new Date())"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="check-out-date"
                                    >Check-out Date</Label
                                >
                                <InputDate
                                    v-model="stayDetails.check_out_date"
                                    :min="stayDetails.check_in_date"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3">
                    <Button
                        variant="outline"
                        type="button"
                        @click="cancel"
                        size="lg"
                        class="w-24 px-4"
                        >Cancel</Button
                    >
                    <Button type="submit" class="w-44" size="lg"
                        >Save Changes</Button
                    >
                </div>
            </form>

            <ReservationOverview :reservation="reservation" />

            <Alert
                :open="confirmation"
                @update:open="confirmation = $event"
                :onConfirm="submit"
                title="Are you sure you want to save changes?"
                description="A code will be sent to your email for confirmation."
                confirm-label="Confirm"
            />
        </div>
    </GuestLayout>
</template>
