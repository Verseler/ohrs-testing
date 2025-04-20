<script setup lang="ts">
import PaymentTypeBadge from "@/Components/PaymentTypeBadge.vue";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Label } from "@/Components/ui/label";
import { Separator } from "@/Components/ui/separator";
import { formatCurrency } from "@/lib/utils";
import type { ReservationWithBeds } from "@/Pages/Admin/Reservation/reservation.types";

const { reservation } = defineProps<{ reservation: ReservationWithBeds }>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Reservation Information</CardTitle>
        </CardHeader>
        <CardContent class="grid gap-4">

            <!--  v-if="notPendingOrCanceled" -->
            <div class="grid gap-2">
                <Label class="text-neutral-700">Payment Details</Label>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total Billings
                        </p>
                        <p class="font-medium">
                            ₱{{ formatCurrency(reservation.total_billings) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Remaining Balance
                        </p>
                        <p
                            class="font-medium"
                            :class="
                                reservation.remaining_balance > 0
                                    ? 'text-red-500'
                                    : 'text-green-500'
                            "
                        >
                            ₱{{ formatCurrency(reservation.remaining_balance) }}
                        </p>
                    </div>
                    <div>
                        <p class="mb-1 text-sm">
                            Payment Type
                        </p>
                        <p class="font-medium">
                            <PaymentTypeBadge :payment-type="reservation.payment_type" />
                        </p>
                    </div>
                </div>
            </div>

            <Separator />

            <div>
                <Label class="text-neutral-700"> Hostel Location </Label>
                <p class="font-medium">
                    {{ reservation?.hostel_office?.name || "-" }}
                </p>
            </div>

            <div>
                <p class="text-sm">
                    Purpose of Stay
                </p>
                <p
                    v-if="reservation.purpose_of_stay"
                    class="font-medium"
                >
                    {{ reservation.purpose_of_stay }}
                </p>
                <p v-else class="text-xs italic text-neutral-500">
                    Not provided
                </p>
            </div>
        </CardContent>
    </Card>
</template>

