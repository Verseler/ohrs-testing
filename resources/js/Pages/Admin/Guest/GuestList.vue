<script setup lang="ts">
import GenderBadge from "@/Components/GenderBadge.vue";
import PageHeader from "@/Components/PageHeader.vue";
import Searchbox from "@/Components/Searchbox.vue";
import {
    Paginator,
    PaginatorButton,
    PaginatorInfo,
} from "@/Components/ui/paginator";
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import TableOrderToggle from "@/Components/ui/table/TableOrderToggle.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { debounce, formatDateString } from "@/lib/utils";
import type { Gender, Guest, GuestsFilters } from "@/Pages/Guest/guest.types";
import type { LaravelPagination } from "@/types";
import { Head, useForm } from "@inertiajs/vue3";
import { Users } from "lucide-vue-next";
import { computed, watch } from "vue";
import { usePoll } from "@inertiajs/vue3";
import TableRowHeader from "@/Components/ui/table/TableRowHeader.vue";
import TableContainer from "@/Components/ui/table/TableContainer.vue";
import SelectField from "@/Components/SelectField.vue";
import { data } from "@/Pages/Admin/Guest/data";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import ClearFilterButton from "@/Components/ui/table/ClearFilterButton.vue";

usePoll(8000);

type GuestListProps = {
    guests: LaravelPagination<Guest>;
    filters: GuestsFilters;
};

const { guests, filters } = defineProps<GuestListProps>();

const form = useForm<Partial<GuestsFilters>>({
    gender: filters.gender,
    search: filters.search,
    sort_by: filters.sort_by,
    sort_order: filters.sort_order ?? "asc",
});

const formHasValue = computed(
    () => form.gender || form.search || form.sort_by
);

function clearFilters() {
    form.gender = undefined;
    form.search = undefined;
    form.sort_by = undefined;
    form.sort_order = "asc";
}

function applyFilter() {
    form.get(route("guest.list"), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

watch(
    [
        () => form.gender,
        () => form.search,
        () => form.sort_by,
        () => form.sort_order,
    ],
    debounce(applyFilter, 300)
);
</script>

<template>
    <Head title="Guests" />

    <AuthenticatedLayout>
        <Breadcrumbs :items="data.breadcrumbs" />
        <PageHeader>
            <template #icon><Users /></template>
            <template #title>Guests</template>
        </PageHeader>

        <!-- Search, Filter and Sort -->
        <div
            class="flex flex-col-reverse justify-between gap-2 mb-2 md:flex-row"
        >
            <div class="flex flex-col gap-2 md:flex-row">
                <SelectField
                    v-model="form.gender"
                    :items="data.filterGender"
                    placeholder="Select a gender"
                    label="Gender"
                />
                <SelectField
                    v-model="form.sort_by"
                    :items="data.sortBy"
                    placeholder="Sort by"
                    label="Sort by"
                />

                <div class="ml-auto space-x-2">
                    <TableOrderToggle
                        v-if="form.sort_by"
                        v-model="form.sort_order"
                    />
                    <ClearFilterButton
                        v-if="formHasValue"
                        @click="clearFilters"
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
                    <template v-if="guests && guests.data.length > 0">
                        <TableRow v-for="guest in guests.data" :key="guest.id">
                            <TableCell class="font-medium capitalize">
                                {{ `${guest.first_name} ${guest.last_name}` }}
                            </TableCell>
                            <TableCell>
                                <GenderBadge :gender="guest.gender as Gender" />
                            </TableCell>
                            <TableCell>
                                {{ guest.office }}
                            </TableCell>
                            <TableCell>
                                {{
                                    formatDateString(
                                        guest.reservation.check_in_date
                                    )
                                }}
                            </TableCell>
                            <TableCell>
                                {{
                                    formatDateString(
                                        guest.reservation.check_out_date
                                    )
                                }}
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
                        :href="guests.first_page_url"
                        :disabled="guests.current_page === 1"
                    />
                    <PaginatorButton
                        variant="prev"
                        :href="guests.prev_page_url ?? ''"
                        :disabled="guests.current_page === 1"
                    />
                    <PaginatorInfo
                        :current_page="guests.current_page"
                        :from="guests.from"
                        :to="guests.to"
                        :total="guests.total"
                    />
                    <PaginatorButton
                        variant="next"
                        :href="guests.next_page_url ?? ''"
                        :disabled="guests.current_page === guests.last_page"
                    />
                    <PaginatorButton
                        variant="end"
                        :href="guests.last_page_url"
                        :disabled="guests.current_page === guests.last_page"
                    />
                </Paginator>
            </TableFooter>
        </TableContainer>
    </AuthenticatedLayout>
</template>
