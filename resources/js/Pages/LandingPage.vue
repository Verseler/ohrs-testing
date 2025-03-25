<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import { usePage } from "@inertiajs/vue3";
import type { SharedData } from "@/types";
import Header from "@/Components/Header.vue";
import LinkButton from "@/Components/LinkButton.vue";
import { HotelIcon } from "lucide-vue-next";
import type { Office } from "@/Pages/Admin/Office/office.types";

type LandingPageProps = {
    canLogin: boolean;
    hostels: Office[];
};

const { canLogin, hostels } = defineProps<LandingPageProps>();

const page = usePage<SharedData>();
</script>

<template>
    <Head title="Reservation Form" />

    <div class="w-full min-h-screen">
        <Header :can-login="canLogin" :user="page.props.auth.user" />

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
                    <div
                        v-for="hostel in hostels"
                        :key="hostel.id"
                        class="overflow-hidden transition-all duration-200 bg-white border border-gray-200 rounded-lg shadow-sm min-w-64"
                        :class="
                            hostel.has_hostel
                                ? 'hover:shadow-md hover:border-primary/50'
                                : 'opacity-75'
                        "
                    >
                        <div class="p-6">
                            <div class="flex justify-center mb-3">
                                <div
                                    class="flex items-center justify-center w-16 h-16 rounded-full bg-primary/10"
                                >
                                    <HotelIcon
                                        class="size-8 text-primary-500"
                                    />
                                </div>
                            </div>

                            <h2 class="text-lg font-semibold text-center">
                                {{ hostel.name }}
                            </h2>
                            <p
                                class="mb-3 text-sm font-medium text-center text-neutral-500"
                            >
                                Region {{ hostel.region.name }}
                            </p>

                            <div class="flex justify-center">
                                <LinkButton
                                    v-if="hostel.has_hostel"
                                    class="w-full"
                                    :href="
                                        route('reservation.form', {
                                            hostel_office_id: hostel.id,
                                        })
                                    "
                                >
                                    Select Location
                                </LinkButton>
                                <Button
                                    v-else
                                    disabled
                                    class="w-full opacity-70"
                                >
                                    Currently Unavailable
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 text-sm text-center text-gray-500">
                        <p>
                            Need assistance with your reservation? Contact our
                            support team.
                        </p>
                    </div>
                </template>
                <p v-else class='text-sm italic text-neutral-500'>
                    No hostels available at the moment. Please check back later.
                </p>
            </div>
        </div>
    </div>
</template>
