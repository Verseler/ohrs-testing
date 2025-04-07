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
import StatusBadge from "@/Components/StatusBadge.vue";
import { Separator } from "@/Components/ui/separator";
import { Label } from "@/Components/ui/label";
import { Button } from "@/Components/ui/button";
import { InputError, InputDate } from "@/Components/ui/input";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { Badge } from "@/Components/ui/badge";
import { SidebarTrigger } from "@/Components/ui/sidebar";

type ReservationExtendFormProps = {
    reservation: Reservation;
};

const { reservation } = defineProps<ReservationExtendFormProps>();

type ExtendForm = {
    reservation_id: number;
    check_in_date: string | Date;
    new_check_out_date: string | Date;
};

const form = useForm<ExtendForm>({
    reservation_id: reservation.id,
    check_in_date: reservation.check_in_date,
    new_check_out_date: reservation.check_out_date,
});

const checkInDatePlusOne = computed(() => {
    const checkInDate = new Date(reservation.check_in_date);
    checkInDate.setDate(checkInDate.getDate() + 1);

    return checkInDate;
});

const lessThenOldCheckOut = computed(
    () => reservation.check_out_date > form.new_check_out_date
);

const additionalDays = computed(() => {
    return getDaysDifference(
        new Date(reservation.check_out_date),
        form.new_check_out_date
    );
});

const additionalCharge = computed(() => {
    return reservation.daily_rate * additionalDays.value;
});

const bookedBy = computed(() => {
    const firstName = reservation.first_name || "N/A";
    const middleInitial = reservation.middle_initial
        ? reservation.middle_initial + "."
        : "";
    const lastName = reservation.last_name || "N/A";

    return `${firstName} ${middleInitial} ${lastName}`;
});

function extendByDays(days: number) {
    const currentCheckout = new Date(reservation.check_out_date);
    const newDate = new Date(currentCheckout);

    newDate.setDate(newDate.getDate() + days);
    form.new_check_out_date = formatDate(newDate) ?? newDate;
}

function reduceByDays(days: number) {
    const currentCheckout = new Date(reservation.check_out_date);
    const newDate = new Date(currentCheckout);
    newDate.setDate(newDate.getDate() - days);

    if (newDate <= new Date(reservation.check_in_date)) return;

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
    <Head title="Update Reservation Checkout" />

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
            <template #title>Update Reservation Checkout</template>
        </PageHeader>

        <div class="max-w-2xl">
            <Card>
                <CardHeader>
                    <CardDescription>
                        Extend the stay for reservation #{{
                            reservation.reservation_code
                        }}
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <!-- Current Reservation Summary -->
                    <div class="p-4 rounded-lg bg-muted">
                        <h3 class="mb-3 font-medium">Current Reservation</h3>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-muted-foreground">Booked By</p>
                                <p class="font-medium">
                                    {{ bookedBy }}
                                </p>
                            </div>
                            <div>
                                <p class="text-muted-foreground">Status</p>
                                <StatusBadge :status="reservation.status" />
                            </div>
                            <div>
                                <p class="text-muted-foreground">
                                    Check-in Date
                                </p>
                                <p class="font-medium">
                                    {{
                                        formatDateString(
                                            reservation.check_in_date
                                        )
                                    }}
                                </p>
                            </div>
                            <div>
                                <p class="text-muted-foreground">
                                    Current Check-out Date
                                </p>
                                <p class="font-medium">
                                    {{
                                        formatDateString(
                                            reservation.check_out_date
                                        )
                                    }}
                                </p>
                            </div>
                            <div>
                                <p>Reservation rate per day</p>
                                <span class="font-medium">
                                    ₱{{
                                        formatCurrency(reservation.daily_rate)
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <!-- Extension Form -->
                    <form @submit.prevent="showConfirmation" class="space-y-4">
                        <!-- New Check-out Date -->
                        <div class="space-y-2">
                            <Label for="new-checkout-date">
                                New Check-out Date
                            </Label>
                            <InputDate
                                v-model="form.new_check_out_date"
                                :min="checkInDatePlusOne"
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
                                <div class="flex justify-between text-sm">
                                    <span>Reservation rate per day</span>
                                    <span class="font-medium">
                                        ₱{{
                                            formatCurrency(
                                                reservation.daily_rate
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
                            v-if="
                                reservation.check_out_date !==
                                form.new_check_out_date
                            "
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

                        <div
                            v-if="
                                reservation.check_out_date !==
                                form.new_check_out_date
                            "
                        >
                            <Button
                                type="submit"
                                class="w-full text-base min-h-12"
                                :disabled="form.processing"
                            >
                                <Loader2Icon
                                    v-if="form.processing"
                                    class="w-4 h-4 mr-2 animate-spin"
                                />
                                Extend Reservation
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
