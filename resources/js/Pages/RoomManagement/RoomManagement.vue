<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PageHeader from "@/Components/PageHeader.vue";
import Badge from "@/Components/ui/badge/Badge.vue";
import {
    Bed,
    Ellipsis,
    FilterX,
    Home,
    Pencil,
    Plus,
    SquareArrowOutUpRight,
    Trash,
} from "lucide-vue-next";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
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
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import {
    Popover,
    PopoverContent,
    PopoverField,
    PopoverTrigger,
} from "@/Components/ui/popover";
import TableOrderToggle from "@/Components/ui/table/TableOrderToggle.vue";
import Searchbox from "@/Components/Searchbox.vue";
import type { LaravelPagination } from "@/types/index";
import type {
    Filters,
    Gender,
    Room,
    RoomStatus,
    RoomWithBedCounts,
} from "@/Pages/RoomManagement/room.types";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { Button } from "@/components/ui/button";
import { computed, ref, watch } from "vue";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";

const ROOMS_COLUMNS = [
    "name",
    "eligible_gender",
    "status",
    "beds_count",
    "available_beds",
] as const;

type RoomManagementProps = {
    rooms: LaravelPagination<RoomWithBedCounts>;
    filters: Filters;
};

// Rooms
const { rooms, filters } = defineProps<RoomManagementProps>();

const selectedRoom = ref<Room | null>(null);

const form = useForm<Filters>({
    search: filters.search,
    eligible_gender: filters.eligible_gender,
    status: filters.status,
    sort_by: filters.sort_by,
    sort_order: filters.sort_order ?? "asc",
});

const formHasValue = computed(
    () => form.search || form.eligible_gender || form.status || form.sort_by
);

//Room Filter
const clearFilter = () => {
    form.search = undefined;
    form.eligible_gender = null;
    form.status = null;
    form.sort_by = null;
    form.sort_by = null;
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
        () => form.search,
        () => form.eligible_gender,
        () => form.status,
        () => form.sort_by,
        () => form.sort_order,
    ],
    applyFilter
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
    });
}
</script>

<template>
    <Head title="Room Management" />

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
                        <BreadcrumbPage>Room Management</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <Link :href="route('room.createForm')">
                <Button><Plus />Add Room</Button>
            </Link>
        </div>

        <PageHeader>
            <template #icon><Bed /></template>
            <template #title>Room Management</template>
        </PageHeader>

        <!-- Search, Filter and Sort -->
        <div class="flex mb-2 gap-x-2">
            <Select v-model="form.eligible_gender as Gender">
                <SelectTrigger class="w-40">
                    <SelectValue placeholder="Select a gender" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Gender</SelectLabel>
                        <SelectItem value="any"> Any </SelectItem>
                        <SelectItem value="male"> Male </SelectItem>
                        <SelectItem value="female"> Female </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>

            <Select v-model="form.status as RoomStatus">
                <SelectTrigger class="w-40">
                    <SelectValue placeholder="Select a status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Status</SelectLabel>
                        <SelectItem value="available"> Available </SelectItem>
                        <SelectItem value="fully_occupied">
                            Fully Occupied
                        </SelectItem>
                        <SelectItem value="maintenance">
                            Maintenance
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>

            <Select v-model="form.sort_by as string">
                <SelectTrigger class="w-40">
                    <SelectValue placeholder="Sort by" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Sort by</SelectLabel>
                        <SelectItem value="name"> Name </SelectItem>
                        <SelectItem value="status"> Status </SelectItem>
                        <SelectItem value="eligible_gender">
                            Eligible Gender
                        </SelectItem>
                        <SelectItem value="beds_count"> Beds Count </SelectItem>
                        <SelectItem value="available_beds">
                            Available Beds
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>

            <TableOrderToggle v-if="form.sort_by" v-model="form.sort_order" />

            <Button
                v-if="formHasValue"
                @click="clearFilter"
                variant="destructive"
                size="icon"
            >
                <FilterX />
            </Button>

            <Searchbox class="ml-auto" v-model="form.search" />
        </div>

        <div class="border rounded">
            <Table>
                <TableHeader>
                    <TableRow class="bg-primary-500 hover:bg-primary-600">
                        <TableHead class="text-white"> Room Name </TableHead>
                        <TableHead class="text-white">Beds Count</TableHead>
                        <TableHead class="text-white">
                            Available Beds
                        </TableHead>
                        <TableHead class="text-white">Status</TableHead>
                        <TableHead class="text-white">
                            Eligible Gender
                        </TableHead>
                        <TableHead class="text-right"></TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <template v-if="rooms.data.length > 0">
                        <TableRow
                            v-for="room in rooms.data"
                            :key="room.id"
                            class="text-neutral-800"
                        >
                            <TableCell class="font-medium">
                                {{ room.name }}
                            </TableCell>
                            <TableCell>{{ room.beds_count }}</TableCell>
                            <TableCell>{{ room.available_beds }}</TableCell>
                            <TableCell>
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
                                    {{ room.status }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :severity="
                                        room.eligible_gender === 'male'
                                            ? 'info'
                                            : room.eligible_gender === 'female'
                                            ? 'danger'
                                            : 'secondary'
                                    "
                                >
                                    {{ room.eligible_gender }}
                                </Badge>
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
                                            <Link
                                                :href="
                                                    route('room.show', {
                                                        id: room.id,
                                                    })
                                                "
                                            >
                                                <PopoverField>
                                                    <SquareArrowOutUpRight />View
                                                </PopoverField>
                                            </Link>

                                            <Link
                                                :href="
                                                    route('room.editForm', {
                                                        id: room.id,
                                                    })
                                                "
                                            >
                                                <PopoverField>
                                                    <Pencil />Edit
                                                </PopoverField>
                                            </Link>

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
                        <TableEmpty :colspan="ROOMS_COLUMNS.length">
                            No results.
                        </TableEmpty>
                    </template>
                </TableBody>
            </Table>

            <TableFooter class="flex justify-center py-1.5">
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
        </div>

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
