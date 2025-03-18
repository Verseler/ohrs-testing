<script setup lang="ts">
import { ref, computed, watch } from "vue";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
} from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Label } from "@/Components/ui/label";
import { Badge } from "@/Components/ui/badge";
import { Separator } from "@/Components/ui/separator";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbSeparator,
    BreadcrumbPage,
} from "@/Components/ui/breadcrumb";
import BackLink from "@/Components/BackLink.vue";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/Components/ui/popover";
import { Calendar } from "@/Components/ui/calendar";
import {
    Loader2Icon,
    CalendarIcon,
    Home,
    CalendarCheck,
} from "lucide-vue-next";
import type { Reservation } from "@/Pages/Admin/Reservation/reservation.types.ts";
import { Head, useForm } from "@inertiajs/vue3";
import { formatDateString, formatCurrency } from "@/lib/utils";
import StatusBadge from "@/Components/StatusBadge.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PageHeader from "@/Components/PageHeader.vue";

type ReservationExtendFormProps = {
    reservation: Reservation;
};

const { reservation } = defineProps<ReservationExtendFormProps>();

const form = useForm({
    reservation_id: reservation.id,
    new_checkout_date: reservation.check_out_date,
});

type PaymentOption = "now" | "checkout" | "existing";

// Extension form state
const newCheckoutDate = ref("");
const paymentOption = ref<PaymentOption>("checkout");
const isLoading = ref(false);
const extensionComplete = ref(false);
const dateError = ref("");

// Constants
const nightlyRate = 1000; // Rate per night in PHP - in a real app, this would be fetched or calculated

// Computed properties
const additionalNights = computed(() => {
    if (!newCheckoutDate.value || !form.new_checkout_date) return 0;

    const currentCheckout = new Date(form.new_checkout_date);
    const newCheckout = new Date(newCheckoutDate.value);

    // Calculate difference in days
    const diffTime = newCheckout.getTime() - currentCheckout.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    return Math.max(0, diffDays);
});

const additionalCharge = computed(() => {
    return additionalNights.value * nightlyRate;
});

const isValidExtension = computed(() => {
    if (!newCheckoutDate.value || dateError.value) return false;
    if (additionalNights.value <= 0) return false;
    if (paymentOption.value === "now") return false;

    return true;
});

// Methods
const getMinDate = () => {
    // Minimum date should be at least one day after current checkout
    const minDate = new Date(form.new_checkout_date);
    return minDate;
};

const extendByDays = (days: number) => {
    const currentCheckout = new Date(form.new_checkout_date);
    const newDate = new Date(currentCheckout);
    newDate.setDate(newDate.getDate() + days);
    newCheckoutDate.value = newDate.toISOString().split("T")[0];
};

const validateDate = () => {
    if (!newCheckoutDate.value) {
        dateError.value = "Please select a new check-out date";
        return false;
    }

    const currentCheckout = new Date(form.new_checkout_date);
    const newCheckout = new Date(newCheckoutDate.value);

    if (newCheckout <= currentCheckout) {
        dateError.value =
            "New check-out date must be after the current check-out date";
        return false;
    }

    dateError.value = "";
    return true;
};

// Watch for changes in checkout date to validate
watch(newCheckoutDate, validateDate);

