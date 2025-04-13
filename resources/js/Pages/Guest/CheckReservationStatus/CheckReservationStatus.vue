<script setup lang="ts">
import { Head, useForm, usePage } from "@inertiajs/vue3";
import type { PageProps } from "@/types";
import SearchCodeForm from "@/Pages/Guest/CheckReservationStatus/Partials/SearchCodeForm.vue";
import { computed, watch } from "vue";
import { Reservation } from "@/Pages/Admin/Reservation/reservation.types";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import ReservationItem from "@/Pages/Guest/CheckReservationStatus/Partials/ReservationItem.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";

type CheckReservationStatusProps = {
    hostels: {value: number; label: string}[];
}

const { hostels } = defineProps<CheckReservationStatusProps>();

const page = usePage<PageProps>();

const reservations = computed<Reservation[] | null>(() => {
    const response = page.props.response_data as Reservation[];
    return response || null;
});

const searchForm = useForm({
    code: "",
    hostel_id: hostels?.[0]?.value || null
});

watch(() => searchForm.hostel_id, () => {
    page.props.response_data = null;
});


function submitSearch() {
    if(!searchForm.code) return;

    searchForm.get(route("reservation.search", { search: searchForm.code, hostel_id: searchForm.hostel_id }), {
        preserveState: true
    });
}
</script>

<template>
    <Head title="Check Reservation Status" />

    <GuestLayout>
        <div class="max-w-3xl px-4 py-12 mx-auto">
            <div class="mb-8 text-center">
                <h1 class="mb-2 text-2xl font-bold text-gray-900">
                    Check Your Reservation Status
                </h1>
                <p class="text-gray-600">
                    "Enter your reservation code or the name of the employee who
                    made the booking to check the status of your reservation.
                </p>
            </div>

            <SearchCodeForm
                v-model:code="searchForm.code"
                v-model:hostelId="searchForm.hostel_id"
                :error="page.props.flash.error ?? ''"
                :loading="searchForm.processing"
                :submit="submitSearch"
                :hostels="hostels"
            />

            <Card
                v-if="reservations && reservations.length > 0"
                class="rounded-md shadow-none"
            >
                <CardHeader>
                    <CardTitle> Search Results </CardTitle>
                </CardHeader>
                <CardContent class="space-y-2.5 max-h-[16rem] overflow-y-auto">
                    <ReservationItem
                        v-for="reservation in reservations"
                        :key="reservation.id"
                        :reservation="reservation"
                        :hostels="hostels"
                    />
                </CardContent>
            </Card>
        </div>
    </GuestLayout>
</template>
