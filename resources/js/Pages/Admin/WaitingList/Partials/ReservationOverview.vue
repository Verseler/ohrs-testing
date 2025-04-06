<script setup lang="ts">
import EmployeeID from "@/Components/EmployeeID.vue";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Label } from "@/Components/ui/label";
import { formatDateString } from "@/lib/utils";
import type { ReservationWithBeds } from "@/Pages/Admin/Reservation/reservation.types";
import { Users } from "lucide-vue-next";

const { reservation } = defineProps<{ reservation: ReservationWithBeds }>();
</script>

<template>
    <Card class="relative max-w-xl md:min-w-[27rem]">
        <CardHeader>
            <CardTitle class="flex items-center justify-between">
                <span>Reservation Overview</span>
                <span
                    class="absolute px-3 py-2 ml-1 flex gap-x-1.5 font-normal text-white border rounded-sm rounded-tr-lg right-1 top-1 bg-primary-500 border-primary-500"
                >
                    <Users class="size-4" /> {{ reservation.guests.length }}
                </span>
            </CardTitle>
        </CardHeader>
        <CardContent class="grid grid-cols-2 gap-y-4 gap-x-2">
            <div>
                <Label class="text-neutral-700"> Check-in Date </Label>
                <p class="text-lg font-medium text-primary-500">
                    {{ formatDateString(reservation.check_in_date) }}
                </p>
            </div>

            <div>
                <Label class="text-neutral-700">Check-out Date</Label>
                <p class="text-lg font-medium text-primary-500">
                    {{ formatDateString(reservation.check_out_date) }}
                </p>
            </div>

            <div>
                <Label class="text-neutral-700"> Booked By </Label>
                <p class="font-medium">
                    {{ `${reservation.first_name} ${reservation.last_name}` }}
                </p>
            </div>

            <div>
                <Label class="text-neutral-700"> Phone </Label>
                <p class="font-medium">
                    {{ reservation.phone }}
                </p>
            </div>

            <div class="col-span-2">
                <Label class="text-neutral-700"> Code </Label>
                <p class="font-medium">
                    {{ reservation.reservation_code }}
                </p>
            </div>

            <div class='col-span-1'>
                <Label class="text-neutral-700"> ID Type </Label>
                <p class="font-medium">
                    {{ reservation.id_type }}
                </p>
            </div>

            <EmployeeID class='col-span-1' :value="reservation.employee_id" />
        </CardContent>
    </Card>
</template>
