<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PageHeader from "@/Components/PageHeader.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import {
    Home,
    Ellipsis,
    Maximize,
    CalendarClock,
    FilterX,
    Check,
} from "lucide-vue-next";
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
    PopoverTrigger,
} from "@/Components/ui/popover";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Button } from "@/Components/ui/button";
import PopoverLinkField from "@/Components/ui/popover/PopoverLinkField.vue";
import type {
    ReservationFilters,
    ReservationWithBeds,
    WaitingListFilers,
} from "@/Pages/Admin/Reservation/reservation.types";
import type { LaravelPagination, PageProps } from "@/types";
import type { Office } from "@/Pages/Admin/Office/office.types";
import Searchbox from "@/Components/Searchbox.vue";
import { computed, onMounted, watch } from "vue";
import TableOrderToggle from "@/Components/ui/table/TableOrderToggle.vue";
import { debounce, formatDateString, formatDateTimeString } from "@/lib/utils";
import { toast } from "vue-sonner";

const RESERVATIONS_COLUMNS = [
    "reservation_code",
    "requested_date",
    "book_by",
    "check_in_date",
    "check_out_date",
    "total_guests",
    "guest_office",
] as const;

type Reservation = Omit<
    ReservationWithBeds,
    "guest_office_id" | "host_office_id"
> & {
    guest_office: Office;
    host_office: Office;
};

type ReservationManagementProps = {
    reservations: LaravelPagination<Reservation>;
    filters: ReservationFilters;
};

const { reservations, filters } = defineProps<ReservationManagementProps>();

const page = usePage<PageProps>();

const form = useForm<WaitingListFilers>({
    search: filters.search,
    sort_by: filters.sort_by,
    sort_order: filters.sort_order ?? "asc",
});

const formHasValue = computed(() => form.search || form.sort_by);

//Room Filter
const clearFilter = () => {
    form.search = undefined;
    form.sort_by = null;
    form.sort_order = "asc";
};

function applyFilter() {
    form.get(route("reservation.waitingList"), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

watch(
    [() => form.search, () => form.sort_by, () => form.sort_order],
    debounce(applyFilter, 300)
);

// Display flash success or error message as sonner or toast
onMounted(() => {
    if (page.props.flash.success) {
        toast.success(page.props.flash.success, {
            style: {
                background: "#22c55e",
                color: "white",
            },
            position: "top-center",
        });

        setTimeout(() => {
            page.props.flash.success = null;
        }, 300);
    }
});
</script>

<template>
    <Head title="Waiting List" />

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
                        <BreadcrumbPage>Waiting List</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </div>

        <PageHeader>
            <template #icon><CalendarClock /></template>
            <template #title>Waiting List</template>
        </PageHeader>

        <!-- Search, Filter and Sort -->
        <div class="flex mb-2 gap-x-2">
            <Select v-model="form.sort_by">
                <SelectTrigger class="w-40">
                    <SelectValue placeholder="Sort by" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Sort by</SelectLabel>
                        <SelectItem value="reservation_code">
                            Reservation code
                        </SelectItem>
                        <SelectItem value="created_at">
                            Requested date
                        </SelectItem>
                        <SelectItem value="first_name"> Book by </SelectItem>
                        <SelectItem value="check_in_date">
                            Checked in
                        </SelectItem>
                        <SelectItem value="check_out_date">
                            Checked out
                        </SelectItem>
                        <SelectItem value="guest_office_id">
                            Guest Office
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
                        <TableHead class="text-white">
                            Reservation Code
                        </TableHead>
                        <TableHead class="text-white">
                            Requested Date
                        </TableHead>
                        <TableHead class="text-white"> Book by </TableHead>
                        <TableHead class="text-white"> Check in </TableHead>
                        <TableHead class="text-white"> Check out </TableHead>
                        <TableHead class="text-white"> Total Guests </TableHead>
                        <TableHead class="text-white"> Guest Office </TableHead>
                        <TableHead class="text-right"></TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="reservations.data.length > 0">
                        <TableRow
                            v-for="reservation in reservations.data"
                            :key="reservation.id"
                            class="text-neutral-800"
                        >
                            <TableCell class="font-medium">
                                {{ reservation.reservation_code }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{
                                    formatDateTimeString(reservation.created_at)
                                }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ reservation.first_name }}
                                {{
                                    reservation.middle_initial &&
                                    reservation.middle_initial + "."
                                }}
                                {{ reservation.last_name }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{
                                    formatDateString(reservation.check_in_date)
                                }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{
                                    formatDateString(reservation.check_out_date)
                                }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ reservation.guests.length }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ reservation.guest_office.name }}
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
                                                    route(
                                                        'reservation.assignBedsForm',
                                                        {
                                                            id: reservation.id,
                                                        }
                                                    )
                                                "
                                            >
                                                <Check /> Confirm
                                            </PopoverLinkField>

                                            <PopoverLinkField
                                                :href="
                                                    route('reservation.show', {
                                                        id: reservation.id,
                                                    })
                                                "
                                            >
                                                <Maximize />Show
                                            </PopoverLinkField>
                                        </div>
                                    </PopoverContent>
                                </Popover>
                            </TableCell>
                        </TableRow>
                    </template>

                    <template v-else>
                        <TableEmpty :colspan="RESERVATIONS_COLUMNS.length">
                            No results.
                        </TableEmpty>
                    </template>
                </TableBody>
            </Table>

            <TableFooter class="flex justify-center py-1.5">
                <Paginator>
                    <PaginatorButton
                        variant="start"
                        :href="reservations.first_page_url"
                        :disabled="reservations.current_page === 1"
                    />
                    <PaginatorButton
                        variant="prev"
                        :href="reservations.prev_page_url ?? ''"
                        :disabled="reservations.current_page === 1"
                    />
                    <PaginatorInfo
                        :current_page="reservations.current_page"
                        :from="reservations.from"
                        :to="reservations.to"
                        :total="reservations.total"
                    />
                    <PaginatorButton
                        variant="next"
                        :href="reservations.next_page_url ?? ''"
                        :disabled="
                            reservations.current_page === reservations.last_page
                        "
                    />
                    <PaginatorButton
                        variant="end"
                        :href="reservations.last_page_url"
                        :disabled="
                            reservations.current_page === reservations.last_page
                        "
                    />
                </Paginator>
            </TableFooter>
        </div>
    </AuthenticatedLayout>
</template>
