<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PageHeader from "@/Components/PageHeader.vue";
import { CalendarCheck, Ellipsis, Maximize } from "lucide-vue-next";
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
import type {
    ReservationFilters,
    Reservation as ReservationWithBeds,
} from "@/Pages/Admin/Reservation/reservation.types";
import type { LaravelPagination } from "@/types/index";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/Components/ui/popover";
import { Button } from "@/Components/ui/button";
import { computed, watch } from "vue";
import PopoverLinkField from "@/Components/ui/popover/PopoverLinkField.vue";
import TableOrderToggle from "@/Components/ui/table/TableOrderToggle.vue";
import Searchbox from "@/Components/Searchbox.vue";
import { debounce } from "@/lib/utils";
import type { Office } from "@/Pages/Admin/Office/office.types";
import StatusBadge from "@/Components/StatusBadge.vue";
import { usePoll } from "@inertiajs/vue3";
import SelectField from "@/Components/SelectField.vue";
import { data } from "@/Pages/Admin/Reservation/data";
import TableContainer from "@/Components/ui/table/TableContainer.vue";
import TableRowHeader from "@/Components/ui/table/TableRowHeader.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import ClearFilterButton from "@/Components/ui/table/ClearFilterButton.vue";
import PaymentTypeBadge from "@/Components/PaymentTypeBadge.vue";

usePoll(10000);

type Reservation = Omit<ReservationWithBeds, "host_office_id"> & {
    host_office: Office;
};

type ReservationManagementProps = {
    reservations: LaravelPagination<Reservation>;
    filters: ReservationFilters;
};

const { reservations, filters } = defineProps<ReservationManagementProps>();

const form = useForm<ReservationFilters>({
    status: filters.status,
    balance: filters.balance,
    payment_type: filters.payment_type,
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
        () => form.payment_type,
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
        <Breadcrumbs :items="data.breadcrumbs" />
        <PageHeader>
            <template #icon><CalendarCheck /></template>
            <template #title>Reservation Management</template>
        </PageHeader>

        <!-- Search, Filter and Sort -->
        <div
            class="flex flex-col-reverse justify-between gap-2 mb-2 md:flex-row"
        >
            <div class="flex flex-col gap-2 md:flex-row">
                <SelectField
                    v-model="(form.status as string)"
                    placeholder="Reservation Status"
                    label="Status"
                    :items="data.filterStatus"
                />
                <SelectField
                    v-model="form.balance"
                    placeholder="Balance Status"
                    label="Balance"
                    :items="data.filterBalance"
                />
                <SelectField
                    v-model="form.payment_type"
                    placeholder="Payment Type"
                    label="Payment Type"
                    :items="data.filterPaymentType"
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

            <Searchbox v-model="form.search" />
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
                    <template v-if="reservations.data.length > 0">
                        <TableRow
                            v-for="reservation in reservations.data"
                            :key="reservation.id"
                        >
                            <TableCell class="font-medium">
                                {{ reservation.reservation_code }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ reservation.first_name }}
                                {{
                                    reservation?.middle_initial &&
                                    reservation.middle_initial + "."
                                }}
                                {{ reservation.last_name }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ reservation.check_in_date }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ reservation.check_out_date }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ reservation.total_billings }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ reservation.remaining_balance }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ reservation.guests.length }}
                            </TableCell>
                            <TableCell>
                                <PaymentTypeBadge
                                    :payment-type="reservation.payment_type"
                                />
                            </TableCell>
                            <TableCell>
                                <StatusBadge :status="reservation.status" />
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
        </TableContainer>
    </AuthenticatedLayout>
</template>
