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
import ValueAdjuster from "@/Components/ValueAdjuster.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, InertiaForm } from "@inertiajs/vue3";
import { Bed as BedIcon, Home } from "lucide-vue-next";
import { Room } from "@/Pages/RoomManagement/room.types";
import { Button } from "@/Components/ui/button";
import { usePage } from "@inertiajs/vue3";
import { SharedData } from "@/types";
import { toast } from "vue-sonner";
import { watch } from "vue";

const page = usePage<SharedData>();

type CreateRoomForm = Omit<Room, "id"> & {
    number_of_beds: number
};

const form: InertiaForm<CreateRoomForm> = useForm({
    name: "",
    eligible_gender: "any",
    status: "available",
    bed_price_rate: 200,
    number_of_beds: 1
});


function increaseBed() {
    form.number_of_beds++;
}

function decreaseBed() {
    if(form.number_of_beds <= 1) return;

    form.number_of_beds--;
}

// Display flash success or error message as sonner or toast
watch(
    () => page.props.flash.error,
    () => {
        if (page.props.flash.error) {
            toast.error(page.props.flash.error, {
                style: {
                    background: "#ef4444",
                    color: "white",
                },
                position: "top-center",
            });

            setTimeout(() => {
                page.props.flash.error = null;
            }, 300);
        }
    }
);

function showSubmitConfirmation() {
    form.post(route("room.create"));
}
</script>

<template>
    <Head title="Create Room" />

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
                            Room Management
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>Create Room</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink />
        </div>

        <PageHeader>
            <template #icon><BedIcon /></template>
            <template #title>Create Room</template>
        </PageHeader>

        <form
            @submit.prevent="showSubmitConfirmation"
            class="max-w-xl space-y-4"
        >
            <!-- Name Field -->
            <div class="flex flex-col gap-2">
                <Label for="name">Room Name</Label>
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

            <!-- Eligible Gender Field -->
            <div class="flex flex-col gap-2">
                <Label for="name">Eligible Gender</Label>
                <RadioGroup
                    v-model="form.eligible_gender"
                    default-value="option-one"
                >
                    <div class="flex w-full gap-4">
                        <RadioButtonCard
                            id="any"
                            value="any"
                            label="Any"
                            :active="form.eligible_gender === 'any'"
                            class="flex-1"
                        />
                        <RadioButtonCard
                            id="male"
                            value="male"
                            label="Male"
                            :active="form.eligible_gender === 'male'"
                            class="flex-1"
                        />
                        <RadioButtonCard
                            id="female"
                            value="female"
                            label="Female"
                            :active="form.eligible_gender === 'female'"
                            class="flex-1"
                        />
                    </div>
                </RadioGroup>
                <InputError v-if="form.errors.eligible_gender">
                    {{ form.errors.eligible_gender }}
                </InputError>
            </div>

            <!-- Bed Field -->
            <div class="flex flex-col gap-2">
                <div class="grid grid-cols-3 gap-x-4">
                    <!-- Increment and decrement number of bed(s) -->
                    <div class="col-span-2 space-y-2">
                        <Label>Number of Bed(s)</Label>
                        <ValueAdjuster
                            :value="form.number_of_beds"
                            :on-decrease="decreaseBed"
                            :disable-decrease="form.number_of_beds <= 1"
                            :on-increase="increaseBed"
                        />
                    </div>

                    <!-- Bed price rate field -->
                    <div class="space-y-2">
                        <Label for="bed-price-rate">Bed Price Rate</Label>
                        <Input
                            id="bed-price-rate"
                            class="w-full"
                            type="number"
                            step=".01"
                            v-model="form.bed_price_rate"
                        />
                        <InputError v-if="form.errors.bed_price_rate">
                            {{ form.errors.bed_price_rate }}
                        </InputError>
                    </div>
                </div>
            </div>

            <div>
                <Button
                    type="submit"
                    size="lg"
                    class="w-full mt-4 text-md"
                    :disabled="form.processing"
                >
                    Save
                </Button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
