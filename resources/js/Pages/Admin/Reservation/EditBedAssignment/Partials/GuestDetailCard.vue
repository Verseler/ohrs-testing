<script setup lang="ts">
import { UserIcon, BedIcon, CalendarIcon } from "lucide-vue-next";
import type { Gender } from "@/Pages/Guest/guest.types";
import { Card, CardContent, CardFooter, CardHeader } from "@/Components/ui/card";

interface GuestDetailsProps {
    firstName: string;
    lastName: string;
    gender: Gender;
    roomName: string;
    bedName: string;
    checkInDate: string;
    checkOutDate: string;
}

const { firstName, lastName, gender, bedName, roomName, checkInDate, checkOutDate } =
    defineProps<GuestDetailsProps>();
</script>

<template>
    <Card
        class="overflow-hidden max-w-md bg-white rounded-lg border border-gray-200 shadow max-h-max"
    >
        <CardHeader>
            <div class="pb-2 border-b border-gray-200 bg-primary/10">
                <h3 class="text-lg font-semibold text-primary">
                    {{ firstName }} {{ lastName }}
                </h3>
            </div>
        </CardHeader>

        <CardContent class="grid grid-cols-2 gap-x-2 gap-y-6">
            <!-- Basic info section -->
            <div class="flex items-start space-x-3">
                <UserIcon class="mt-0.5 w-5 h-5 text-gray-500" />
                <div>
                    <div class="text-sm text-gray-500">Gender</div>
                    <div class="font-medium">{{ gender }}</div>
                </div>
            </div>

            <div class="flex gap-2 justify-between items-center">
                <!-- Bed details -->
                <div class="flex items-start space-x-3">
                    <BedIcon class="mt-0.5 w-5 h-5 text-gray-500" />
                    <div>
                        <div class="text-sm text-gray-500">Prev. Room/Bed</div>
                        <div class="font-medium">
                            {{ roomName }} {{ bedName }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                    <CalendarIcon class="mt-0.5 w-5 h-5 text-gray-500" />
                    <div>
                        <div class="text-sm text-gray-500">Check-in Date</div>
                        <div class="font-medium">
                            {{ checkInDate }}
                        </div>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <CalendarIcon class="mt-0.5 w-5 h-5 text-gray-500" />
                    <div>
                        <div class="text-sm text-gray-500">Check-out Date</div>
                        <div class="font-medium">
                            {{ checkOutDate }}
                        </div>
                    </div>
                </div>
        </CardContent>


        <CardFooter class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center">
                <div class="mr-2 w-2 h-2 bg-green-500 rounded-full"></div>
                <span class="text-sm font-medium">Currently Reserved</span>
            </div>
        </CardFooter>
    </Card>
</template>
