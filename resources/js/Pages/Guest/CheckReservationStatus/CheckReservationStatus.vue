<script setup lang="ts">
import Header from "@/Components/Header.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import type { SharedData } from "@/types";
import { Info } from "lucide-vue-next";
import { Message } from "@/Components/ui/message";
import SearchCodeForm from "@/Pages/Guest/CheckReservationStatus/Partials/SearchCodeForm.vue";

type CheckReservationStatusProps = {
    canLogin: boolean;
};

const { canLogin } = defineProps<CheckReservationStatusProps>();

const page = usePage<SharedData>();

const form = useForm({
    code: '',
});

function checkReservation() {
    form.get(route('reservation.checkStatus', { code: form.code }));
}
</script>

<template>
    <Head title="Check Reservation Status" />

    <div class="w-full min-h-screen">
        <Header :can-login="canLogin" :user="page.props.auth.user" />

        <div class="px-4 py-12 mx-auto max-w-2xl">
            <div class="mb-8 text-center">
                <h1 class="mb-2 text-2xl font-bold text-gray-900">
                    Check Your Reservation Status
                </h1>
                <p class="text-gray-600">
                    Enter your reservation code to view the current status of
                    your booking.
                </p>
            </div>

            <SearchCodeForm
                v-model="form.code"
                :error="page.props.flash.error ?? ''"
                :loading="form.processing"
                :submit="checkReservation"
            />

            <!-- Help Information -->
            <Message
                severity="info"
                class="flex gap-x-2 items-center p-4 mb-6 text-sm"
            >
                <Info class="size-4" />
                Your reservation code was automatically downloaded after your
                reservation was created.
            </Message>
        </div>
    </div>
</template>
