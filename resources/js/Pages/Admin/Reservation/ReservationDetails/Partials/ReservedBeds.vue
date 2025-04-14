<script setup lang="ts">
import GenderBadge from "@/Components/GenderBadge.vue";
import StatusBadge from "@/Components/StatusBadge.vue";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import { formatCurrency, formatDateString } from "@/lib/utils";
import type { ReservationWithBeds } from "@/Pages/Admin/Reservation/reservation.types";
import { Gender } from "@/Pages/Guest/guest.types";

const { reservation } = defineProps<{ reservation: ReservationWithBeds }>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Guest/s</CardTitle>
        </CardHeader>
        <CardContent>

            <CardContent class="p-0">
                <Table>
                    <TableHeader>
                        <TableRow class="bg-neutral-100">
                            <TableHead class="min-w-max text-neutral-600">Guest Name</TableHead>
                            <TableHead class="text-neutral-600">Gender</TableHead>
                            <TableHead class="text-neutral-600">Room</TableHead>
                            <TableHead class="text-neutral-600">Bed</TableHead>
                            <TableHead class="text-neutral-600">Check-in</TableHead>
                            <TableHead class="text-neutral-600">Check-out</TableHead>
                            <TableHead class="text-neutral-600">Status</TableHead>
                            <TableHead class="text-right text-neutral-600">Individual Billings</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="reservation?.reserved_beds_with_guests?.length > 0">
                            <TableRow
                                v-for="reserved in reservation.reserved_beds_with_guests"
                                :key="reserved.id"
                            >
                                <TableCell>
                                    {{ reserved.guest.first_name }} {{ reserved.guest.last_name }}
                                </TableCell>
                                <TableCell>
                                    <GenderBadge :gender="reserved.guest.gender" />
                                </TableCell>
                                <TableCell>{{ reserved?.bed?.room?.name ?? '-' }}</TableCell>
                                <TableCell>{{ reserved?.bed?.name ?? '-' }}</TableCell>
                                <TableCell>{{ formatDateString(reserved.check_in_date) }}</TableCell>
                                <TableCell>{{ formatDateString(reserved.check_out_date) }}</TableCell>
                                <TableCell>
                                    <StatusBadge :status="reserved.status" />
                                </TableCell>
                                <TableCell class="text-right">
                                    ₱{{ formatCurrency(reserved.individual_billings) }}
                                </TableCell>
                            </TableRow>
                        </template>
                        <TableRow v-else>
                            <TableCell colspan="7" class="py-4 text-center text-muted-foreground">
                                No reserved beds
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </CardContent>
    </Card>
</template>
