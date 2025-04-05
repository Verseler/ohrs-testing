<script setup lang="ts">
import { BedIcon } from "lucide-vue-next";
import { Card } from "@/Components/ui/card";
import GenderBadge from "@/Components/GenderBadge.vue";
import type { Gender } from "@/Pages/Guest/guest.types";

type BedAvailableItemsProps = {
    name: string;
    eligibleGender: Gender;
    availableBeds: number;
};

const { availableBeds, eligibleGender, name } =
    defineProps<BedAvailableItemsProps>();
</script>

<template>
    <Card
        class="flex items-center justify-between p-3 border rounded-md shadow-none"
    >
        <div class="flex items-center gap-3">
            <BedIcon class="w-5 h-5 text-muted-foreground" />

            <div class="font-medium">
                {{ name }}
            </div>

            <GenderBadge :gender="eligibleGender" />
        </div>

        <div class="font-medium">
            {{ availableBeds }} {{ availableBeds > 1 ? "beds" : "bed" }}
        </div>
    </Card>
</template>
