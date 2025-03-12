<script setup lang="ts">
import { Head, useForm, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from "@/Components/ui/breadcrumb";
import PageHeader from "@/Components/PageHeader.vue";
import { Home, CalendarCheck, Ellipsis, Pencil, FilterX, Maximize } from "lucide-vue-next";
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
import { Filters, Reservation as ReservationWithBeds } from "@/Pages/ReservationManagement/reservations.types";
import type { LaravelPagination, SharedData } from "@/types/index";
import Badge from "@/Components/ui/badge/Badge.vue";
import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover";
import { Button } from "@/Components/ui/button";
import { computed, ref, watch } from "vue";
import PopoverLinkField from "@/Components/ui/popover/PopoverLinkField.vue";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import TableOrderToggle from "@/Components/ui/table/TableOrderToggle.vue";
import Searchbox from "@/Components/Searchbox.vue";
import { debounce } from "@/lib/utils";
import { Office } from "@/Pages/OfficeManagement/office.types";


const RESERVATIONS_COLUMNS = [
    "reservation_code",
    "book_by",
    "check_in_date",
    "check_out_date",
    "total_billing",
    'remaining_balance',
    "total_guests",
    "guest_office",
    "status",
] as const;

type Reservation = Omit<ReservationWithBeds, 'guest_office_id' | 'host_office_id'> & {
    guest_office: Office;
    host_office: Office;
}

type ReservationManagementProps = {
    reservations: LaravelPagination<Reservation>;
    filters: Filters;
};

const { reservations, filters } = defineProps<ReservationManagementProps>();

const page = usePage<SharedData>();

const selectedReservation = ref<Reservation | null>(null);

const form = useForm<Filters>({
    status: filters.status,
    balance: filters.balance,
    search: filters.search,
    sort_by: filters.sort_by,
    sort_order: filters.sort_order ?? "asc",
});


const formHasValue = computed(
    () => form.search || form.balance || form.status || form.sort_by
);

//Room Filter
const clearFilter = () => {
    form.status = null;
    form.balance = null;
    form.search = undefined;
    form.sort_by = null;
    form.sort_order = "asc";
};

function applyFilter() {
    form.get(route("reservation.list"), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

watch(
    [
        () => form.status,
        () => form.balance,
        () => form.search,
        () => form.sort_by,
        () => form.sort_order,
    ],
    debounce(applyFilter, 300)
);

</script>

<template>
    <Head title="Reservation Management" />

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
                        <BreadcrumbPage>Reservation Management</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </div>

        <PageHeader>
            <template #icon><CalendarCheck /></template>
            <template #title>Reservation Management</template>
        </PageHeader>

         <!-- Search, Filter and Sort -->
         <div class="flex mb-2 gap-x-2">
            <Select v-model="form.status">
                <SelectTrigger class="w-40">
                    <SelectValue placeholder="Reservation Status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Status</SelectLabel>
                        <SelectItem value="pending"> Pending </SelectItem>
                        <SelectItem value="checked_in"> Checked In </SelectItem>
                        <SelectItem value="checked_out"> Checked Out </SelectItem>
                        <SelectItem value="canceled"> Canceled </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>

            <Select v-model="form.balance">
                <SelectTrigger class="w-40">
                    <SelectValue placeholder="Balance Status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Balance</SelectLabel>
                        <SelectItem value="paid"> Paid </SelectItem>
                        <SelectItem value="has_balance"> Has Balance </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>

            <Select v-model="form.sort_by">
                <SelectTrigger class="w-40">
                    <SelectValue placeholder="Sort by" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Sort by</SelectLabel>
                        <SelectItem value="reservation_code"> Reservation code </SelectItem>
                        <SelectItem value="first_name"> Book by </SelectItem>
                        <SelectItem value="check_in_date"> Checked in </SelectItem>
                        <SelectItem value="check_out_date"> Checked out </SelectItem>
                        <SelectItem value="total_billing"> Total billing </SelectItem>
                        <SelectItem value="status"> Status </SelectItem>
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
                        <TableHead class="text-white"> Reservation Code </TableHead>
                        <TableHead class="text-white"> Book by </TableHead>
                        <TableHead class="text-white"> Check in </TableHead>
                        <TableHead class="text-white"> Check out </TableHead>
                        <TableHead class="text-white"> Total Billing </TableHead>
                        <TableHead class="text-white"> Remaining Balance </TableHead>
                        <TableHead class="text-white"> Total Guests </TableHead>
                        <TableHead class="text-white"> Guest Office </TableHead>
                        <TableHead class="text-white"> Status </TableHead>
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
                                {{ reservation.first_name }} {{ reservation.middle_initial + "." }} {{ reservation.last_name }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ reservation.check_in_date }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ reservation.check_out_date }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ reservation.total_billing }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ reservation.remaining_balance }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ reservation.guests.length }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ reservation.guest_office.name  }}
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :severity="
                                        reservation.status === 'checked_in'
                                            ? 'success'
                                            : reservation.status === 'checked_out'
                                            ? 'secondary'
                                            : reservation.status === 'canceled'
                                            ? 'danger'
                                            : 'warning' //status: pending
                                    "
                                >
                                {{ reservation.status }}
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
                                          <PopoverLinkField
                                                :href="
                                                    route('reservation.show', {
                                                        id: reservation.id,
                                                    })
                                                "
                                            >
                                                <Maximize />Show
                                            </PopoverLinkField>
                                          <PopoverLinkField
                                                :href="
                                                    route('reservation.editForm', {
                                                        id: reservation.id,
                                                    })
                                                "
                                            >
                                                <Pencil />Edit
                                            </PopoverLinkField>
                                            <!--
                                            <PopoverField
                                                @click="
                                                    showDeleteConfirmation(room)
                                                "
                                                variant="danger"
                                            >
                                                <Trash />Delete
                                            </PopoverField> -->
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
                        :disabled="reservations.current_page === reservations.last_page"
                    />
                    <PaginatorButton
                        variant="end"
                        :href="reservations.last_page_url"
                        :disabled="reservations.current_page === reservations.last_page"
                    />
                </Paginator>
            </TableFooter>
        </div>
    </AuthenticatedLayout>
</template>
