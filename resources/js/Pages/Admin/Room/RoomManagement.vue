<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PageHeader from "@/Components/PageHeader.vue";
import { Bed as BedIcon, Ellipsis, Pencil, Plus, Trash } from "lucide-vue-next";
import {
    Table,
    TableBody,
    TableFooter,
    TableEmpty,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import {
    Paginator,
    PaginatorButton,
    PaginatorInfo,
} from "@/Components/ui/paginator";
import {
    Popover,
    PopoverContent,
    PopoverField,
    PopoverTrigger,
} from "@/Components/ui/popover";
import TableOrderToggle from "@/Components/ui/table/TableOrderToggle.vue";
import type { LaravelPagination } from "@/types/index";
import type {
    Bed,
    RoomFilters,
    Room,
    RoomWithBedCounts,
} from "@/Pages/Admin/Room/room.types";
import { Head, router, useForm } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import { computed, ref, watch } from "vue";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import PopoverLinkField from "@/Components/ui/popover/PopoverLinkField.vue";
import { debounce, formatDate, tomorrowDate } from "@/lib/utils";
import { Label } from "@/Components/ui/label";
import { usePoll } from "@inertiajs/vue3";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import { data } from "@/Pages/Admin/Room/data";
import SelectField from "@/Components/SelectField.vue";
import TableContainer from "@/Components/ui/table/TableContainer.vue";
import TableRowHeader from "@/Components/ui/table/TableRowHeader.vue";
import AvailabilityBadge from "@/Components/AvailabilityBadge.vue";
import GenderBadge from "@/Components/GenderBadge.vue";
import ClearFilterButton from "@/Components/ui/table/ClearFilterButton.vue";
import LinkButton from "@/Components/LinkButton.vue";
import { InputDate } from "@/Components/ui/input";

usePoll(20000);

type RoomManagementProps = {
    rooms: LaravelPagination<RoomWithBedCounts>;
    filters: RoomFilters;
};

// Rooms
const { rooms, filters } = defineProps<RoomManagementProps>();

function getBedPrice(beds: Bed[]) {
    if (beds.length === 0) {
        return "N/A";
    }

    const minPrice = Math.min(...beds.map((bed) => bed.price));
    const maxPrice = Math.max(...beds.map((bed) => bed.price));

    if (minPrice === maxPrice) {
        return `₱${minPrice}`;
    }

    return `${minPrice} - ${maxPrice}`;
}

const selectedRoom = ref<Room | null>(null);

const form = useForm({
    check_in_date: filters.check_in_date ?? formatDate(new Date()),
    check_out_date: filters.check_in_date ?? formatDate(tomorrowDate()),
    eligible_gender: filters.eligible_gender,
    sort_by: filters.sort_by,
    sort_order: filters.sort_order ?? "asc",
});

const formHasValue = computed(() => form.eligible_gender || form.sort_by);

//Room Filter
const clearFilter = () => {
    form.eligible_gender = undefined;
    form.sort_by = undefined;
    form.sort_order = "asc";
};

function applyFilter() {
    form.get(route("room.list"), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

watch(
    [
        () => form.check_out_date,
        () => form.check_in_date,
        () => form.eligible_gender,
        () => form.sort_by,
        () => form.sort_order,
    ],
    debounce(applyFilter, 300)
);

//Delete Confirmation Dialog
const deleteConfirmation = ref(false);

function showDeleteConfirmation(room: Room) {
    selectedRoom.value = room;

    deleteConfirmation.value = true;
}

function handleDeleteRoom() {
    if (!selectedRoom.value) return;

    router.visit(route("room.delete", { id: selectedRoom.value.id }), {
        method: "delete",
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            deleteConfirmation.value = false;
            selectedRoom.value = null;
        },
    });
}
</script>

<template>
    <Head title="Room Management" />

    <AuthenticatedLayout>
        <div class="flex justify-between min-h-12">
            <Breadcrumbs :items="data.breadcrumbs" />

            <LinkButton :href="route('room.createForm')">
                <Plus /><span>Add Room</span>
            </LinkButton>
        </div>

        <PageHeader>
            <template #icon><BedIcon /></template>
            <template #title>Room Management</template>
        </PageHeader>

        <!-- Filter and Sort -->
        <div
            class="flex flex-col-reverse gap-2 justify-between mb-2 md:flex-row"
        >
            <div class="flex flex-col gap-2 md:flex-row">
                <SelectField
                    v-model="form.eligible_gender"
                    placeholder="Select a gender"
                    label="Gender"
                    :items="data.filterGender"
                />
                <SelectField
                    v-model="form.sort_by"
                    placeholder="Sort by"
                    label="Sort by"
                    :items="data.sortBy"
                />

                <div class="ml-auto space-x-2">
                    <TableOrderToggle
                        v-if="form.sort_by"
                        v-model="form.sort_order"
                    />
                    <ClearFilterButton
                        v-if="formHasValue"
                        @click="clearFilter"
                    />
                </div>
            </div>

            <div
                class="gap-x-2 items-center space-y-2 md:space-y-0 md:ml-auto md:flex md:h-9"
            >
                <div class="relative">
                    <Label
                        for="check_in"
                        class="absolute text-[0.65rem] text-neutral-500 z-10 -top-2 left-1.5 bg-white"
                    >
                        Check In
                    </Label>
                    <InputDate
                        id="check_in"
                        v-model="form.check_in_date"
                        :min="formatDate(new Date())"
                        :max="form.check_out_date"
                        class="!h-10 min-w-52"
                    />
                </div>
                <div class="relative">
                    <Label
                        for="check_out"
                        class="absolute text-[0.65rem] text-neutral-500 z-10 -top-2 left-1.5 bg-white"
                    >
                        Check Out
                    </Label>
                    <InputDate
                        id="check_out"
                        v-model="form.check_out_date"
                        :min="form.check_in_date"
                        class="!h-10 min-w-52"
                    />
                </div>
            </div>
        </div>

        <TableContainer>
            <Table>
                <TableHeader>
                    <TableRowHeader>
                        <TableHead v-for="head in data.tableHeads">
                            {{ head }}
                        </TableHead>
                    </TableRowHeader>
                </TableHeader>

                <TableBody>
                    <template v-if="rooms.data.length > 0">
                        <TableRow v-for="room in rooms.data" :key="room.id">
                            <TableCell class="font-medium">
                                {{ room.name }}
                            </TableCell>
                            <TableCell>
                                {{ room.beds_count }}
                            </TableCell>
                            <TableCell>
                                {{ room.available_beds }}
                            </TableCell>
                            <TableCell>
                                <AvailabilityBadge
                                    :available="
                                        room.available_beds !== undefined &&
                                        room.available_beds !== null &&
                                        room.available_beds <= 0
                                    "
                                />
                            </TableCell>
                            <TableCell>
                                <GenderBadge :gender="room.eligible_gender" />
                            </TableCell>
                            <TableCell>
                                <span class="text-xs text-neutral-700">
                                    {{ getBedPrice(room.beds ?? []) }}
                                </span>
                            </TableCell>
                            <TableCell class="text-right">
                                <Popover>
                                    <PopoverTrigger as-child>
                                        <Button size="icon" variant="ghost">
                                            <Ellipsis
                                                class="text-neutral-400 hover:text-neutral-500"
                                            />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="p-0 max-w-28">
                                        <div class="flex flex-col">
                                            <PopoverLinkField
                                                :href="
                                                    route('room.editForm', {
                                                        id: room.id,
                                                    })
                                                "
                                            >
                                                <Pencil />Edit
                                            </PopoverLinkField>

                                            <PopoverField
                                                @click="
                                                    showDeleteConfirmation(room)
                                                "
                                                variant="danger"
                                            >
                                                <Trash />Delete
                                            </PopoverField>
                                        </div>
                                    </PopoverContent>
                                </Popover>
                            </TableCell>
                        </TableRow>
                    </template>

                    <template v-else>
                        <TableEmpty :colspan="data.tableHeads.length">
                            No results.
                        </TableEmpty>
                    </template>
                </TableBody>
            </Table>

            <TableFooter>
                <Paginator>
                    <PaginatorButton
                        variant="start"
                        :href="rooms.first_page_url"
                        :disabled="rooms.current_page === 1"
                    />
                    <PaginatorButton
                        variant="prev"
                        :href="rooms.prev_page_url ?? ''"
                        :disabled="rooms.current_page === 1"
                    />
                    <PaginatorInfo
                        :current_page="rooms.current_page"
                        :from="rooms.from"
                        :to="rooms.to"
                        :total="rooms.total"
                    />
                    <PaginatorButton
                        variant="next"
                        :href="rooms.next_page_url ?? ''"
                        :disabled="rooms.current_page === rooms.last_page"
                    />
                    <PaginatorButton
                        variant="end"
                        :href="rooms.last_page_url"
                        :disabled="rooms.current_page === rooms.last_page"
                    />
                </Paginator>
            </TableFooter>
        </TableContainer>

        <Alert
            :open="deleteConfirmation"
            @update:open="deleteConfirmation = $event"
            :onConfirm="handleDeleteRoom"
            title="Are you sure you want to delete this room?"
            description="This action cannot be undone and will remove all room data."
            severity="danger"
            confirm-label="Delete"
        />
    </AuthenticatedLayout>
</template>
