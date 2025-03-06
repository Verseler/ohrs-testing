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
import { computed, ref, watch } from "vue";
import { Bed, Room, RoomWithBed } from "@/Pages/RoomManagement/room.types";
import Separator from "@/Components/ui/separator/Separator.vue";
import { Button } from "@/Components/ui/button";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { usePage } from "@inertiajs/vue3";
import { SharedData } from "@/types";
import { toast } from 'vue-sonner';

type InsertRoomProps = {
    room: RoomWithBed | null;
};

const { room } = defineProps<InsertRoomProps>();

const page = usePage<SharedData>();
const counter = ref(room?.beds.length ?? 1);

type UpsertRoomForm = Partial<
    Room & {
        beds: Pick<Bed, "id" | "status" | "price" | "name">[];
    }
>;

const form: InertiaForm<UpsertRoomForm> = useForm({
    id: room?.id,
    name: room?.name,
    eligible_gender: room?.eligible_gender,
    beds: room?.beds,
    status: room?.status,
    bed_price_rate: room?.bed_price_rate
});

const bedsLength = computed(() => {
    if (!form.beds) return 0;

    return form.beds.length;
});

function addMoreBed() {
    if (!form.beds || !room) return;

    counter.value++;

    form.beds.push({
        id: Date.now(),
        name: `Bed #${counter.value}`,
        price: room.bed_price_rate,
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
    if (!room || !room.id) return;

    form.put(route("room.edit", { room: room }));
}
</script>

<template>
    <Head title="Edit Room" />
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
                        <BreadcrumbPage>Edit Room</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink />
        </div>

        <PageHeader>
            <template #icon><BedIcon /></template>
            <template #title>Edit Room</template>
        </PageHeader>

        <form
            @submit.prevent="showSubmitConfirmation"
            class="max-w-2xl space-y-4"
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

            <!-- Room Status Field -->
            <div class="flex flex-col gap-2">
                <Label for="name">Status</Label>
                <RadioGroup v-model="form.status" default-value="option-one">
                    <div class="flex w-full gap-4">
                        <RadioButtonCard
                            id="available"
                            value="available"
                            label="Available"
                            :active="form.status === 'available'"
                            class="flex-1"
                        />
                        <RadioButtonCard
                            id="fully_occupied"
                            value="fully_occupied"
                            label="Fully Occupied"
                            :active="form.status === 'fully_occupied'"
                            class="flex-1"
                        />
                        <RadioButtonCard
                            id="maintenance"
                            value="maintenance"
                            label="Maintenance"
                            :active="form.status === 'maintenance'"
                            class="flex-1"
                        />
                    </div>
                </RadioGroup>
                <InputError v-if="form.errors.status">
                    {{ form.errors.status }}
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
            <div class="space-y-4">
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
                    </div>
                </div>

                <Separator class="my-2" />

                <!-- List of bed fields -->
                <div
                    id="name"
                    v-for="(bed, index) in form.beds"
                    class="grid flex-1 grid-cols-3 gap-x-2"
                >
                    <div class="col-span-2">
                        <Input
                        v-model="bed.name"
                        maxlength="8"
                        :invalid="!!(form.errors as any)[`beds.${index}.name`]"
                        />
                        <InputError
                        v-if="(form.errors as any)[`beds.${index}.name`]"
                        >
                        {{ "Field is required." }}
                    </InputError>
                    </div>

                    <div class="flex gap-x-2">
                        <div class="flex-1">
                            <Select v-model="bed.status">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Status</SelectLabel>
                                        <SelectItem value="available">
                                            Available
                                        </SelectItem>
                                        <SelectItem value="reserved">
                                            Reserved
                                        </SelectItem>
                                        <SelectItem value="occupied">
                                            Occupied
                                        </SelectItem>
                                        <SelectItem value="maintenance">
                                            Maintenance
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError
                                v-if="(form.errors as any)[`beds.${index}.code`]"
                            >
                                {{ "Field is required." }}
                            </InputError>
                        </div>

                        <Button
                            v-if="bedsLength > 1"
                            @click="removeBed(bed.id)"
                            size="icon"
                            variant="ghost"
                            type="button"
                            class="text-red-500 hover:bg-red-50 hover:text-red-500"
                        >
                            <Trash />
                        </Button>
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
