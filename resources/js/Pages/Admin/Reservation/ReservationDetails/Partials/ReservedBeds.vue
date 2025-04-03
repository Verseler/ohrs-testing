<script setup lang="ts">
import GenderBadge from "@/Components/GenderBadge.vue";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import type { ReservationWithBeds } from "@/Pages/Admin/Reservation/reservation.types";
import { roomScheduledEligibleGender } from "@/Pages/Admin/WaitingList/helpers";

const { reservation } = defineProps<{ reservation: ReservationWithBeds }>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Reserved Bed/s</CardTitle>
        </CardHeader>
        <CardContent>
            <div class="space-y-2">
                <template v-if="reservation?.reserved_beds?.length > 0">
                    <div
                        v-for="reserved in reservation.reserved_beds_with_guests"
                        class="flex text-sm gap-x-2"
                    >
                        <p>
                            {{ reserved.guest.first_name }}
                            {{ reserved.guest.last_name }}
                        </p>

                        <p>
                            - {{ reserved.bed.room.name }}
                            {{ reserved.bed.name }}
                        </p>

                        <GenderBadge
                            class="ml-auto"
                            v-if="roomScheduledEligibleGender(reserved.bed)"
                            :gender="roomScheduledEligibleGender(reserved.bed)"
                        />
                        <GenderBadge
                            v-else
                            class="ml-auto"
                            :gender="reserved.bed.room.eligible_gender"
                        />
                    </div>
                </template>
                <p
                    v-else
                    class="py-2 text-sm italic text-neutral-500 text-muted-foreground"
                >
                    No reserved bed/s
                </p>
            </div>
        </CardContent>
    </Card>
</template>
