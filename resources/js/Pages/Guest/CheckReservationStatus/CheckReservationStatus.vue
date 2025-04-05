<script setup lang="ts">
import Header from "@/Components/Header.vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import type { PageProps } from "@/types";
import SearchCodeForm from "@/Pages/Guest/CheckReservationStatus/Partials/SearchCodeForm.vue";
import { computed } from "vue";
import { Reservation } from "@/Pages/Admin/Reservation/reservation.types";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";

const page = usePage<PageProps>();

const reservations = computed<Reservation[] | null>(() => {
    const response = page.props.response_data as Reservation[];
    return response || null;
});

const searchForm = useForm({
    code: "",
});

function checkReservation(code: string) {
    router.visit(route("reservation.checkStatus", { code: code }), {
        method: "get",
    });
}

function submitSearch() {
    searchForm.get(route("reservation.search", { search: searchForm.code }));
}
</script>

<template>
    <Head title="Check Reservation Status" />

    <div class="w-full min-h-screen">
        <Header />

        <div class="max-w-2xl px-4 py-12 mx-auto">
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
                v-model="searchForm.code"
                :error="page.props.flash.error ?? ''"
                :loading="searchForm.processing"
                :submit="submitSearch"
            />

            <Card
                v-if="reservations && reservations.length > 0"
                class="rounded-md shadow-none"
            >
                <CardHeader>
                    <CardTitle> Search Results </CardTitle>
                </CardHeader>
                <CardContent class="space-y-2.5">
                    <div
                        v-for="reservation in reservations"
                        :key="reservation.id"
                        class="flex items-center justify-between"
                    >
                        <p>
                            {{ reservation.first_name }}
                            {{ reservation.middle_initial }}
                            {{ reservation.last_name }}
                            <span class="block text-xs text-blue-500 md:inline-block">
                                [{{ reservation.check_in_date }}
                                <span class="text-neutral-500">to</span>
                                {{ reservation.check_out_date }}]
                            </span>
                        </p>
                        <Button
                            @click="
                                checkReservation(reservation.reservation_code)
                            "
                            variant="outline"
                            class="border-primary-500 text-primary-500 hover:bg-primary-50 hover:text-primary-600"
                        >
                            Check Status
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
