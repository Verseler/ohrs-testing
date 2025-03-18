<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Label } from "@/Components/ui/label";
import { Separator } from "@/Components/ui/separator";
import { formatCurrency, formatDateString } from "@/lib/utils";
import type { ReservationWithBeds } from "@/Pages/Admin/Reservation/reservation.types";

const { reservation } = defineProps<{ reservation: ReservationWithBeds }>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Reservation Information</CardTitle>
        </CardHeader>
        <CardContent class="grid gap-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <Label class="text-neutral-700">Check-in Date</Label>
                    <p class="font-medium">
                        {{ formatDateString(reservation.check_in_date) }}
                    </p>
                </div>
                <div>
                    <Label class="text-neutral-700">Check-out Date</Label>
                    <p class="font-medium">
                        {{ formatDateString(reservation.check_out_date) }}
                    </p>
                </div>
            </div>

            <Separator />

            <div class="grid gap-2">
                <Label class="text-neutral-700">Payment Details</Label>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total Billing
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
                </div>
            </div>

            <Separator />

            <div>
                <Label class="text-neutral-700"> Hostel Office </Label>
                <p class="font-medium">
                    {{
                        `Region
                            ${reservation?.hostel_office?.region?.name} -
                            ${reservation?.hostel_office?.name}` || "-"
                    }}
                </p>
            </div>
        </CardContent>
    </Card>
</template>
