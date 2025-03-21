<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Label } from "@/Components/ui/label";
import type { ReservationWithBeds } from "@/Pages/Admin/Reservation/reservation.types";
import { IdCard } from 'lucide-vue-next';

const { reservation } = defineProps<{ reservation: ReservationWithBeds }>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Guest Information</CardTitle>
        </CardHeader>
        <CardContent class="grid gap-4">
            <div>
                <Label class="text-neutral-700">Full Name</Label>
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

            <div class="space-y-1">
                <Label class="text-neutral-700"> Employee ID </Label>
                <p
                    class="flex items-center px-2 py-2 rounded gap-x-2 bg-neutral-100"
                >
                    <IdCard class="size-5 text-neutral-500" />
                    {{ reservation.employee_id || "-" }}
                </p>
            </div>
        </CardContent>
    </Card>
</template>
