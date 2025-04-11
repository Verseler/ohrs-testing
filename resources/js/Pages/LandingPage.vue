<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import Header from "@/Components/Header.vue";
import type { Office } from "@/Pages/Admin/Office/office.types";
import HostelCard from "@/Components/HostelCard.vue";

type LandingPageProps = {
    hostels: Office[];
};

const { hostels } = defineProps<LandingPageProps>();
</script>

<template>
    <Head title="Reservation Form" />

    <div class="w-full min-h-screen">
        <Header />

        <div class="max-w-4xl px-4 py-12 mx-auto">
            <div class="mb-10 text-center">
                <h1 class="mb-2 text-2xl font-bold text-gray-900">
                    Select a Hostel Location
                </h1>
                <p class="max-w-xl mx-auto text-gray-600">
                    Choose one of the Region 10 hostels below to begin your
                    reservation process. Find your suitable accommodations.
                </p>
            </div>

            <div
                class="container flex flex-wrap items-center justify-center gap-6"
            >
                <template v-if="hostels && hostels.length > 0">
                    <HostelCard
                        v-for="hostel in hostels"
                        :key="hostel.id"
                        :hostel="hostel"
                    />
                </template>
                <p v-else class="text-sm italic text-neutral-500">
                    No hostels available at the moment. Please check back later.
                </p>
            </div>

            <div
                v-if="hostels && hostels.length > 0"
                class="mt-5 text-sm text-center text-gray-500"
            >
                <p>Experiencing problems? Contact our support team.</p>
            </div>
        </div>
    </div>
</template>
