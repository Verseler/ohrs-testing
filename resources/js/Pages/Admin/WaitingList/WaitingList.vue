<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PageHeader from "@/Components/PageHeader.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { Ellipsis, Maximize, CalendarClock } from "lucide-vue-next";
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
import { Button } from "@/Components/ui/button";
import PopoverLinkField from "@/Components/ui/popover/PopoverLinkField.vue";
import type {
    ReservationFilters,
    ReservationWithBeds,
    WaitingListFilers,
} from "@/Pages/Admin/Reservation/reservation.types";
import type { LaravelPagination } from "@/types";
import type { Office } from "@/Pages/Admin/Office/office.types";
import Searchbox from "@/Components/Searchbox.vue";
import { computed, onMounted, watch } from "vue";
import TableOrderToggle from "@/Components/ui/table/TableOrderToggle.vue";
import { debounce, formatDateString, formatDateTimeString } from "@/lib/utils";
import { usePoll } from "@inertiajs/vue3";
import { showSuccess } from "@/Composables/useFlash";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import { data } from "@/Pages/Admin/WaitingList/data";
import SelectField from "@/Components/SelectField.vue";
import TableRowHeader from "@/Components/ui/table/TableRowHeader.vue";
import TableContainer from "@/Components/ui/table/TableContainer.vue";
import ClearFilterButton from "@/Components/ui/table/ClearFilterButton.vue";

usePoll(10000);

type Reservation = Omit<ReservationWithBeds, "host_office_id"> & {
    host_office: Office;
};

type ReservationManagementProps = {
    reservations: LaravelPagination<Reservation>;
    filters: Omit<ReservationFilters, "payment_type">;
};

const { reservations, filters } = defineProps<ReservationManagementProps>();

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

onMounted(() => showSuccess());
</script>

<template>
    <Head title="Waiting List" />

    <AuthenticatedLayout>
        <Breadcrumbs :items="data.breadcrumbs" />

        <PageHeader>
            <template #icon><CalendarClock /></template>
            <template #title>Waiting List</template>
        </PageHeader>

        <!-- Search, Filter and Sort -->
        <div
            class="flex flex-col-reverse justify-between gap-2 mb-2 md:flex-row"
        >
            <div class="flex flex-col gap-2 md:flex-row">
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

            <Searchbox class="ml-auto" v-model="form.search" />
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
                                            <Maximize />Show
                                        </PopoverLinkField>
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
