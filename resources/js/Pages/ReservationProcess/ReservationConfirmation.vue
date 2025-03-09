<script setup lang="ts">
import { formatDateString, getDaysDifference } from "@/lib/utils";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import {  Room } from "@/Pages/RoomManagement/room.types";
import { Toaster } from "@/Components/ui/sonner";
import { Button } from "@/Components/ui/button";
import { Head, usePage } from "@inertiajs/vue3";
import Header from "@/Components/Header.vue";
import * as htmlToImage from "html-to-image";
import { Card } from "@/Components/ui/card";
import { Download } from "lucide-vue-next";
import { SharedData } from "@/types";
import { toast } from "vue-sonner";
import { onMounted } from "vue";

type ReservedRoom = Room & {
    total_beds: number;
};


type LandingPageProps = {
    canLogin: boolean;
    reservation_code: string;
    booked_by: string;
    phone: number;
    check_in_date: string | Date;
    check_out_date: string | Date;
    reserved_rooms: ReservedRoom[];
    bed_price_rate: number;
    total_amount: number;
    total_guests: number;
};

const {
    canLogin,
    reservation_code,
    booked_by,
    phone,
    check_in_date,
    check_out_date,
    reserved_rooms,
    bed_price_rate,
    total_amount,
    total_guests
} = defineProps<LandingPageProps>();

const lengthOfStay = getDaysDifference(check_in_date, check_out_date) + 1;

const page = usePage<SharedData>();

function downloadConfirmation() {
    htmlToImage
        .toJpeg(document.getElementById("confirmation") as HTMLElement, {
            quality: 2,
        })
        .then(function (dataUrl) {
            var link = document.createElement("a");
            link.download = `${reservation_code}-HRS-R10.jpeg`;
            link.href = dataUrl;
            link.click();
        });
}

onMounted(() => {
    toast.info("Remember to download your confirmation before leaving.",
            {
                style: {
                    background: "#3b82f6",
                    color: "white",
                },
                position: "top-center",
            }
    );
});
</script>

<template>
    <Head title="Reservation Slip" />

    <div class="w-full min-h-screen">
        <Header :can-login="canLogin" :user="page.props.auth.user" />
        <div
            class="flex flex-col items-center p-4 mt-10 min-h-screen"
        >
            <Card
                id="confirmation"
                class="w-full max-w-2xl text-sm rounded-none"
            >
                <div class="p-8 rounded-lg shadow-lg bg-card">
                    <ApplicationLogo />

                    <h1 class="mb-2 text-3xl font-bold text-center">
                        Reservation Confirmed!
                    </h1>
                    <p class="mb-8 text-center text-muted-foreground">
                        Thank you for choosing our hostel. Your reservation
                        details are below.
                    </p>

                    <div
                        class="p-2 text-center rounded-md bg-muted bg-neutral-200"
                    >
                        <p class="text-xs font-medium text-muted-foreground">
                            Reservation Code
                        </p>
                        <p class="text-xl font-bold">
                            {{ reservation_code }}
                        </p>
                    </div>

                    <div class="mt-5 space-y-6">
                        <table class="flex w-full">
                            <thead>
                                <tr
                                    class="flex flex-col space-y-1 min-w-max text-sm pe-3 bg-neutral-100 text-neutral-500"
                                >
                                    <td>Booked By</td>
                                    <td>Phone #</td>
                                    <td>Check-in Date</td>
                                    <td>Check-out Date</td>
                                </tr>
                            </thead>
                            <tbody class="w-full text-sm text-neutral-800">
                                <tr class="flex flex-col space-y-1 w-full ps-2">
                                    <td class="border-b">{{ booked_by }}</td>
                                    <td class="border-b">{{ phone }}</td>
                                    <td class="border-b">
                                        {{ formatDateString(check_in_date) }}
                                    </td>
                                    <td class="border-b">
                                        {{ formatDateString(check_out_date) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table class="w-full">
                            <thead>
                                <tr
                                    class="text-sm bg-neutral-100 text-neutral-500"
                                >
                                    <td>Room</td>
                                    <td>Total Beds</td>
                                    <td>Bed Price Rate</td>
                                    <td>Eligible Gender</td>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-neutral-800">
                                <tr v-for="room in reserved_rooms" :key="room.id"  class="border-b">
                                    <td>{{ room.name }}</td>
                                    <td>{{ room.total_beds }}</td>
                                    <td>₱{{ room.bed_price_rate }}</td>
                                    <td>{{ room.eligible_gender }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="p-4 rounded-md bg-muted">
                            <h2 class="mb-4 text-lg font-semibold">
                                Billing Summary
                            </h2>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">
                                        Bed Rate
                                    </span>
                                    <span>₱{{ bed_price_rate }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">
                                        Total Guests
                                    </span>
                                    <span>{{ total_guests ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground" >
                                        Length of stay
                                    </span>
                                    <span>
                                        {{ lengthOfStay }}
                                    </span>
                                </div>
                                <div class="my-2 h-px bg-border"></div>
                                <div class="flex justify-between font-bold">
                                    <span>Total Amount</span>
                                    <span>{{ total_amount }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 mt-8 border-t">
                        <p class="text-sm text-center text-muted-foreground">
                            A confirmation email has been sent to your
                            registered email address. If you have any questions,
                            please contact our support team.
                        </p>
                    </div>
                </div>
            </Card>

            <Button size="lg" @click="downloadConfirmation" class="mt-2 w-full max-w-2xl rounded">
                <Download class="w-4 h-4" />
                Download
            </Button>
        </div>

        <Toaster />
    </div>
</template>
