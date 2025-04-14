<script setup lang="ts">
import { BedIcon } from "lucide-vue-next";
import { Card } from "@/Components/ui/card";
import GenderBadge from "@/Components/GenderBadge.vue";
import type { Gender } from "@/Pages/Guest/guest.types";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import { formatDateString } from "@/lib/utils";

type BedAvailableItemsProps = {
    name: string;
    eligibleGender: Gender;
    eligibleGenderSchedules: Gender[];
    availableBeds: number;
    days: string[];
};

const { availableBeds, name, eligibleGenderSchedules, days } =
    defineProps<BedAvailableItemsProps>();
</script>

<template>
    <div>
        <div class="mx-2 mb-1.5 flex items-center justify-between gap-x-2">
            <div class="flex items-center gap-2 font-medium">
                <BedIcon class="w-5 h-5 text-muted-foreground" />
                <p>{{ name }}</p>
            </div>

            <div>
                Available Bed{{ availableBeds > 1 && 's' }}: <span class="font-bold">{{ availableBeds }}</span>
            </div>
        </div>
        <Card class="border rounded-md shadow-none">
        <Table class="rounded-md">
            <TableHeader>
                <TableRow class="bg-neutral-50">
                    <TableHead v-for="day in days" class="px-2 text-center text-black border-r">
                        {{ formatDateString(day) }}
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow>
                    <TableCell v-for="(gender, index) in eligibleGenderSchedules" :key="index" class="border-r">
                        <div class="grid place-content-center">
                            <GenderBadge :gender="gender" class="shadow-none" />
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </Card>
    </div>
</template>
