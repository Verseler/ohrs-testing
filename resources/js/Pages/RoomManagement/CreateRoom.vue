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
import { Bed as BedIcon, Home, Trash } from "lucide-vue-next";
import { computed, ref } from "vue";
import { Bed, Room } from "@/Pages/RoomManagement/room.types";
import Separator from "@/Components/ui/separator/Separator.vue";
import { Button } from "@/components/ui/button";

const counter = ref(1);
const defaultPrice = ref(200.0);

const DEFAULT_BEDS: Omit<Bed, "room_id">[] = [
    {
        id: Date.now(),
        name: `Bed ${counter.value}`,
        price: defaultPrice.value,
        status: "available",
    },
];

type UpsertRoomForm = Omit<Room, "id"> & { beds: Omit<Bed, "room_id">[] };

const form: InertiaForm<UpsertRoomForm> = useForm({
    name: "",
    eligible_gender: "any",
    status: "available",
    beds: DEFAULT_BEDS,
});

const bedsLength = computed(() => {
    if (!form.beds) return 0;

    return form.beds.length;
});

function addMoreBed() {
    if (!form.beds) return;

    counter.value++;

    form.beds.push({
        id: Date.now(), //* temporary ID only, it will be replaced in the backend
        name: `Bed ${counter.value}`,
        price: defaultPrice.value,
        status: "available",
    });
}

function removeBed(id: number | string) {
    if (!form.beds) return;

    form.beds = form.beds.filter((bed) => bed.id !== id);
}

function removeLastBed() {
    if (form.beds && form.beds.length > 1) {
        form.beds.pop();
    }
}

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
                            :value="bedsLength"
                            :on-decrease="removeLastBed"
                            :on-increase="addMoreBed"
                        />
                    </div>

                    <!-- Default price field -->
                    <div class="space-y-2">
                        <Label for="default-price">Default Price</Label>
                        <Input
                            id="default-price"
                            class="w-full"
                            type="number"
                            step=".01"
                            v-model="defaultPrice"
                        />
                    </div>
                </div>

                <Separator class="my-2" />

                <!-- List of bed fields -->
                <div
                    id="name"
                    v-for="(bed, index) in form.beds"
                    class="grid grid-cols-3 gap-x-4"
                >
                    <div class="flex-1 col-span-2">
                        <Input
                            v-model="bed.name"
                            class="w-full"
                            maxlength="8"
                            :invalid="!!(form.errors as any)[`beds.${index}.code`]"
                        />

                        <InputError
                            v-if="(form.errors as any)[`beds.${index}.code`]"
                        >
                            {{ "Field is required." }}
                        </InputError>
                    </div>
                    <div>
                        <div class="flex items-center gap-x-2">
                            <Input
                                class="flex-1"
                                v-model="bed.price"
                                id="bed-rice"
                                type="number"
                                step=".01"
                                :invalid="!!(form.errors as any)[`beds.${index}.price`]"
                            />
                            <Button
                                v-if="bedsLength > 1"
                                @click="removeBed(bed.id)"
                                size="icon"
                                variant="ghost"
                                class="text-red-500 hover:bg-red-50 hover:text-red-500"
                            >
                                <Trash />
                            </Button>
                        </div>
                        <InputError v-if="(form.errors as any)[`beds.${index}.price`]">
                            {{ "Price is required." }}
                        </InputError>
                    </div>
                </div>

                <InputError v-if="form.errors.beds">
                    {{ form.errors.beds }}
                </InputError>
            </div>

            <div>
                <Button
                    type="submit"
                    class="w-full mt-4 text-md"
                    size="lg"
                    :disabled="form.processing"
                >
                    Save
                </Button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
