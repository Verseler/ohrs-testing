<script setup lang="ts">
import type { Reservation } from "@/Pages/Admin/Reservation/reservation.types";
import { ref, watch } from "vue";
import { Button } from "@/Components/ui/button";
import { Input, InputError, InputDate } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Separator } from "@/Components/ui/separator";
import {
    CheckCircleIcon,
    CircleIcon,
    CreditCard,
    Home,
    ReceiptText,
} from "lucide-vue-next";
import { Head, router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PageHeader from "@/Components/PageHeader.vue";
import { formatCurrency, formatDateString } from "@/lib/utils";
import { Alert } from "@/Components/ui/alert-dialog";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbSeparator,
    BreadcrumbPage,
} from "@/Components/ui/breadcrumb";
import BackLink from "@/Components/BackLink.vue";
import type {
    Payment,
    PaymentOption,
} from "@/Pages/Admin/Payment/payment.types";
import { SidebarTrigger } from "@/Components/ui/sidebar";

type ReservationDetailsProps = {
    reservation: Reservation;
};

const { reservation } = defineProps<ReservationDetailsProps>();

const form = useForm<Partial<Payment>>({
    amount: reservation.remaining_balance,
    reservation_id: reservation.id,
    or_number: "",
    or_date: undefined,
});

// Payment form state
const paymentOption = ref<PaymentOption>("full");

function selectPaymentOption(option: PaymentOption) {
    paymentOption.value = option;
}

watch(paymentOption, () => {
    if (paymentOption.value === "full") {
        form.amount = reservation.remaining_balance;
    }
});

watch(
    () => form.amount,
    () => {
        if (form.amount === reservation.remaining_balance) {
            paymentOption.value = "full";
        } else {
            paymentOption.value = "custom";
        }
    }
);

//Payment Confirmation Dialog
const paymentConfirmation = ref(false);

function showPaymentConfirmation() {
    paymentConfirmation.value = true;
}

function submitPayment() {
    form.post(route("reservation.payment"));
}

//Pay Later Confirmation
const payLaterConfirmation = ref(false);

function showPayLaterConfirmation() {
    payLaterConfirmation.value = true;
}

function submitPayLater() {
    router.post(route("reservation.payLater", { id: reservation.id }));
}
</script>

