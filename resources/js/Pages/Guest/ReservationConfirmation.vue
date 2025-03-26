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
import { Head, Link, usePage } from "@inertiajs/vue3";
import type { PageProps } from "@/types";
import Header from "@/Components/Header.vue";
import { Button } from "@/Components/ui/button";
import { Reservation } from "@/Pages/Admin/Reservation/reservation.types";
import * as htmlToImage from "html-to-image";
import { toast } from "vue-sonner";
import { onMounted } from "vue";

type ReservationConfirmationProps = {
    canLogin: boolean;
    reservation: Pick<
        Reservation,
        "check_in_date" | "check_out_date" | "status" | "reservation_code"
    > & {
        hostel_office_name: string;
        total_guests: number;
    };
};

const { canLogin, reservation } = defineProps<ReservationConfirmationProps>();

const page = usePage<PageProps>();

function downloadConfirmation() {
    htmlToImage
        .toJpeg(document.getElementById("confirmation") as HTMLElement, {
            quality: 1,
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
        <Header :can-login="canLogin" :user="page.props.auth.user" />

        <div class="container max-w-xl px-4 py-8 mx-auto">
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
                for the admin to process your reservation. You can check your
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
                    <div class="mb-2 text-sm font-semibold text-neutral-500">
                        RESERVATION CODE
                    </div>
                    <div class="py-4 rounded-md bg-neutral-200">
                        <h2
                            class="text-3xl font-bold tracking-wider text-primary-900"
                        >
                            {{ reservation.reservation_code }}
                        </h2>
                    </div>
                </CardHeader>

                <CardContent class="space-y-6">
                    <div class="p-4 rounded-lg bg-muted">
                        <div class="flex items-center justify-between">
                            <div class="font-medium">Status</div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="bg-yellow-500 rounded-full size-2"
                                ></span>
                                <span
                                    class="font-medium text-yellow-500 capitalize"
                                >
                                    {{ reservation.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <Calendar class="mt-0.5 w-5 h-5 text-neutral-500" />
                            <div>
                                <div class="font-medium">Check In</div>
                                <div class="text-neutral-500">
                                    {{ reservation.check_in_date }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <Calendar class="mt-0.5 w-5 h-5 text-neutral-500" />
                            <div>
                                <div class="font-medium">Check Out</div>
                                <div class="text-neutral-500">
                                    {{ reservation.check_out_date }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <Users class="mt-0.5 w-5 h-5 text-neutral-500" />
                            <div>
                                <div class="font-medium">Total Guests</div>
                                <div class="text-neutral-500">
                                    {{ reservation.total_guests }}
                                    {{
                                        reservation.total_guests > 1
                                            ? "guests"
                                            : "guest"
                                    }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <Hotel class="mt-0.5 w-5 h-5 text-neutral-500" />
                            <div>
                                <div class="font-medium">Hostel Office</div>
                                <div class="text-neutral-500">
                                    {{ reservation.hostel_office_name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="flex items-center mt-3 gap-x-2">
                <Link href="/">
                    <Button
                        variant="outline"
                        class="text-primary-500 border-primary-500 hover:bg-primary-50 hover:text-primary-600"
                        size="lg"
                    >
                        <ArrowLeft />Back
                    </Button>
                </Link>
                <Button @click="downloadConfirmation" class="flex-1" size="lg">
                    <Download />Download
                </Button>
            </div>
        </div>
    </div>
</template>
