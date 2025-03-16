<script setup lang="ts">
import { RoomWithBedCounts } from "@/Pages/RoomManagement/room.types";
import { Badge } from "@/Components/ui/badge";
import GenderBadge from "@/Components/GenderBadge.vue";

type AvailableRoomCardProps = {
    room: Omit<RoomWithBedCounts, 'beds'> & {
        beds_count: number;
    };
}

const { room } = defineProps<AvailableRoomCardProps>();

</script>

<template>
    <div class="overflow-hidden rounded-lg border">
        <div class="flex flex-col gap-1 p-4 sm:flex-row lg:flex-row sm:gap-4">
            <h3 class="font-medium">{{ room.name }}</h3>
            <div class="flex gap-2 items-center">
                <GenderBadge :gender="room.eligible_gender" />
                <Badge
                    class="px-2 h-6 font-normal text-center text-white"
                    :class="
                        room.beds_count > 0
                            ? 'bg-green-500 hover:bg-green-600'
                            : 'bg-red-500 hover:bg-red-600'
                    "
                >
                    <span v-if="room.beds_count > 0">
                        {{ room.beds_count }} available
                    </span>
                    <span v-else>fully booked</span>
                </Badge>
            </div>
        </div>
    </div>
</template>
