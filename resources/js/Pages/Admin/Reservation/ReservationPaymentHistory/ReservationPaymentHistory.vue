<script setup lang="ts">
import { computed, ref } from "vue";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";
import { ReceiptIcon, History, Home, Maximize } from "lucide-vue-next";
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbSeparator,
    BreadcrumbPage,
} from "@/Components/ui/breadcrumb";
import BackLink from "@/Components/BackLink.vue";
import PageHeader from "@/Components/PageHeader.vue";
import type { Payment } from "@/Pages/PaymentHistoryManagement/payment.types";
import type { Reservation } from "@/Pages/Admin/Reservation/reservation.types";
import {
    formatCurrency,
    formatDateString,
    formatDateTimeString,
} from "@/lib/utils";
import AmountCard from "@/Pages/Admin/Reservation/ReservationPaymentHistory/Partials/AmountCard.vue";

type ReservationPaymentHistoryProps = {
    reservationPaymentHistory: Reservation & {
        payments: Payment[];
    };
};

const { reservationPaymentHistory } =
    defineProps<ReservationPaymentHistoryProps>();

const totalBillings = computed(() =>
    formatCurrency(reservationPaymentHistory.total_billings)
);

const totalPaid = computed(() =>
    formatCurrency(
        reservationPaymentHistory.total_billings -
            reservationPaymentHistory.remaining_balance
    )
);

const remainingBalance = computed(() =>
    formatCurrency(reservationPaymentHistory.remaining_balance)
);

// State
const showReceiptDialog = ref(false);
const selectedPayment = ref<Payment | null>(null);

// Methods
const viewReceipt = (payment: Payment) => {
    selectedPayment.value = payment;
    showReceiptDialog.value = true;
};

const closeReceiptDialog = () => {
    showReceiptDialog.value = false;

    //for smooth dialog closing animation
    setTimeout(() => {
        selectedPayment.value = null;
    }, 100);
};
</script>

<template>
    <Head title="Reservation Payment History" />

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
                        <BreadcrumbLink
                            :href="
                                route(
                                    'reservation.show',
                                    reservationPaymentHistory.id
                                )
                            "
                        >
                            Reservation Details
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>Payment History</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink
                :href="
                    route('reservation.show', {
                        id: reservationPaymentHistory.id,
                    })
                "
            />
        </div>

        <PageHeader>
            <template #icon><History /></template>
            <template #title>Payment History</template>
        </PageHeader>

        <Card>
            <CardHeader class="pb-3">
                <CardTitle class="text-lg font-medium"
                    >Payment History</CardTitle
                >
                <CardDescription>
                    Transaction history for reservation #{{
                        reservationPaymentHistory.reservation_code
                    }}
                </CardDescription>
            </CardHeader>

            <CardContent>
                <!-- Payment Summary -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <AmountCard>
                        <template #label> Total Billing </template>
                        <template #value> ₱{{ totalBillings }} </template>
                    </AmountCard>
                    <AmountCard>
                        <template #label> Total Paid </template>
                        <template #value>
                            <span class="text-green-600">₱{{ totalPaid }}</span>
                        </template>
                    </AmountCard>
                    <AmountCard>
                        <template #label> Remaining Balance </template>
                        <template #value>
                            <span
                                :class="
                                    reservationPaymentHistory.remaining_balance >
                                    0
                                        ? 'text-red-600'
                                        : 'text-green-600'
                                "
                                >₱{{ remainingBalance }}</span
                            >
                        </template>
                    </AmountCard>
                </div>

                <!-- Payments List -->
                <div
                    v-if="reservationPaymentHistory.payments.length > 0"
                    class="space-y-3"
                >
                    <div
                        v-for="payment in reservationPaymentHistory.payments"
                        :key="payment.id"
                        class="flex flex-col p-3 border rounded-md bg-primary-50"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="font-medium">Payment</div>
                                <div class="text-xs text-muted-foreground">
                                    {{
                                        formatDateTimeString(
                                            payment.payment_date
                                        )
                                    }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-green-600">
                                    +₱{{ formatCurrency(payment.amount) }}
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between pt-2 mt-2 border-t border-dashed"
                        >
                            <div class="flex items-center">
                                <span class="text-xs text-muted-foreground">
                                    OR #{{ payment.or_number }}
                                </span>
                            </div>
                            <div>
                                <Button size="sm" @click="viewReceipt(payment)">
                                    <Maximize class="mr-1" />
                                    View
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Payments -->
                <div
                    v-else
                    class="py-8 text-center border rounded-md text-neutral-500"
                >
                    <ReceiptIcon
                        class="w-10 h-10 mx-auto mb-2 text-muted-foreground"
                    />
                    <p class="text-muted-foreground">
                        No payment records found for this reservation.
                    </p>
                </div>
            </CardContent>

            <!-- View Receipt Dialog -->
            <Dialog v-model:open="showReceiptDialog">
                <DialogContent class="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Payment Receipt</DialogTitle>
                        <DialogDescription>
                            OR #{{ selectedPayment?.or_number }}
                        </DialogDescription>
                    </DialogHeader>

                    <div v-if="selectedPayment" class="space-y-4">
                        <div class="mb-2 text-center">
                            <h3 class="font-bold">Official Receipt</h3>
                            <p class="text-sm text-muted-foreground">
                                {{
                                    formatDateTimeString(
                                        selectedPayment.payment_date
                                    )
                                }}
                            </p>
                        </div>

                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        OR Number:
                                    </p>
                                    <p class="font-medium">
                                        {{ selectedPayment.or_number }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Reservation:
                                    </p>
                                    <p class="font-medium">
                                        {{
                                            reservationPaymentHistory.reservation_code
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Guest:
                                </p>
                                <p class="font-medium">
                                    {{ reservationPaymentHistory.first_name }}
                                    {{
                                        reservationPaymentHistory.middle_initial
                                            ? reservationPaymentHistory.middle_initial +
                                              "."
                                            : ""
                                    }}
                                    {{ reservationPaymentHistory.last_name }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Payment Date:
                                    </p>
                                    <p class="font-medium">
                                        {{
                                            formatDateString(
                                                selectedPayment.payment_date
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Payment Amount:
                                    </p>
                                    <p class="font-bold text-green-600">
                                        ₱{{
                                            formatCurrency(
                                                selectedPayment.amount
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div class="pt-3 mt-3 border-t">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Total Billing:
                                        </p>
                                        <p class="font-medium">
                                            ₱{{
                                                formatCurrency(
                                                    reservationPaymentHistory.total_billings
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Remaining Balance:
                                        </p>
                                        <p
                                            class="font-medium"
                                            :class="
                                                reservationPaymentHistory.remaining_balance >
                                                0
                                                    ? 'text-red-600'
                                                    : 'text-green-600'
                                            "
                                        >
                                            ₱{{
                                                formatCurrency(
                                                    reservationPaymentHistory.remaining_balance
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button
                            variant="destructive"
                            @click="closeReceiptDialog"
                            >Close</Button
                        >
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </Card>
    </AuthenticatedLayout>
</template>
