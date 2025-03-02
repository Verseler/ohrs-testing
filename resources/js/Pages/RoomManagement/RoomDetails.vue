<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import { Head } from "@inertiajs/vue3";
import BackLink from "@/Components/BackLink.vue";
import PageHeader from "@/Components/PageHeader.vue";
import { Bed as BedIcon, Check, Home, Wrench, X } from "lucide-vue-next";
import { Bed, RoomWithBedCounts } from "@/Pages/RoomManagement/room.types";
import { capitalized } from "@/lib/utils";
import { Badge } from "@/Components/ui/badge";
import StatusCard from "@/Components/StatusCard.vue";
import BedStatusCard from "@/Components/BedStatusCard.vue";
import { Card, CardDescription, CardTitle } from "@/Components/ui/card";
import { Separator } from "@/Components/ui/separator";

type RoomDetailsProps = {
    room: RoomWithBedCounts & { beds: Bed[] };
};

const { room } = defineProps<RoomDetailsProps>();
</script>

<template>
    <Head title="Room Details" />

    <AuthenticatedLayout>
        <div class="flex justify-between min-h-12">
            <Breadcrumb>
                <BreadcrumbList>
                    <BreadcrumbItem>
                        <BreadcrumbLink :href="route('dashboard')">
                            <Home class="size-4" />
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbLink :href="route('room.list')">
                            Room Management
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>Room Details</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink />
        </div>

        <PageHeader>
            <template #icon><BedIcon /></template>
            <template #title>Room Details</template>
        </PageHeader>

        <div class="grid gap-10 md:grid-cols-2">
            <!-- Left side (Room Details) -->
            <div class="max-w-lg h-96">
                <h2 class="mb-4 text-2xl font-bold">Room</h2>
                <Card class="p-4 border rounded-lg shadow-sm">
                    <CardTitle>
                        <div class="flex items-center justify-between mb-3">
                            <div class="block text-2xl font-medium">
                                {{ room.name }}
                            </div>
                            <div>
                                Total Beds:
                                <Badge class="px-4 ml-auto" severity="info">
                                    {{ room.beds_count }}
                                </Badge>
                            </div>
                        </div>
                    </CardTitle>

                    <CardDescription>
                        <div class="space-x-2">
                            <Badge
                                :severity="
                                    room.status === 'available'
                                        ? 'success'
                                        : room.status === 'fully_occupied'
                                        ? 'danger'
                                        : room.status === 'maintenance'
                                        ? 'warning'
                                        : 'secondary'
                                "
                            >
                                {{ `Room Status: ${room.status}` }}
                            </Badge>

                            <Badge
                                :severity="
                                    capitalized(room.eligible_gender) === 'male'
                                        ? 'info'
                                        : capitalized(room.eligible_gender) ===
                                          'female'
                                        ? 'danger'
                                        : 'secondary'
                                "
                            >
                                {{
                                    `Gender: ${capitalized(
                                        room.eligible_gender
                                    )}`
                                }}
                            </Badge>
                        </div>

                        <Separator class="mt-4 mb-2" />

                        <div>
                            <h3 class="mb-2 text-lg text-neutral-700">
                                Beds Status Summary
                            </h3>
                            <div class="space-y-2">
                                <StatusCard severity="success">
                                    <template #icon><Check /></template>
                                    <template #label>AVAILABLE</template>
                                    <template #value>
                                        {{ room.available_beds }}
                                    </template>
                                </StatusCard>
                                <StatusCard severity="danger">
                                    <template #icon><X /></template>
                                    <template #label>OCCUPIED</template>
                                    <template #value>
                                        {{ room.occupied_beds }}
                                    </template>
                                </StatusCard>
                                <StatusCard severity="warning">
                                    <template #icon><Wrench /></template>
                                    <template #label>MAINTENANCE</template>
                                    <template #value>
                                        {{ room.under_maintenance_beds }}
                                    </template>
                                </StatusCard>
                            </div>
                        </div>
                    </CardDescription>
                </Card>
            </div>

            <!-- Right side (Bed Status) -->
            <div class="max-w-md">
                <h2 class="mb-4 text-2xl font-bold">Bed Status</h2>
                <div class="flex flex-wrap gap-3 py-3">
                    <BedStatusCard
                        v-for="bed in room.beds"
                        :key="bed.id"
                        :severity="
                            bed.status === 'available'
                                ? 'success'
                                : bed.status === 'occupied'
                                ? 'danger'
                                : bed.status === 'maintenance'
                                ? 'secondary'
                                : 'primary'
                        "
                        :value="bed.name"
                        size="small"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
