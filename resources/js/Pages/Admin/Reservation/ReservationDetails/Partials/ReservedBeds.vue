<script setup lang="ts">
import GenderBadge from "@/Components/GenderBadge.vue";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import type { ReservationWithBeds } from "@/Pages/Admin/Reservation/reservation.types";

const { reservation } = defineProps<{ reservation: ReservationWithBeds }>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Reserved Beds</CardTitle>
        </CardHeader>
        <CardContent>
            <div class="space-y-2">
                <template v-if="reservation.reserved_beds.length > 0">
                    <div
                        v-for="bed in reservation.reserved_beds"
                        class="flex text-sm gap-x-2"
                    >
                        <p>{{ bed.room.name }}</p>
                        <p>- {{ bed.name }}</p>
                        <GenderBadge
                            class="ml-auto"
                            :gender="bed.room.eligible_gender"
                        />
                    </div>
                </template>
                <p
                    v-else
                    class="py-2 text-sm italic text-neutral-500 text-muted-foreground"
                >
                    No reserved beds
                </p>
            </div>
        </CardContent>
    </Card>
</template>
