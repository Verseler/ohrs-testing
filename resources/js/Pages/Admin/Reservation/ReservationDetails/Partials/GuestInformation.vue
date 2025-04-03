<script setup lang="ts">
import EmployeeID from "@/Components/EmployeeID.vue";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Label } from "@/Components/ui/label";
import type { ReservationWithBeds } from "@/Pages/Admin/Reservation/reservation.types";

const { reservation } = defineProps<{ reservation: ReservationWithBeds }>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Guest Information</CardTitle>
        </CardHeader>
        <CardContent class="grid gap-4">
            <div class="grid grid-cols-2">
                <div>
                    <Label class="text-neutral-700">Booked By</Label>
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
                    <Label class="text-neutral-700">Total Number of Guests</Label>
                    <p class="font-medium">
                        {{ reservation?.guests?.length }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <Label class="text-neutral-700"> Phone </Label>
                    <p class="font-medium">
                        {{ reservation.phone }}
                    </p>
                </div>
                <div>
                    <Label class="text-neutral-700"> Email </Label>
                    <p class="font-medium">
                        {{ reservation.email || "-" }}
                    </p>
                </div>
            </div>

            <div>
                <Label class="text-neutral-700"> Guest Office </Label>
                <p class="font-medium">
                    {{
                        `Region
                            ${reservation?.guest_office?.region?.name} -
                            ${reservation?.guest_office?.name}` || "-"
                    }}
                </p>
            </div>

            <EmployeeID :value="reservation.employee_id" />
        </CardContent>
    </Card>
</template>
