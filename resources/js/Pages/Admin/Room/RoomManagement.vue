<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PageHeader from "@/Components/PageHeader.vue";
import Badge from "@/Components/ui/badge/Badge.vue";
import {
    Bed as BedIcon,
    Ellipsis,
    FilterX,
    Home,
    Pencil,
    Plus,
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
import type { LaravelPagination, PageProps } from "@/types/index";
import type {
    Bed,
    RoomFilters,
    Room,
    RoomWithBedCounts,
} from "@/Pages/Admin/Room/room.types";
import type { Gender } from "@/Pages/Guest/guest.types";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import { computed, ref, watch } from "vue";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import PopoverLinkField from "@/Components/ui/popover/PopoverLinkField.vue";
import { debounce, tomorrowDate, yesterdayDate } from "@/lib/utils";
import { toast } from "vue-sonner";
import { Label } from "@/Components/ui/label";
import DatePicker from "@/Components/DatePicker.vue";
import { usePoll } from "@inertiajs/vue3";

usePoll(5000);

const ROOMS_COLUMNS = [
    "name",
    "eligible_gender",
    "bed price",
    "beds_count",
    "status",
    "available_beds",
] as const;

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

const page = usePage<PageProps>();

const selectedRoom = ref<Room | null>(null);

const form = useForm({
    check_in_date: filters.check_in_date ?? new Date(),
    check_out_date: filters.check_in_date ?? tomorrowDate(),
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

// Display flash success or error message as sonner or toast
watch(
    () => page.props.flash.error,
    () => {
        if (page.props.flash.error) {
            toast.error(page.props.flash.error, {
                style: {
                    background: "#ef4444",
                    color: "white",
                },
                position: "top-center",
            });

            setTimeout(() => {
                page.props.flash.error = null;
            }, 300);
        }
    }
);

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
            <template #icon><BedIcon /></template>
            <template #title>Room Management</template>
        </PageHeader>

        <!-- Filter and Sort -->
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

            <Select v-model="form.sort_by as string">
                <SelectTrigger class="w-40">
                    <SelectValue placeholder="Sort by" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Sort by</SelectLabel>
                        <SelectItem value="name"> Name </SelectItem>
                        <SelectItem value="eligible_gender">
                            Eligible Gender
                        </SelectItem>
                        <SelectItem value="beds_count"> Total Beds </SelectItem>
                        <SelectItem value="available_beds">
                            Available Beds
                        </SelectItem>
                        <SelectItem value="bed_price"> Bed Price </SelectItem>
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

            <div class="flex items-center ml-auto gap-x-2 h-9">
                <div class="relative">
                    <Label
                        for="check_in"
                        class="absolute text-[0.65rem] text-neutral-500 z-10 -top-2 left-1.5 bg-white"
                    >
                        Check In
                    </Label>
                    <DatePicker
                        id="check_in"
                        class="text-sm rounded shadow-sm max-h-10 min-w-40 border-neutral-200"
                        calendar-class="-right-10"
                        v-model="form.check_in_date"
                        :min-value="yesterdayDate()"
                        :max-value="form.check_out_date"
                    />
                </div>
                <div class="relative">
                    <Label
                        for="check_out"
                        class="absolute text-[0.65rem] text-neutral-500 z-10 -top-2 left-1.5 bg-white"
                    >
                        Check Out
                    </Label>
                    <DatePicker
                        id="check_out"
                        class="text-sm rounded shadow-sm max-h-10 min-w-40 border-neutral-200"
                        calendar-class="right-0"
                        v-model="form.check_out_date"
                        :min-value="form.check_in_date"
                    />
                </div>
            </div>
        </div>

        <div class="border rounded">
            <Table>
                <TableHeader>
                    <TableRow class="bg-primary-500 hover:bg-primary-600">
                        <TableHead class="text-white"> Room Name </TableHead>
                        <TableHead class="text-white"> Total Beds </TableHead>

                        <TableHead class="text-white">
                            Available Beds
                        </TableHead>
                        <TableHead class="text-white"> Status </TableHead>
                        <TableHead class="text-white">
                            Eligible Gender
                        </TableHead>
                        <TableHead class="text-white"> Bed Price </TableHead>
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
                                    v-if="
                                        room.available_beds !== null &&
                                        room.available_beds !== undefined
                                    "
                                    :severity="
                                        room.available_beds <= 0
                                            ? 'danger'
                                            : 'success'
                                    "
                                >
                                    {{
                                        room.available_beds <= 0
                                            ? "Fully Occupied"
                                            : "Available"
                                    }}
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
