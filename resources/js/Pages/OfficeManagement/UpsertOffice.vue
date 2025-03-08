<script setup lang="ts">
import BackLink from "@/Components/BackLink.vue";
import PageHeader from "@/Components/PageHeader.vue";
import RadioButtonCard from "@/Components/RadioButtonCard.vue";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import { Input, InputError } from "@/Components/ui/input";
import Label from "@/Components/ui/label/Label.vue";
import { RadioGroup } from "@/Components/ui/radio-group";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, InertiaForm } from "@inertiajs/vue3";
import {  Home, Hotel } from "lucide-vue-next";
import { Button } from "@/Components/ui/button";
import { Office } from "@/Pages/OfficeManagement/office.types";

const { office } = defineProps<{ office: Office | null }>();


type UpsertOfficeForm = Pick<Office, "name" | "has_hostel">;

const form: InertiaForm<UpsertOfficeForm> = useForm({
    id: office?.id ?? null,
    name: office?.name ?? '',
    has_hostel: office?.has_hostel ?? false
});

function showSubmitConfirmation() {
    form.post(route("office.upsert"));
}
</script>

<template>
    <Head :title="office ? 'Edit Office' : 'Create Office'" />

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
                        <BreadcrumbLink :href="route('office.list')">
                            Office Management
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>{{ office ? 'Edit' : 'Create' }} Office</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink />
        </div>

        <PageHeader>
            <template #icon><Hotel /></template>
            <template #title>{{ office ? 'Edit' : 'Create' }} Office</template>
        </PageHeader>

        <form
            @submit.prevent="showSubmitConfirmation"
            class="space-y-4 max-w-xl"
        >
            <!-- Name Field -->
            <div class="flex flex-col gap-2">
                <Label for="name">Office Name</Label>
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

            <div class="flex flex-col gap-2">
                <Label for="name">Has Hostel</Label>
                <RadioGroup
                    :model-value="form.has_hostel ? 'yes' : 'no'"
                    v-on:update:model-value="(value) => form.has_hostel = value === 'yes'"
                >
                    <div class="flex gap-4 w-full">
                        <RadioButtonCard
                            id="yes"
                            value="yes"
                            label="Yes"
                            :active="form.has_hostel"
                            class="flex-1"
                        />
                        <RadioButtonCard
                            id="no"
                            value="no"
                            label="No"
                            :active="form.has_hostel === false"
                            class="flex-1"
                        />
                    </div>
                </RadioGroup>
                <InputError v-if="form.errors.has_hostel">
                    {{ form.errors.has_hostel }}
                </InputError>
            </div>

            <div>
                <Button
                    type="submit"
                    size="lg"
                    class="mt-4 w-full text-md"
                    :disabled="form.processing"
                >
                    Save
                </Button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
