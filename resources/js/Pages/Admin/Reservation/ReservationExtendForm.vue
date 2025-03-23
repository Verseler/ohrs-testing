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
import { Head, useForm, usePage } from "@inertiajs/vue3";
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
    formatDateString,
    getDaysDifference,
} from "@/lib/utils";
import { computed, ref, watch } from "vue";
import StatusBadge from "@/Components/StatusBadge.vue";
import { Separator } from "@/Components/ui/separator";
import { Label } from "@/Components/ui/label";
import { Button } from "@/Components/ui/button";
import DatePicker from "@/Components/DatePicker.vue";
import { InputError } from "@/Components/ui/input";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { Badge } from "@/Components/ui/badge";
import { SharedData } from "@/types";
import { toast } from "vue-sonner";

type ReservationExtendFormProps = {
    reservation: Reservation;
};

const { reservation } = defineProps<ReservationExtendFormProps>();

const page = usePage<SharedData>();

const form = useForm({
    reservation_id: reservation.id,
    new_check_out_date: new Date(reservation.check_out_date),
});

const additionalDays = computed(() => {
    return getDaysDifference(
        new Date(reservation.check_out_date),
        form.new_check_out_date
    );
});

const additionalCharge = computed(() => {
    return reservation.daily_rate * additionalDays.value;
});

const bookBy = computed(() => {
    return `${reservation.first_name}
    ${reservation?.middle_initial ? reservation.middle_initial + "." : ""}
    ${reservation.last_name}`;
});

function extendByDays(days: number) {
    const currentCheckout = new Date(reservation.check_out_date);
    const newDate = new Date(currentCheckout);
    newDate.setDate(newDate.getDate() + days);
    const newCheckoutDate = new Date(newDate.toISOString().split("T")[0]);

    form.new_check_out_date = newCheckoutDate;
}

//Confirmation Dialog
const confirmation = ref(false);

function showConfirmation() {
    confirmation.value = true;
}

function submitExtendReservation() {
    form.post(route("reservation.extend", { id: reservation.id }));
}

// Display flash success or error message as sonner or toast
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
    <Head title="Extend Reservation" />

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
                        <BreadcrumbPage>Extend Reservation</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink
                :href="route('reservation.show', { id: reservation.id })"
            />
        </div>

        <PageHeader>
            <template #icon><CalendarCheck /></template>
            <template #title>Extend Reservation</template>
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
                                <p class="text-muted-foreground">Book By</p>
                                <p class="font-medium">
                                    {{ bookBy }}
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
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <DatePicker
                                    v-model="form.new_check_out_date"
                                    :min-value="
                                        new Date(reservation.check_out_date)
                                    "
                                    calendar-class="left-5"
                                />
                                <div class="flex items-center gap-2">
                                    <Button
                                        @click="extendByDays(1)"
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                    >
                                        +1 Day
                                    </Button>
                                    <Button
                                        @click="extendByDays(2)"
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                    >
                                        +2 Days
                                    </Button>
                                    <Button
                                        @click="extendByDays(3)"
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                    >
                                        +3 Days
                                    </Button>
                                </div>
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
                                    <span class="font-medium"
                                        >Total Additional Charge</span
                                    >
                                    <span class="font-bold"
                                        >₱{{ formatCurrency(additionalCharge) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- New Balance -->
                        <div
                            v-if="additionalDays > 0"
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
                                    <span>Additional Charge</span>
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

                        <div v-if="additionalDays > 0">
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
