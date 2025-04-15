<script setup lang="ts">
import BackLink from "@/Components/BackLink.vue";
import PageHeader from "@/Components/PageHeader.vue";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import { Button } from "@/Components/ui/button";
import { InputError } from "@/Components/ui/input";
import InputPassword from "@/Components/ui/input/InputPassword.vue";
import { Label } from "@/Components/ui/label";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import type { User } from "@/types";
import { Head, useForm } from "@inertiajs/vue3";
import { Home, ShieldUser } from "lucide-vue-next";
import RoleBadge from "@/Pages/Admin/User/Partials/RoleBadge.vue";
import { ref } from "vue";
import { SidebarTrigger } from "@/Components/ui/sidebar";

type ChangePasswordUserProps = {
    user: User;
};

const { user } = defineProps<ChangePasswordUserProps>();

const form = useForm({
    id: user?.id,
    new_password: "",
    confirm_password: "",
});

//confirmation dialog
const confirmation = ref(false);

function showConfirmation() {
    confirmation.value = true;
}

function submit() {
    form.put(route("user.changePass"));
}
</script>

<template>
    <Head title="Change Password" />

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
                        <BreadcrumbPage>Change Password</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink :href="route('user.list')" />
        </div>

        <PageHeader>
            <template #icon><ShieldUser /></template>
            <template #title>Change Password</template>
        </PageHeader>

        <form @submit.prevent="showConfirmation" class="max-w-lg space-y-4">
            <div class="mb-8 space-y-0.5">
                <h2 class="text-2xl font-bold">{{ user.name }}</h2>
                <RoleBadge class="ms-0.5" :role="user.role" />
            </div>

            <div class="flex flex-col gap-2">
                <Label for="new_password">New Password</Label>
                <InputPassword
                    id="new_password"
                    v-model="form.new_password"
                    :invalid="!!form.errors.new_password"
                    placeholder="Enter your new password"
                    autofocus
                />

                <InputError>
                    {{ form.errors.new_password }}
                </InputError>
            </div>
            <div class="flex flex-col gap-2 mt-2">
                <Label for="new_password">Confirm Password</Label>
                <InputPassword
                    id="new_password"
                    v-model="form.confirm_password"
                    :invalid="!!form.errors.confirm_password"
                    placeholder="Confirm password"
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
            title="Are you sure you want to change the password?"
            description="Please confirm to proceed with changing the password."
        />
    </AuthenticatedLayout>
</template>
