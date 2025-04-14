<script setup lang="ts">
import { Head, useForm, usePage } from "@inertiajs/vue3";
import type { PageProps } from "@/types";
import SearchCodeForm from "@/Pages/Guest/CheckReservationStatus/Partials/SearchCodeForm.vue";
import { ref, watch } from "vue";
import { Reservation } from "@/Pages/Admin/Reservation/reservation.types";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import ReservationItem from "@/Pages/Guest/CheckReservationStatus/Partials/ReservationItem.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import axios from "axios";

type CheckReservationStatusProps = {
    hostels: {value: number; label: string}[];
}


const { hostels } = defineProps<CheckReservationStatusProps>();

const page = usePage<PageProps>();

const searchForm = useForm({
    search: "",
    hostel_id: hostels?.[0]?.value || null
});

watch(() => searchForm.hostel_id, () => {
    page.props.response_data = null;
});

const loading = ref(false);
const errorMessage = ref<string | null>(null);
const reservations = ref<Reservation[]>([]);


async function submitSearch() {
    if (!searchForm.search) return;

    loading.value = true;
    errorMessage.value = null;
    reservations.value = [];

    try {
        const response = await axios.get(route('reservation.search'), {
            params: {
                search: searchForm.search,
                hostel_id: searchForm.hostel_id
            }
        });

        if (response.data.success) {
            reservations.value = response.data.data as Reservation[];
        } else {
            errorMessage.value = response.data.message;
        }
    } catch (error) {
        errorMessage.value = 'An error occurred while searching';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Head title="Check Reservation Status" />

    <GuestLayout>
        <div class="px-4 py-12 mx-auto max-w-3xl">
            <div class="mb-8 text-center">
                <h1 class="mb-2 text-2xl font-bold text-gray-900">
                    Check Your Reservation Status
                </h1>
                <p class="text-gray-600">
                    Enter your reservation code or the name of the employee who
                    made the booking to check the status of your reservation.
                </p>
            </div>

            <SearchCodeForm
                v-model:search="searchForm.search"
                v-model:hostelId="searchForm.hostel_id"
                :error="errorMessage || ''"
                :loading="loading"
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
