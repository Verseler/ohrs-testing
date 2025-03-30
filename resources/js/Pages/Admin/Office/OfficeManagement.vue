<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PageHeader from "@/Components/PageHeader.vue";
import { Ellipsis, FilterX, Hotel, Pencil, Plus, Trash } from "lucide-vue-next";
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
import Searchbox from "@/Components/Searchbox.vue";
import type { LaravelPagination } from "@/types/index";
import type {
    Office,
    OfficeFilters,
    Region,
} from "@/Pages/Admin/Office/office.types";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import { computed, onMounted, ref, watch } from "vue";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import PopoverLinkField from "@/Components/ui/popover/PopoverLinkField.vue";
import { debounce } from "@/lib/utils";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { usePoll } from "@inertiajs/vue3";
import { data } from "@/Pages/Admin/Office/data";
import SelectField from "@/Components/SelectField.vue";
import TableRowHeader from "@/Components/ui/table/TableRowHeader.vue";
import TableContainer from "@/Components/ui/table/TableContainer.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import { showError } from "@/Composables/useFlash";

usePoll(5000);

type OfficeManagementProps = {
    offices: LaravelPagination<Office>;
    filters: OfficeFilters;
    regions: Region[];
};

const { offices, filters, regions } = defineProps<OfficeManagementProps>();

const selectedOffice = ref<Office | null>(null);

const form = useForm<Partial<OfficeFilters>>({
    region_id: filters.region_id,
    search: filters.search,
    sort_by: filters.sort_by,
    sort_order: filters.sort_order ?? "asc",
});

const formHasValue = computed(
    () => form.region_id || form.search || form.sort_by
);

//Room Filter
function clearFilter() {
    form.region_id = undefined;
    form.search = undefined;
    form.sort_by = undefined;
    form.sort_order = "asc";
}

function applyFilter() {
    form.get(route("office.list"), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

watch(
    [
        () => form.region_id,
        () => form.search,
        () => form.sort_by,
        () => form.sort_order,
    ],
    debounce(applyFilter, 300)
);

//display error
onMounted(() => showError());



//Delete Confirmation Dialog
const deleteConfirmation = ref(false);

function showDeleteConfirmation(office: Office) {
    selectedOffice.value = office;
    deleteConfirmation.value = true;
}

function handleDeleteOffice() {
    if (!selectedOffice.value) return;

    router.delete(route("office.delete", { id: selectedOffice.value.id }), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            deleteConfirmation.value = false;
            selectedOffice.value = null;
        },
    });
}
</script>

<template>
    <Head title="Office Management" />

    <AuthenticatedLayout>
        <div class="flex justify-between min-h-12">
            <Breadcrumbs :items="data.breadcrumbs" />

            <Link :href="route('office.upsertForm')">
                <Button><Plus />Add Office</Button>
            </Link>
        </div>

        <PageHeader>
            <template #icon><Hotel /></template>
            <template #title>Office Management</template>
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

            <SelectField
                v-model="form.sort_by"
                :items="data.sortBy"
                placeholder="Sort By"
                label="Sort By"
            />

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
                    <template v-if="offices && offices.data.length > 0">
                        <TableRow
                            v-for="office in offices.data"
                            :key="office.id"
                        >
                            <TableCell>
                                {{ office.region.name }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ office.name }}
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
                                                    route('office.upsertForm', {
                                                        id: office.id,
                                                    })
                                                "
                                            >
                                                <Pencil />Edit
                                            </PopoverLinkField>

                                            <PopoverField
                                                @click="
                                                    showDeleteConfirmation(
                                                        office
                                                    )
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
                        :href="offices.first_page_url"
                        :disabled="offices.current_page === 1"
                    />
                    <PaginatorButton
                        variant="prev"
                        :href="offices.prev_page_url ?? ''"
                        :disabled="offices.current_page === 1"
                    />
                    <PaginatorInfo
                        :current_page="offices.current_page"
                        :from="offices.from"
                        :to="offices.to"
                        :total="offices.total"
                    />
                    <PaginatorButton
                        variant="next"
                        :href="offices.next_page_url ?? ''"
                        :disabled="offices.current_page === offices.last_page"
                    />
                    <PaginatorButton
                        variant="end"
                        :href="offices.last_page_url"
                        :disabled="offices.current_page === offices.last_page"
                    />
                </Paginator>
            </TableFooter>
        </TableContainer>

        <Alert
            :open="deleteConfirmation"
            @update:open="deleteConfirmation = $event"
            :onConfirm="handleDeleteOffice"
            title="Are you sure you want to delete this office?"
            description="This action cannot be undone and will remove all office data."
            severity="danger"
            confirm-label="Delete"
        />
    </AuthenticatedLayout>
</template>
