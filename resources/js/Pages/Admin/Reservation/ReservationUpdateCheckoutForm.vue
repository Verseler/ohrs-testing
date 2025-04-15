<script setup lang="ts">
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbSeparator,
    BreadcrumbPage,
} from "@/Components/ui/breadcrumb";
import BackLink from "@/Components/BackLink.vue";

import { Home, CalendarCheck, Loader2Icon } from "lucide-vue-next";
import type { Reservation } from "@/Pages/Admin/Reservation/reservation.types.ts";
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PageHeader from "@/Components/PageHeader.vue";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
} from "@/Components/ui/card";
import {
    formatCurrency,
    formatDate,
    formatDateString,
    getDaysDifference,
} from "@/lib/utils";
import { computed, ref } from "vue";
import { Separator } from "@/Components/ui/separator";
import { Label } from "@/Components/ui/label";
import { Button } from "@/Components/ui/button";
import { InputError, InputDate } from "@/Components/ui/input";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { Badge } from "@/Components/ui/badge";
import { SidebarTrigger } from "@/Components/ui/sidebar";
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from "@/Components/ui/select";

type ReservationExtendFormProps = {
    reservation: Reservation;
};

const { reservation } = defineProps<ReservationExtendFormProps>();

type ExtendForm = {
    reservation_id: number;
    guest_id: number | null;
    new_check_out_date: string | Date;
};

const form = useForm<ExtendForm>({
    reservation_id: reservation?.id,
    guest_id: null,
    new_check_out_date: ''
});

const selectedGuest = computed(() => {
    if (!form.guest_id) return null;
    return reservation.guests.find(guest => guest.id === form.guest_id) || null;
});

const lessThenOldCheckOut = computed(
    () => {
        if(!selectedGuest.value) return;

        return new Date(selectedGuest.value.stay_details.check_in_date) > new Date(form.new_check_out_date)
    }
);

const additionalDays = computed(() => {
    if(!selectedGuest.value || !form.new_check_out_date) return 0;

    return getDaysDifference(
        new Date(selectedGuest.value.stay_details.check_out_date),
        new Date(form.new_check_out_date)
    );
});

const additionalCharge = computed(() => {
    if(!selectedGuest.value) return 0;

    if(selectedGuest.value.stay_details.is_exempted) return 0;

    return selectedGuest.value.stay_details.bed.price * additionalDays.value;
});

function extendByDays(days: number) {
    if(!selectedGuest.value) return;

    const currentCheckout = new Date(selectedGuest.value.stay_details.check_out_date);
    const newDate = new Date(currentCheckout);

    newDate.setDate(newDate.getDate() + days);
    form.new_check_out_date = formatDate(newDate) ?? newDate;
}

function reduceByDays(days: number) {
    if(!selectedGuest.value) return;

    const currentCheckout = new Date(selectedGuest.value.stay_details.check_out_date);
    const newDate = new Date(currentCheckout);
    newDate.setDate(newDate.getDate() - days);
    if (newDate <= new Date(selectedGuest.value.stay_details.check_in_date)) return;

    form.new_check_out_date = formatDate(newDate) ?? newDate;
}

//Confirmation Dialog
const confirmation = ref<boolean>(false);

function showConfirmation() {
    confirmation.value = true;
}

function submitExtendReservation() {
    form.post(route("reservation.updateCheckout", { id: reservation.id }));
}
</script>

