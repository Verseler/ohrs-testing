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
import type { Office } from "@/Pages/Admin/Office/office.types";
import { Home, ShieldUser } from "lucide-vue-next";
import { computed, ref } from "vue";
import { Separator } from "@/Components/ui/separator";
import InputPassword from "@/Components/ui/input/InputPassword.vue";
import { Button } from "@/Components/ui/button";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { SidebarTrigger } from "@/Components/ui/sidebar";
import SearchableSelect from "@/Components/SearchableSelect.vue";

type CreateUserProps = {
    offices: Office[];
};

const { offices } = defineProps<CreateUserProps>();

const officeOptions = computed(() => {
    if(!offices || offices.length <= 0) return [];

    return offices.map((office) => ({
        value: office.id,
        label: office.name,
    }));

});

const form = useForm({
    name: "",
    office_id: undefined,
    role: undefined,
    password: "",
    confirm_password: "",
});

//Confirmation Dialog
const confirmation = ref(false);

function showConfirmation() {
    confirmation.value = true;
}

function submit() {
    form.post(route("user.create"));
}
</script>

<template>
    <Head title="Create User" />

    <AuthenticatedLayout>
        <div class="flex justify-between min-h-12">
            <Breadcrumb>
                <BreadcrumbList>
                    <BreadcrumbItem>
                        <SidebarTrigger class="me-2" />
                    </BreadcrumbItem>

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
                        <BreadcrumbPage>Create Room</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink :href="route('user.list')" />
        </div>

        <PageHeader>
            <template #icon><ShieldUser /></template>
            <template #title>Create User</template>
        </PageHeader>

        <form @submit.prevent="showConfirmation" class="max-w-lg space-y-6">
            <!-- Name Field -->
            <div class="flex flex-col gap-2">
                <Label for="name">Username</Label>
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

            <!-- Office Field -->
            <div>
                <Label for="office">Office</Label>
                <SearchableSelect
                    v-model="form.office_id"
                    :options="officeOptions"
                    option-label="name"
                    option-value="id"
                    placeholder="Select office"
                />
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
                        <SelectValue placeholder="Select role" />
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

            <!-- Password Field -->
            <div class="flex flex-col gap-2">
                <Label for="password">Password</Label>
                <InputPassword
                    id="password"
                    v-model="form.password"
                    :invalid="!!form.errors.password"
                    autofocus
                />

                <InputError>
                    {{ form.errors.password }}
                </InputError>
            </div>

            <!-- Confirmation Password Field -->
            <div class="flex flex-col gap-2">
                <Label for="confirm-password">Confirm Password</Label>
                <InputPassword
                    id="confirm-password"
                    v-model="form.confirm_password"
                    :invalid="!!form.errors.confirm_password"
                    autofocus
                />

                <InputError>
                    {{ form.errors.confirm_password }}
                </InputError>
            </div>

            <Button type="submit" size="lg" class="w-full">Save</Button>
        </form>

        <Alert
            :open="confirmation"
            @update:open="confirmation = $event"
            :onConfirm="submit"
            title="Are you sure you want to create this user?"
            description="Please confirm to proceed with creating the user."
            confirm-label="Confirm"
        />
    </AuthenticatedLayout>
</template>
