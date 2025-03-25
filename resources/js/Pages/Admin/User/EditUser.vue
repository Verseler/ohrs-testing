<script setup lang="ts">
import BackLink from "@/Components/BackLink.vue";
import PageHeader from "@/Components/PageHeader.vue";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import { Input, InputError } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import type { Office, Region } from "@/Pages/Admin/Office/office.types";
import { Home, ShieldUser } from "lucide-vue-next";
import { computed, ref, watch } from "vue";
import { Separator } from "@/Components/ui/separator";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { Button } from "@/Components/ui/button";
import { User } from "@/types";

type EditUserProps = {
    user: User;
    regions: Region[];
    offices: Office[];
};

const { user, offices, regions } = defineProps<EditUserProps>();

const form = useForm<Partial<User>>({
    id: user?.id,
    name: user?.name,
    office_id: user?.office_id,
    role: user?.role,
});

const selectedRegionId = ref<Region["id"]>(user?.office.region_id);

const officesInARegion = computed(() =>
    offices.filter((office) => office.region_id === selectedRegionId.value)
);

//clear selected office if region is changed
watch(selectedRegionId, () => {
    form.office_id = undefined;
});

//Confirmation Dialog
const confirmation = ref(false);

function showConfirmation() {
    confirmation.value = true;
}

function submit() {
    form.put(route("user.edit"));
}
</script>

<template>
    <Head title="Create User" />

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
                        <BreadcrumbLink :href="route('room.list')">
                            User Management
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>Edit Room</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink :href="route('user.list')" />
        </div>

        <PageHeader>
            <template #icon><ShieldUser /></template>
            <template #title>Edit User</template>
        </PageHeader>

        <form @submit.prevent="showConfirmation" class="max-w-lg space-y-6">
            <!-- Name Field -->
            <div class="flex flex-col gap-2">
                <Label for="name">Display Name</Label>
                <Input
                    id="name"
                    v-model="form.name"
                    :invalid="!!form.errors.name"
                    autofocus
                />

                <InputError v-if="form.errors.name">
                    {{ form.errors.name }}
                </InputError>
            </div>

            <!-- Region Field -->
            <div class="flex flex-col gap-2">
                <Label for="region">Region</Label>
                <Select id="region" v-model="selectedRegionId">
                    <SelectTrigger :invalid="!!form.errors.office_id">
                        <SelectValue placeholder="Select region" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectItem
                                v-for="region in regions"
                                :Key="region.id"
                                :value="region.id"
                            >
                                {{ region.name }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
                <InputError v-if="form.errors.office_id">
                    {{ form.errors.office_id }}
                </InputError>
            </div>

            <!-- Office Field -->
            <div>
                <Label for="office">Office</Label>
                <Select
                    id="office"
                    v-model="form.office_id"
                    :disabled="Boolean(selectedRegionId) === false"
                >
                    <SelectTrigger :invalid="!!form.errors.office_id">
                        <SelectValue placeholder="Select office" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectItem
                                v-for="office in officesInARegion"
                                :Key="office.id"
                                :value="office.id"
                            >
                                {{ office.name }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
                <InputError v-if="form.errors.office_id">
                    {{ form.errors.office_id }}
                </InputError>
            </div>

            <Separator />

            <!-- Role Field -->
            <div>
                <Label for="role">Role</Label>
                <Select id="role" v-model="form.role">
                    <SelectTrigger :invalid="!!form.errors.role">
                        <SelectValue placeholder="Select office" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectItem value="admin"> Admin </SelectItem>
                            <SelectItem value="super_admin">
                                Super Admin
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
                <InputError v-if="form.errors.role">
                    {{ form.errors.role }}
                </InputError>
            </div>

            <Button type="submit" size="lg" class="w-full">Save</Button>
        </form>

        <Alert
            :open="confirmation"
            @update:open="confirmation = $event"
            :onConfirm="submit"
            title="Are you sure you want to save the changes?"
            description="Please confirm to proceed with saving the edits."
            confirm-label="Confirm"
        />
    </AuthenticatedLayout>
</template>
