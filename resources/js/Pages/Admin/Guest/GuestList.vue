<script setup lang="ts">
import GenderBadge from "@/Components/GenderBadge.vue";
import PageHeader from "@/Components/PageHeader.vue";
import Searchbox from "@/Components/Searchbox.vue";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import { Button } from "@/Components/ui/button";
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
import type { Region } from "@/Pages/Admin/Office/office.types";
import type { LaravelPagination } from "@/types";
import { Head, useForm } from "@inertiajs/vue3";
import { FilterX, Home, Users } from "lucide-vue-next";
import { computed, watch } from "vue";

const GUESTS_COLUMNS = [
    "name",
    "gender",
    "phone",
    "region",
    "office_id",
    "check_in_date",
    "check_out_date",
] as const;

type GuestListProps = {
    guests: LaravelPagination<Guest>;
    filters: GuestsFilters;
    regions: Region[];
};

const { guests, filters } = defineProps<GuestListProps>();

const form = useForm<Partial<GuestsFilters>>({
    region_id: filters.region_id,
    gender: filters.gender,
    search: filters.search,
    sort_by: filters.sort_by,
    sort_order: filters.sort_order ?? "asc",
});

const formHasValue = computed(
    () => form.region_id || form.gender || form.search || form.sort_by
);

function clearFilters() {
    form.region_id = undefined;
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
        () => form.region_id,
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
                        <BreadcrumbPage>Guests</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </div>

        <PageHeader>
            <template #icon><Users /></template>
            <template #title>Guests</template>
        </PageHeader>

        <!-- Search, Filter and Sort -->
        <div class="flex mb-2 gap-x-2">
            <Select v-model="form.region_id">
                <SelectTrigger class="w-40">
                    <SelectValue placeholder="Select a region" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Region</SelectLabel>
                        <SelectItem
                            v-for="region in regions"
                            :key="region.id"
                            :value="region.id"
                        >
                            {{ region.name }}
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>

            <Select v-model="form.gender">
                <SelectTrigger class="w-40">
                    <SelectValue placeholder="Select a gender" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Gender</SelectLabel>
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
                        <SelectItem value="first_name"> First Name </SelectItem>
                        <SelectItem value="last_name"> Last Name </SelectItem>
                        <SelectItem value="gender"> Gender </SelectItem>
                        <SelectItem value="phone"> Phone </SelectItem>
                        <SelectItem value="region_id"> Region </SelectItem>
                        <SelectItem value="office_id"> Office </SelectItem>
                        <SelectItem value="check_in_date">
                            Check In
                        </SelectItem>
                        <SelectItem value="check_out_date">
                            Check Out
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>

            <TableOrderToggle v-if="form.sort_by" v-model="form.sort_order" />

            <Button
                v-if="formHasValue"
                @click="clearFilters"
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
                        <TableHead class="text-white"> Name </TableHead>
                        <TableHead class="text-white"> Gender </TableHead>
                        <TableHead class="text-white"> Phone </TableHead>
                        <TableHead class="text-white"> Region </TableHead>
                        <TableHead class="text-white"> Office </TableHead>
                        <TableHead class="text-white"> Check In </TableHead>
                        <TableHead class="text-white"> Check Out </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="guests && guests.data.length > 0">
                        <TableRow
                            v-for="guest in guests.data"
                            :key="guest.id"
                            class="text-neutral-800"
                        >
                            <TableCell class="font-medium capitalize">
                                {{ `${guest.first_name} ${guest.last_name}` }}
                            </TableCell>
                            <TableCell>
                                <GenderBadge :gender="guest.gender as Gender" />
                            </TableCell>
                            <TableCell> {{ guest.phone ?? " - " }} </TableCell>
                            <TableCell>
                                Region {{ guest.office.region.name }}
                            </TableCell>
                            <TableCell> {{ guest.office.name }} </TableCell>
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
                        <TableEmpty :colspan="GUESTS_COLUMNS.length">
                            No results.
                        </TableEmpty>
                    </template>
                </TableBody>
            </Table>
            <TableFooter class="flex justify-center py-1.5">
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
        </div>
    </AuthenticatedLayout>
</template>
