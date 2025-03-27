<script setup lang="ts">
import { computed } from "vue";
import { UserIcon, BedIcon, HomeIcon } from "lucide-vue-next";
import type { Gender } from "@/Pages/Guest/guest.types";

interface GuestDetailsProps {
    firstName: string;
    lastName: string;
    gender: Gender;
    roomName: string;
    bedName: string;
}

const { firstName, lastName, gender, bedName, roomName } =
    defineProps<GuestDetailsProps>();
</script>

<template>
    <div
        class="max-w-md overflow-hidden bg-white border border-gray-200 rounded-lg shadow max-h-max"
    >
        <!-- Header with guest name -->
        <div class="px-4 py-3 border-b border-gray-200 bg-primary/10">
            <h3 class="text-lg font-semibold text-primary">
                {{ firstName }} {{ lastName }}
            </h3>
        </div>

        <!-- Guest details -->
        <div class="p-4 space-y-4">
            <!-- Basic info section -->
            <div class="flex items-start space-x-3">
                <UserIcon class="w-5 h-5 text-gray-500 mt-0.5" />
                <div>
                    <div class="text-sm text-gray-500">Gender</div>
                    <div class="font-medium">{{ gender }}</div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-2">
                <!-- Bed details -->
                <div class="flex items-start space-x-3">
                    <BedIcon class="w-5 h-5 text-gray-500 mt-0.5" />
                    <div>
                        <div class="text-sm text-gray-500">Prev. Room/Bed</div>
                        <div class="font-medium">
                            {{ roomName }} {{ bedName }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card footer with reservation status -->
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center">
                <div class="w-2 h-2 mr-2 bg-green-500 rounded-full"></div>
                <span class="text-sm font-medium">Currently Reserved</span>
            </div>
        </div>
    </div>
</template>
