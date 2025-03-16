<script setup lang="ts">
import AvailableRoomCard from "@/Pages/ReservationProcess/Partials/AvailableRoomsDetails/AvailableRoomCard.vue";
import AvailableRoomCalendar from "@/Pages/ReservationProcess/Partials/AvailableRoomCalendar.vue";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import type { RoomWithBedCounts } from "@/Pages/RoomManagement/room.types";
import { onMounted, ref, watch } from "vue";
import { formatDate } from "@/lib/utils";
import { toast } from "vue-sonner";
import axios from "axios";

const selectedDate = ref<Date | null>(new Date());

const availableRooms = ref<(Omit<RoomWithBedCounts, 'beds'> & {
        beds_count: number;
    })[]>([]);

async function getAvailableRooms(date: Date | null) {
    if(!date) return;

    await axios.get('/api/rooms/available', {
            params: { selected_date: formatDate(date) }
        }).then((response) => {
            availableRooms.value = response.data;
        }).catch((error) => {
            toast.error(error, {
                style: {
                    background: "#eab308",
                    color: "white",
                },
                position: "top-center",
            });
        });
}

onMounted(() => {
    getAvailableRooms(selectedDate.value);
});

watch(() => selectedDate.value, (newDate) => {
    getAvailableRooms(newDate);
}, { immediate: true });
</script>

<template>
    <div class="space-y-4 lg:max-w-lg">
    <Card class="w-full text-white bg-primary-600">
        <CardHeader>
            <CardTitle class="text-xl">Room/Bed Availability</CardTitle>
            <p class="text-sm text-muted-foreground">
                Check available beds across all room
            </p>
        </CardHeader>
        <CardContent>
            <div class="space-y-4">
                <template v-if="availableRooms && availableRooms.length > 0">
                    <AvailableRoomCard
                        v-for="room in availableRooms"
                        :key="room.id"
                        :room="room"
                    />
                </template>

                <div v-else class="py-5 text-sm font-normal text-center text-neutral-100">
                    <p>No available rooms/beds</p>
                </div>
                <div v-else class="py-5 text-sm font-normal text-center text-neutral-100">
                    <p>No available rooms/beds</p>
                </div>
            </div>
        </CardContent>
    </Card>

    <AvailableRoomCalendar v-model="selectedDate" />
</div>
</template>
