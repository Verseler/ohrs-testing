<script setup lang="ts">
import {
    Check,
    Calendar,
    Users,
    Download,
    Hotel,
    ArrowLeft,
} from "lucide-vue-next";
import { Card, CardContent, CardHeader } from "@/Components/ui/card";
import { Separator } from "@/Components/ui/separator";
import { Head, Link } from "@inertiajs/vue3";
import Header from "@/Components/Header.vue";
import { Button } from "@/Components/ui/button";
import { Reservation } from "@/Pages/Admin/Reservation/reservation.types";
import * as htmlToImage from "html-to-image";
import { toast } from "vue-sonner";
import { onMounted } from "vue";
import LinkButton from "@/Components/LinkButton.vue";
import ListItem from "@/Pages/Guest/Partials/ListItem.vue";
import Code from "@/Pages/Guest/Partials/Code.vue";
import PendingStatus from "@/Pages/Guest/Partials/PendingStatus.vue";

type ReservationConfirmationProps = {
    reservation: Pick<
        Reservation,
        "check_in_date" | "check_out_date" | "status" | "reservation_code"
    > & {
        hostel_office_name: string;
        total_guests: number;
    };
};

const { reservation } = defineProps<ReservationConfirmationProps>();

function downloadConfirmation() {
    htmlToImage
        .toJpeg(document.getElementById("confirmation") as HTMLElement, {
            quality: 1,
            skipFonts: true,
        })
        .then(function (dataUrl) {
            var link = document.createElement("a");
            link.download = `${reservation.reservation_code}-HRS-${reservation.hostel_office_name}.jpeg`;
            link.href = dataUrl;
            link.click();
        });
}

onMounted(() => {
    downloadConfirmation();

    //remind guest to download confirmation if ever they have not downloaded it yet
    toast.info("Remember to download your confirmation before leaving.", {
        style: {
            background: "#3b82f6",
            color: "white",
        },
        position: "top-center",
    });
});
</script>

<template>
    <Head title="Reservation Confirmation" />

    <div class="w-full min-h-screen">
        <Header />

        <div class="container max-w-xl px-4 py-8 mx-auto">
            <!-- Check Icon -->
            <div class="flex items-center justify-center mb-8">
                <div class="p-3 bg-green-600 rounded-full">
                    <Check class="w-8 h-8 text-white" />
                </div>
            </div>

            <h1 class="mb-2 text-3xl font-bold text-center">
                Reservation Submitted!
            </h1>
            <p class="mb-8 text-center text-muted-foreground text-neutral-500">
                Your reservation has been successfully submitted. Please wait
                while your reservation is being processed. You can check your
                reservation status update in
                <Link
                    :href="route('reservation.checkStatusForm')"
                    class="text-blue-500 underline"
                >
                    here
                </Link>
                .
            </p>

            <Card id="confirmation" class="rounded-sm shadow">
                <CardHeader class="pb-4 text-center">
                    <h2 class="mb-2 text-sm font-semibold text-neutral-500">
                        RESERVATION CODE
                    </h2>
                    <Code>{{ reservation.reservation_code }}</Code>
                </CardHeader>

                <CardContent class="space-y-6">
                    <div
                        class="flex items-center justify-between p-4 rounded-lg bg-muted"
                    >
                        <div class="font-medium">Status</div>
                        <PendingStatus>{{ reservation.status }}</PendingStatus>
                    </div>

                    <Separator />

                    <div class="space-y-4">
                        <ListItem
                            :icon="Calendar"
                            label="Check In"
                            :value="reservation.check_in_date"
                        />
                        <ListItem
                            :icon="Calendar"
                            label="Check Out"
                            :value="reservation.check_out_date"
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

            <div class="flex items-center mt-3 gap-x-2">
                <LinkButton
                    href="/"
                    variant="outline"
                    class="text-primary-500 border-primary-500 hover:bg-primary-50 hover:text-primary-600"
                    size="lg"
                >
                    <ArrowLeft />Back
                </LinkButton>

                <Button @click="downloadConfirmation" class="flex-1" size="lg">
                    <Download />Download
                </Button>
            </div>
        </div>
    </div>
</template>
