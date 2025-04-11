<script setup lang="ts">
import { HotelIcon } from "lucide-vue-next";
import LinkButton from "@/Components/LinkButton.vue";
import Button from "@/Components/ui/button/Button.vue";
import BedAvailabilityChecker from "@/Components/BedAvailability/BedAvailabilityChecker.vue";
import type { Office } from "@/Pages/Admin/Office/office.types";

const { hostel } = defineProps<{ hostel: Office }>();
</script>

<template>
    <div
        class="overflow-hidden transition-all duration-200 bg-white border border-gray-200 rounded-lg shadow-sm min-w-64"
        :class="
            hostel.has_hostel ? 'hover:shadow-md hover:border-primary/50' : 'opacity-75'
        "
    >
        <div class="p-6">
            <div class="flex justify-center mb-3">
                <div
                    class="flex items-center justify-center w-16 h-16 rounded-full bg-primary/10"
                >
                    <HotelIcon class="size-8 text-primary-500" />
                </div>
            </div>

            <h2 class="text-lg font-semibold mb-4 text-center">
                {{ hostel.hostel_name || hostel.name }}
            </h2>

            <div class="flex justify-center">
                <div v-if="hostel.has_hostel" class="flex gap-x-2">
                    <LinkButton
                        :href="
                            route('reservation.form', {
                                hostel_office_id: hostel.id,
                            })
                        "
                    >
                        Make Reservation
                    </LinkButton>
                    <BedAvailabilityChecker :hostelId="hostel.id" />
                </div>

                <Button v-else disabled class="w-full opacity-70">
                    Currently Unavailable
                </Button>
            </div>
        </div>
    </div>
</template>
