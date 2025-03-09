<script setup lang="ts">
import AvailableRoomCalendar from "@/Pages/ReservationProcess/Partials/AvailableRoomCalendar.vue";
import type { Bed, RoomWithBedCounts } from "@/Pages/RoomManagement/room.types";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { VenusAndMars, Venus, Mars } from "lucide-vue-next";
import { Badge } from "@/Components/ui/badge";
import { onMounted, ref, watch } from "vue";
import { formatDate } from "@/lib/utils";
import { toast } from "vue-sonner";
import axios from "axios";

const selectedDate = ref<Date | null>(new Date());

const availableRooms = ref<RoomWithBedCounts[]>([]);

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
                    <div
                    v-for="room in availableRooms"
                    :key="room.id"
                    class="overflow-hidden rounded-lg border"
                    >
                        <div class="flex flex-col gap-1 p-4 sm:flex-row lg:flex-row sm:gap-4">
                            <h3 class="font-medium">{{ room.name }}</h3>
                            <div class="flex gap-2 items-center">
                                <Badge
                                    variant="outline"
                                    class="gap-x-1 place-content-center px-2 h-6 font-normal min-w-16 border-neutral-200"
                                    >
                                        ₱{{ room.bed_price_rate }}
                                    </Badge>
                                <Badge
                                    variant="outline"
                                    class="gap-x-1 place-content-center px-2 h-6 font-normal min-w-16 border-neutral-200"
                                    :severity="room.eligible_gender === 'male'
                                            ? 'info'
                                            : room.eligible_gender === 'female'
                                            ? 'danger'
                                            : 'success'"
                                >
                                    <Mars
                                        v-if="room.eligible_gender === 'female'"
                                        class="size-3.5"
                                    />
                                    <Venus
                                        v-else-if="room.eligible_gender === 'male'"
                                        class="size-3.5"
                                    />
                                    <VenusAndMars v-else class="size-3.5" />
                                    {{ room.eligible_gender }}
                                </Badge>
                                <Badge
                                    v-if="room.beds"
                                    class="px-2 h-6 font-normal text-center text-white"
                                    :class="
                                        room.beds.length > 0
                                            ? 'bg-green-500 hover:bg-green-600'
                                            : 'bg-red-500 hover:bg-red-600'
                                    "
                                >
                                    <span v-if="room.beds.length > 0">
                                        {{ room.beds.length }} available
                                    </span>
                                    <span v-else>fully booked</span>
                                </Badge>
                            </div>
                        </div>
                    </div>
                </template>

                <div v-else class="py-5 text-sm font-normal text-center text-neutral-100">
                    <p>No available rooms/beds</p>
                </div>
            </div>
        </CardContent>
    </Card>

    <AvailableRoomCalendar v-model="selectedDate" />
</div>
</template>
