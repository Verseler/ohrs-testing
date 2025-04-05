<script setup lang="ts">
import { HotelIcon } from "lucide-vue-next";
import LinkButton from "@/Components/LinkButton.vue";
import Button from "@/Components/ui/button/Button.vue";
import BedAvailabilityChecker from "@/Components/BedAvailability/BedAvailabilityChecker.vue";

type HostelCardProps = {
    hostelName: string;
    regionName: string;
    hasHostel: boolean;
    hostelId: number;
};

const { hasHostel, hostelId, hostelName, regionName } =
    defineProps<HostelCardProps>();
</script>

<template>
    <div
        class="overflow-hidden transition-all duration-200 bg-white border border-gray-200 rounded-lg shadow-sm min-w-64"
        :class="
            hasHostel ? 'hover:shadow-md hover:border-primary/50' : 'opacity-75'
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

            <h2 class="text-lg font-semibold text-center">
                {{ hostelName }}
            </h2>
            <p class="mb-3 text-sm font-medium text-center text-neutral-500">
                Region {{ regionName }}
            </p>

            <div class="flex justify-center">
                <div v-if="hasHostel" class="flex gap-x-2">
                    <LinkButton
                        :href="
                            route('reservation.form', {
                                hostel_office_id: hostelId,
                            })
                        "
                    >
                        Make Reservation
                    </LinkButton>
                    <BedAvailabilityChecker :hostelId="hostelId" />
                </div>

                <Button v-else disabled class="w-full opacity-70">
                    Currently Unavailable
                </Button>
            </div>
        </div>
    </div>
</template>
