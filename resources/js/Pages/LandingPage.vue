<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import { usePage } from "@inertiajs/vue3";
import type { SharedData } from "@/types";
import Header from "@/Components/Header.vue";
import LinkButton from "@/Components/LinkButton.vue";
import { TreePalmIcon, HotelIcon, Building2Icon } from "lucide-vue-next";

const { canLogin } = defineProps<{ canLogin: boolean }>();

const page = usePage<SharedData>();

const offices = [
    {
        id: 1,
        name: "Regional Executive Office",
        icon: HotelIcon,
        available: true,
    },
    {
        id: 2,
        name: "Camiguin",
        icon: TreePalmIcon,
        available: false,
    },
    {
        id: 3,
        name: "Other Office",
        icon: Building2Icon,
        available: false,
    },
];
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

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div
                    v-for="office in offices"
                    :key="office.id"
                    class="overflow-hidden transition-all duration-200 bg-white border border-gray-200 rounded-lg shadow-sm"
                    :class="
                        office.available
                            ? 'hover:shadow-md hover:border-primary/50'
                            : 'opacity-75'
                    "
                >
                    <div class="p-6">
                        <div class="flex justify-center mb-4">
                            <div
                                class="flex items-center justify-center w-16 h-16 rounded-full bg-primary/10"
                            >
                                <component
                                    :is="office.icon"
                                    class="w-8 h-8 text-primary"
                                />
                            </div>
                        </div>

                        <h2 class="mb-2 text-lg font-semibold text-center">
                            {{ office.name }}
                        </h2>

                        <div class="flex justify-center">
                            <LinkButton
                                v-if="office.available"
                                class="w-full"
                                :href="
                                    route('reservation.form', {
                                        hostel_office_id: office.id,
                                    })
                                "
                            >
                                Select Location
                            </LinkButton>
                            <Button v-else disabled class="w-full opacity-70">
                                Currently Unavailable
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 text-sm text-center text-gray-500">
                <p>
                    Need assistance with your reservation? Contact our support
                    team.
                </p>
            </div>
        </div>
    </div>
</template>