const processExtension = async () => {
    if (!isValidExtension.value) {
        return;
    }

    isLoading.value = true;

    try {
        // Simulate API call
        await new Promise((resolve) => setTimeout(resolve, 1500));

        // In a real app, you would send the extension data to your backend
        // const response = await api.extendReservation({
        //   reservationId: reservation.value.id,
        //   newCheckoutDate: newCheckoutDate.value,
        //   additionalCharge: additionalCharge.value,
        //   paymentOption: paymentOption.value,
        //   referenceNumber: referenceNumber.value,
        //   reason: extensionReason.value
        // })

        // Show success state
        extensionComplete.value = true;
    } catch (error) {
        console.error("Extension processing error:", error);
        // Handle error (show error message, etc.)
    } finally {
        isLoading.value = false;
    }
};
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
                                <p class="text-muted-foreground">Guest</p>
                                <p class="font-medium">
                                    {{ reservation.first_name }}
                                    {{
                                        reservation.middle_initial
                                            ? reservation.middle_initial + "."
                                            : ""
                                    }}
                                    {{ reservation.last_name }}
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
                    <div v-if="!extensionComplete">
                        <form
                            @submit.prevent="processExtension"
                            class="space-y-4"
                        >
                            <!-- New Check-out Date -->
                            <div class="space-y-2">
                                <Label for="new-checkout-date"
                                    >New Check-out Date</Label
                                >
                                <div
                                    class="grid grid-cols-1 gap-4 md:grid-cols-2"
                                >
                                    <Popover>
                                        <PopoverTrigger asChild>
                                            <Button
                                                id="new-checkout-date"
                                                variant="outline"
                                                :class="[
                                                    'w-full justify-start text-left font-normal',
                                                    !newCheckoutDate &&
                                                        'text-muted-foreground',
                                                ]"
                                            >
                                                <CalendarIcon
                                                    class="w-4 h-4 mr-2"
                                                />
                                                {{
                                                    newCheckoutDate
                                                        ? formatDateString(
                                                              newCheckoutDate
                                                          )
                                                        : "Select date"
                                                }}
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-auto p-0">
                                            <Calendar
                                                mode="single"
                                                v-model="newCheckoutDate"
                                                :disabled-dates="{
                                                    before: getMinDate(),
                                                }"
                                                :initialFocus="true"
                                            />
                                        </PopoverContent>
                                    </Popover>

                                    <div class="flex items-center gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            @click="extendByDays(1)"
                                        >
                                            +1 Day
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            @click="extendByDays(2)"
                                        >
                                            +2 Days
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            @click="extendByDays(3)"
                                        >
                                            +3 Days
                                        </Button>
                                    </div>
                                </div>
                                <p
                                    v-if="dateError"
                                    class="text-sm text-red-500"
                                >
                                    {{ dateError }}
                                </p>
                            </div>

                            <!-- Extension Summary -->
                            <div
                                v-if="additionalNights > 0"
                                class="p-4 space-y-3 rounded-lg bg-muted/50"
                            >
                                <div class="flex items-center justify-between">
                                    <h3 class="font-medium">
                                        Extension Summary
                                    </h3>
                                    <Badge
                                        variant="outline"
                                        class="bg-primary/10"
                                    >
                                        {{ additionalNights }} additional
                                        {{
                                            additionalNights === 1
                                                ? "day"
                                                : "days"
                                        }}
                                    </Badge>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Rate per day</span>
                                        <span class="font-medium"
                                            >₱{{
                                                formatCurrency(nightlyRate)
                                            }}</span
                                        >
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Additional nights</span>
                                        <span class="font-medium"
                                            >× {{ additionalNights }}</span
                                        >
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <span class="font-medium"
                                            >Total Additional Charge</span
                                        >
                                        <span class="font-bold"
                                            >₱{{
                                                formatCurrency(additionalCharge)
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- New Balance -->
                            <div
                                v-if="additionalNights > 0"
                                class="p-4 space-y-3 rounded-lg bg-muted/50"
                            >
                                <h3 class="font-medium">New Billings</h3>

                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Previous Total Billings</span>
                                        <span class="font-medium"
                                            >₱{{
                                                formatCurrency(
                                                    reservation.total_billings
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>New Total Billings</span>
                                        <span class="font-medium"
                                            >₱{{
                                                formatCurrency(nightlyRate)
                                            }}</span
                                        >
                                    </div>
                                    <div
                                        class="flex justify-between pt-2 text-sm"
                                    >
                                        <span>Previous Remaining Balance</span>
                                        <span class="font-medium"
                                            >₱{{
                                                formatCurrency(
                                                    reservation.remaining_balance
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between">
                                        <span class="font-medium"
                                            >New Remaining Balance</span
                                        >
                                        <span class="font-bold"
                                            >₱{{
                                                formatCurrency(additionalCharge)
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex items-center justify-end pt-4">
                                <Button
                                    type="submit"
                                    :disabled="isLoading || !isValidExtension"
                                >
                                    <Loader2Icon
                                        v-if="isLoading"
                                        class="w-4 h-4 mr-2 animate-spin"
                                    />
                                    {{
                                        paymentOption === "now"
                                            ? "Process Payment & Extend"
                                            : "Confirm Extension"
                                    }}
                                </Button>
                            </div>
                        </form>
                    </div>

                    <!-- Extension Success -->
                    <!-- <div v-else class="py-6 text-center">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-green-100 rounded-full">
                        <CheckIcon class="w-6 h-6 text-green-600" />
                        </div>

                        <h3 class="mb-2 text-xl font-medium text-green-600">Reservation Extended!</h3>

                        <div class="max-w-sm mx-auto mb-6">
                        <p class="mb-4 text-muted-foreground">
                            The reservation has been successfully extended to {{ formatDateString(newCheckoutDate) }}.
                        </p>

                        <div class="p-4 mb-4 text-left rounded-lg bg-muted">
                            <div class="grid grid-cols-2 gap-2 text-sm">
                            <p class="text-muted-foreground">Additional Nights:</p>
                            <p class="font-medium">{{ additionalNights }}</p>

                            <p class="text-muted-foreground">Additional Charge:</p>
                            <p class="font-medium">₱{{ formatCurrency(additionalCharge) }}</p>

                            <p class="text-muted-foreground">Payment Status:</p>
                            <p class="font-medium">{{ getPaymentStatusText() }}</p>

                            <p class="text-muted-foreground">New Check-out:</p>
                            <p class="font-medium">{{ formatDateString(newCheckoutDate) }}</p>
                            </div>
                        </div>
                        </div>

                        <div class="flex justify-center gap-4">
                        <Button variant="outline" @click="printConfirmation">
                            <PrinterIcon class="w-4 h-4 mr-2" />
                            Print Confirmation
                        </Button>
                        <Button @click="goBack">
                            Return to Reservation
                        </Button>
                        </div>
                    </div> -->
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
