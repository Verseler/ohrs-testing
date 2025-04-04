<script setup lang="ts">
import LinkButton from "@/Components/LinkButton.vue";
import PageHeader from "@/Components/PageHeader.vue";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { Button } from "@/Components/ui/button";
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
import PopoverLinkField from "@/Components/ui/popover/PopoverLinkField.vue";
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
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import type { LaravelPagination, User } from "@/types";
import { Head, router, useForm } from "@inertiajs/vue3";
import {
    Ellipsis,
    Lock,
    Pencil,
    Plus,
    ShieldUser,
    Trash,
} from "lucide-vue-next";
import { computed, onMounted, ref, watch } from "vue";
import type { UserFilters } from "@/Pages/Admin/User/user.types";
import type { Region } from "@/Pages/Admin/Office/office.types";
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
import TableOrderToggle from "@/Components/ui/table/TableOrderToggle.vue";
import Searchbox from "@/Components/Searchbox.vue";
import RoleBadge from "@/Pages/Admin/User/Partials/RoleBadge.vue";
import { usePoll } from "@inertiajs/vue3";
import { showSuccess } from "@/Composables/useFlash";
import { data } from "@/Pages/Admin/User/data";
import SelectField from "@/Components/SelectField.vue";
import TableContainer from "@/Components/ui/table/TableContainer.vue";
import TableRowHeader from "@/Components/ui/table/TableRowHeader.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import ClearFilterButton from "@/Components/ui/table/ClearFilterButton.vue";

usePoll(25000);

type UserManagementProps = {
    users: LaravelPagination<User>;
    filters: UserFilters;
    regions: Region[];
};

const { users, filters } = defineProps<UserManagementProps>();

const form = useForm<Partial<UserFilters>>({
    region_id: filters?.region_id,
    role: filters?.role,
    search: filters?.search,
    sort_by: filters?.sort_by,
    sort_order: filters?.sort_order ?? "asc",
});

const formHasValue = computed(
    () => form.region_id || form.role || form.search || form.sort_by
);

//User Filter
function clearFilter() {
    form.region_id = undefined;
    form.role = undefined;
    form.search = undefined;
    form.sort_by = undefined;
    form.sort_order = "asc";
}

function applyFilter() {
    form.get(route("user.list"), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

watch(
    [
        () => form.region_id,
        () => form.role,
        () => form.search,
        () => form.sort_by,
        () => form.sort_order,
    ],
    debounce(applyFilter, 300)
);

onMounted(() => showSuccess());

//delete confirmation
const selectedUser = ref<User | null>(null);

const deleteConfirmation = ref(false);

function showDeleteConfirmation(user: User) {
    deleteConfirmation.value = true;

    selectedUser.value = user;
}

function deleteUser() {
    if (!selectedUser.value) return;

    router.visit(route("user.delete", { id: selectedUser.value.id }), {
        method: "delete",
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            deleteConfirmation.value = false;
            selectedUser.value = null;
        },
    });
}
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <div class="flex justify-between min-h-12">
            <Breadcrumbs :items="data.breadcrumbs" />

            <LinkButton :href="route('user.createForm')">
                <Plus />Add User
            </LinkButton>
        </div>

        <PageHeader>
            <template #icon><ShieldUser /></template>
            <template #title>User Management</template>
        </PageHeader>

        <!-- Search, Filter and Sort -->
        <div
            class="flex flex-col-reverse justify-between gap-2 mb-2 md:flex-row"
        >
            <div class="flex flex-col gap-2 md:flex-row">
                <Select v-model="form.region_id">
                    <SelectTrigger class="md:w-40">
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
                    v-model="form.role"
                    placeholder="Select a role"
                    label="Role"
                    :items="data.filterRole"
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
                    <template v-if="users && users.data.length > 0">
                        <TableRow v-for="user in users.data" :key="user.id">
                            <TableCell class="font-medium">
                                {{ user.name }}
                            </TableCell>
                            <TableCell class="font-medium">
                                Region {{ user.office?.region?.name }} -
                                {{ user.office?.name }}
                            </TableCell>
                            <TableCell class="font-medium">
                                <RoleBadge :role="user.role" />
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
                                    <PopoverContent
                                        align="end"
                                        class="p-0 max-w-44"
                                    >
                                        <div class="flex flex-col">
                                            <PopoverLinkField
                                                :href="
                                                    route('user.editForm', {
                                                        id: user.id,
                                                    })
                                                "
                                            >
                                                <Pencil />Edit Details
                                            </PopoverLinkField>
                                            <PopoverLinkField
                                                :href="
                                                    route(
                                                        'user.changePassForm',
                                                        {
                                                            id: user.id,
                                                        }
                                                    )
                                                "
                                            >
                                                <Lock />Change Password
                                            </PopoverLinkField>
                                            <PopoverField
                                                @click="
                                                    showDeleteConfirmation(user)
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
                        :href="users.first_page_url"
                        :disabled="users.current_page === 1"
                    />
                    <PaginatorButton
                        variant="prev"
                        :href="users.prev_page_url ?? ''"
                        :disabled="users.current_page === 1"
                    />
                    <PaginatorInfo
                        :current_page="users.current_page"
                        :from="users.from"
                        :to="users.to"
                        :total="users.total"
                    />
                    <PaginatorButton
                        variant="next"
                        :href="users.next_page_url ?? ''"
                        :disabled="users.current_page === users.last_page"
                    />
                    <PaginatorButton
                        variant="end"
                        :href="users.last_page_url"
                        :disabled="users.current_page === users.last_page"
                    />
                </Paginator>
            </TableFooter>
        </TableContainer>

        <Alert
            :open="deleteConfirmation"
            @update:open="deleteConfirmation = $event"
            :onConfirm="deleteUser"
            title="Are you sure you want to delete this user?"
            description="This action cannot be undone and will remove all user data."
            severity="danger"
            confirm-label="Delete"
        />
    </AuthenticatedLayout>
</template>