<template>
    <Head title="Update Checkout Date" />

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
                        <BreadcrumbPage
                            >Update Reservation Checkout</BreadcrumbPage
                        >
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink
                :href="route('reservation.show', { id: reservation.id })"
            />
        </div>

        <PageHeader>
            <template #icon><CalendarCheck /></template>
            <template #title>Update Checkout Date</template>
        </PageHeader>

        <div class="max-w-2xl">
            <Card>
                <CardHeader>
                    <CardDescription>
                        Extend the stay for reservation #{{ reservation.code }}
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <div>
                        <Label class="mt-4"> Select Guest </Label>
                        <Select v-model="form.guest_id">
                            <SelectTrigger
                                class="h-12 mt-2 rounded-sm shadow-none border-primary-700"

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
                    </div>

                    <!-- Current Reservation Summary -->
                    <div class="p-4 rounded-lg bg-muted">


                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div v-if="selectedGuest">
                                <p class="text-muted-foreground">
                                    Check-in Date
                                </p>
                                <p class="font-medium">
                                    {{
                                        formatDateString(
                                            selectedGuest?.stay_details.check_in_date
                                        )
                                    }}
                                </p>
                            </div>
                            <div v-if="selectedGuest">
                                <p class="text-muted-foreground">
                                    Current Check-out Date
                                </p>
                                <p class="font-medium">
                                    {{
                                        formatDateString(
                                            selectedGuest?.stay_details.check_out_date
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <Separator v-if="selectedGuest" />

                    <!-- Extension Form -->
                    <form v-if="selectedGuest" @submit.prevent="showConfirmation" class="space-y-4">
                        <!-- New Check-out Date -->
                        <div class="space-y-2">
                            <Label for="new-checkout-date">
                                New Check-out Date
                            </Label>
                            <InputDate
                                v-model="form.new_check_out_date"
                                :min="selectedGuest.stay_details.check_in_date"
                                :invalid="!!form.errors.new_check_out_date"
                            />
                            <div class="grid grid-cols-3 gap-2 md:grid-cols-6">
                                <Button
                                    @click="reduceByDays(3)"
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="text-red-500"
                                >
                                    -3 Days
                                </Button>
                                <Button
                                    @click="reduceByDays(2)"
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="text-red-500"
                                >
                                    -2 Days
                                </Button>
                                <Button
                                    @click="reduceByDays(1)"
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="text-red-500"
                                >
                                    -1 Day
                                </Button>
                                <Button
                                    @click="extendByDays(1)"
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="text-green-600"
                                >
                                    +1 Day
                                </Button>
                                <Button
                                    @click="extendByDays(2)"
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="text-green-600"
                                >
                                    +2 Days
                                </Button>
                                <Button
                                    @click="extendByDays(3)"
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="text-green-600"
                                >
                                    +3 Days
                                </Button>
                            </div>
                            <InputError v-if="form.errors.new_check_out_date">
                                {{ form.errors.new_check_out_date }}
                            </InputError>
                        </div>

                        <div
                            v-if="additionalDays > 0"
                            class="p-4 space-y-3 rounded-lg bg-muted/50"
                        >
                            <div class="flex items-center justify-between">
                                <h3 class="font-medium">Extension Summary</h3>
                                <Badge
                                    variant="outline"
                                    class="border-primary-500 text-primary-500 bg-primary-50"
                                >
                                    {{ additionalDays }} additional
                                    {{ additionalDays > 1 ? "days" : "day" }}
                                </Badge>
                            </div>

                            <div class="space-y-2">
                                <div v-if="selectedGuest" class="flex justify-between text-sm">
                                    <span>Bed daily rate</span>
                                    <span class="font-medium">
                                        ₱{{
                                            formatCurrency(
                                                selectedGuest?.stay_details.bed.price
                                            )
                                        }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Additional days</span>
                                    <span class="font-medium">
                                        × {{ additionalDays }}
                                    </span>
                                </div>
                                <Separator />
                                <div class="flex justify-between">
                                    <span class="font-medium">
                                        Total Additional Charge
                                    </span>
                                    <span class="font-bold">
                                        ₱{{ formatCurrency(additionalCharge) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- New Balance -->
                        <div
                            v-if="form.new_check_out_date"
                            class="p-4 space-y-3 rounded-lg bg-muted/50"
                        >
                            <h3 class="font-medium">New Billings</h3>

                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span>Previous Remaining Balance</span>
                                    <span class="font-medium">
                                        ₱{{
                                            formatCurrency(
                                                reservation.remaining_balance
                                            )
                                        }}
                                    </span>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <span v-if="lessThenOldCheckOut">
                                        Reduction
                                    </span>
                                    <span v-else>Additional Charge</span>

                                    <span class="font-medium">
                                        ₱{{ formatCurrency(additionalCharge) }}
                                    </span>
                                </div>
                                <Separator />
                                <div class="flex justify-between">
                                    <span class="font-medium">
                                        New Remaining Balance
                                    </span>
                                    <span class="font-bold">
                                        ₱{{
                                            formatCurrency(
                                                reservation.remaining_balance +
                                                    additionalCharge
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <Button
                                type="submit"
                                class="w-full text-base min-h-12"
                                :disabled="form.processing"
                            >
                                <Loader2Icon
                                    v-if="form.processing"
                                    class="w-4 h-4 mr-2 animate-spin"
                                />
                                Update Reservation
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>

        <Alert
            :open="confirmation"
            @update:open="confirmation = $event"
            :onConfirm="submitExtendReservation"
            title="Are you sure you want to extend the reservation?"
            description="Extending the reservation will update the check-out date and billing details. Please confirm your action to proceed."
            confirm-label="Confirm"
        />
    </AuthenticatedLayout>
</template>

