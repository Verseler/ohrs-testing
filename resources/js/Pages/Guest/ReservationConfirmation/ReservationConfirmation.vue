<script setup lang="ts">
import { Check, Calendar, Users, Download, Hotel, Info } from "lucide-vue-next";
import { Card, CardContent, CardHeader } from "@/Components/ui/card";
import { Separator } from "@/Components/ui/separator";
import { Head } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import * as htmlToImage from "html-to-image";
import { toast } from "vue-sonner";
import { onMounted } from "vue";
import ListItem from "@/Pages/Guest/ReservationConfirmation/Partials/ListItem.vue";
import Code from "@/Pages/Guest/ReservationConfirmation/Partials/Code.vue";
import PendingStatus from "@/Pages/Guest/ReservationConfirmation/Partials/PendingStatus.vue";
import { Message } from "@/Components/ui/message";
import { formatDateString } from "@/lib/utils";
import GuestLayout from "@/Layouts/GuestLayout.vue";

type ReservationDetails = {
    from: string;
    to: string;
    code: string;
    hostel_office_name: string;
    total_guests: number;
};

const { reservation } = defineProps<{ reservation: ReservationDetails }>();

function downloadConfirmation() {
    htmlToImage
        .toJpeg(document.getElementById("confirmation") as HTMLElement, {
            quality: 1,
            skipFonts: true,
        })
        .then(function (dataUrl) {
            var link = document.createElement("a");
            link.download = `${reservation.code}-HRS-${reservation.hostel_office_name}.jpeg`;
            link.href = dataUrl;
            link.click();
        });
}

onMounted(() => {
    const key = `downloaded-${reservation.code}`;

    // Only download if it hasn't been downloaded yet in this session
    if (!sessionStorage.getItem(key)) {
        downloadConfirmation();
        sessionStorage.setItem(key, "true");

        toast.info("Remember to download your confirmation before leaving.", {
            style: {
                background: "#3b82f6",
                color: "white",
            },
            position: "top-center",
        });
    }
});
</script>

<template>
    <Head title="Reservation Confirmation" />

    <GuestLayout>
        <div class="container max-w-xl px-4 py-2 mx-auto">
            <!-- Check Icon -->
            <div class="flex items-center justify-center mb-4">
                <div class="p-3 bg-green-600 rounded-full">
                    <Check class="w-8 h-8 text-white" />
                </div>
            </div>

            <h1 class="mb-2 text-3xl font-bold text-center">
                Reservation Submitted!
            </h1>
            <p class="mb-2 text-center text-muted-foreground text-neutral-500">
                Your reservation has been successfully submitted. Please wait
                while your reservation is being processed. You can check your
                reservation status using the reservation code below.
            </p>

            <Card id="confirmation" class="rounded-sm shadow">
                <CardHeader class="pb-4 text-center">
                    <h2 class="mb-2 text-sm font-semibold text-neutral-500">
                        RESERVATION CODE
                    </h2>
                    <Code>{{ reservation.code }}</Code>
                </CardHeader>

                <CardContent class="space-y-6">
                    <div
                        class="flex items-center justify-between p-4 rounded-lg bg-muted"
                    >
                        <div class="font-medium">Status</div>
                        <PendingStatus>Pending</PendingStatus>
                    </div>

                    <Separator />
                    <div class="space-y-4">
                        <ListItem
                            :icon="Calendar"
                            label="From"
                            :value="formatDateString(reservation.from)"
                        />
                        <ListItem
                            :icon="Calendar"
                            label="To"
                            :value="formatDateString(reservation.to)"
                        />
                        <ListItem
                            :icon="Users"
                            label="Total number of guests"
                            :value="`${reservation.total_guests} ${
                                reservation.total_guests > 1
                                    ? 'guests'
                                    : 'guest'
                            }`"
                        />
                        <ListItem
                            :icon="Hotel"
                            label="Hostel Location"
                            :value="reservation.hostel_office_name"
                        />
                    </div>
                </CardContent>
            </Card>

            <Message
                severity="info"
                class="flex items-center justify-center my-2 text-sm gap-x-2"
            >
                <Info class="size-3.5" /> A copy of your reservation code has
                been sent to your email.
            </Message>

            <Button
                @click="downloadConfirmation"
                variant="outline"
                class="w-full mt-2"
                size="lg"
            >
                <Download />Download Reservation Code
            </Button>
        </div>
    </GuestLayout>
</template>