<template>
    <Head title="Payment Reservation" />

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
                        <BreadcrumbLink
                            :href="
                                route('reservation.show', {
                                    id: reservation.id,
                                })
                            "
                        >
                            Reservation Details
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>Payment</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink
                :href="route('reservation.show', { id: reservation.id })"
            />
        </div>

        <PageHeader>
            <template #icon><CreditCard /></template>
            <template #title>Reservation Payment</template>
        </PageHeader>

        <div class="max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle
                        class="flex items-center justify-between text-xl font-bolds"
                    >
                        Record Payment
                    </CardTitle>
                    <CardDescription>
                        Reservation #{{ reservation.reservation_code }} for
                        {{ reservation.first_name }} {{ reservation.last_name }}
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <!-- Reservation Summary -->
                    <div class="p-4 rounded-lg bg-muted">
                        <h3 class="mb-4 font-medium">Reservation Summary</h3>

                        <div class="grid grid-cols-2 gap-4 text-sm">
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
                                    Check-out Date
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
                                <p class="text-muted-foreground">
                                    Total Billing
                                </p>
                                <p class="font-medium">
                                    ₱{{
                                        formatCurrency(
                                            reservation.total_billings
                                        )
                                    }}
                                </p>
                            </div>
                            <div>
                                <p class="text-muted-foreground">
                                    Remaining Balance
                                </p>
                                <p class="font-medium text-red-500">
                                    ₱{{
                                        formatCurrency(
                                            reservation.remaining_balance
                                        )
                                    }}
                                </p>
                            </div>
                            <div>
                                <p class="text-muted-foreground">
                                    Payment Type
                                </p>
                                <p class="font-medium text-green-500">
                                    {{
                                        reservation.payment_type === "pay_later"
                                            ? "Pay Later"
                                            : "Full Payment"
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid items-center grid-cols-2 gap-3">
                        <div>
                            <Label>OR Number</Label>
                            <div class="relative">
                                <Input
                                    v-model="form.or_number"
                                    :invalid="!!form.errors.or_number"
                                    class="h-12 mt-1 text-base rounded ps-9 border-primary-700"
                                />
                                <ReceiptText
                                    class="absolute text-lg size-4 top-4 left-2.5 text-primary-600"
                                />
                            </div>
                            <InputError v-if="!!form.errors.or_number">
                                {{ form.errors.or_number }}
                            </InputError>
                        </div>

                        <div>
                            <Label>OR Date</Label>
                            <InputDate
                                v-model="form.or_date"
                                :invalid="!!form.errors.or_date"
                                class="mt-1"
                            />
                            <InputError v-if="!!form.errors.or_date">
                                {{ form.errors.or_date }}
                            </InputError>
                        </div>
                    </div>

                    <!-- Payment Options -->
                    <template v-if="reservation.remaining_balance > 0">
                        <Separator />

                        <h3 class="mb-4 font-medium">Select Payment Option</h3>

                        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2">
                            <Card
                                :class="[
                                    'cursor-pointer border-2 transition-all',
                                    paymentOption === 'full' &&
                                        'border-primary-500',
                                ]"
                                @click="selectPaymentOption('full')"
                            >
                                <CardContent class="p-4 text-center">
                                    <CheckCircleIcon
                                        v-if="paymentOption === 'full'"
                                        class="w-5 h-5 mx-auto mb-2 text-primary"
                                    />
                                    <CircleIcon
                                        v-else
                                        class="w-5 h-5 mx-auto mb-2 text-muted-foreground"
                                    />
                                    <h4 class="font-medium">Full Payment</h4>
                                    <p class="text-sm text-muted-foreground">
                                        ₱{{
                                            formatCurrency(
                                                reservation.remaining_balance
                                            )
                                        }}
                                    </p>
                                </CardContent>
                            </Card>

                            <Card
                                :class="[
                                    'cursor-pointer border-2 transition-all',
                                    paymentOption === 'custom' &&
                                        'border-primary-500',
                                ]"
                                @click="selectPaymentOption('custom')"
                            >
                                <CardContent class="p-4 text-center">
                                    <CheckCircleIcon
                                        v-if="paymentOption === 'custom'"
                                        class="w-5 h-5 mx-auto mb-2 text-primary"
                                    />
                                    <CircleIcon
                                        v-else
                                        class="w-5 h-5 mx-auto mb-2 text-muted-foreground"
                                    />
                                    <h4 class="font-medium">Custom Amount</h4>
                                    <p class="text-sm text-muted-foreground">
                                        Specify amount
                                    </p>
                                </CardContent>
                            </Card>
                        </div>

                        <div>
                            <Label>Amount</Label>
                            <div class="relative">
                                <Input
                                    type="number"
                                    v-model.number="form.amount"
                                    :max="reservation.remaining_balance"
                                    :invalid="!!form.errors.amount"
                                    class="h-12 mt-1 text-lg rounded ps-7 text-primary-900 border-primary-700"
                                />
                                <span
                                    class="absolute text-lg top-2.5 left-2.5 text-primary-600"
                                    >₱</span
                                >
                            </div>
                        </div>
                        <div v-if="form.errors.amount" class="text-red-500">
                            {{ form.errors.amount }}
                        </div>
                        <div
                            v-if="form.errors.reservation_id"
                            class="text-red-500"
                        >
                            {{ form.errors.reservation_id }}
                        </div>
                    </template>
                </CardContent>
                <CardFooter v-if="reservation.remaining_balance > 0">
                    <div class="flex justify-end w-full pt-3 border-t gap-x-2">
                        <Button
                            v-if="reservation.payment_type === 'full_payment'"
                            variant="outline"
                            @click="showPayLaterConfirmation"
                            type="button"
                        >
                            <CreditCard /> Pay Later
                        </Button>

                        <Button @click="showPaymentConfirmation" type="button">
                            <CreditCard /> Record Payment
                        </Button>
                    </div>
                </CardFooter>
            </Card>
        </div>

        <Alert
            :open="payLaterConfirmation"
            @update:open="payLaterConfirmation = $event"
            :onConfirm="submitPayLater"
            title="Confirm change to Pay Later"
            confirm-label="Confirm"
        />
        <Alert
            :open="paymentConfirmation"
            @update:open="paymentConfirmation = $event"
            :onConfirm="submitPayment"
            title="Confirm Payment"
            description="Please review the payment details carefully before submitting. This action cannot be undone."
            confirm-label="Confirm"
        />
    </AuthenticatedLayout>
</template>
