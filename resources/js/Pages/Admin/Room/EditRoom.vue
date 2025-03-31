<script setup lang="ts">
import BackLink from "@/Components/BackLink.vue";
import PageHeader from "@/Components/PageHeader.vue";
import RadioButtonCard from "@/Components/RadioButtonCard.vue";
import { Input, InputError } from "@/Components/ui/input";
import Label from "@/Components/ui/label/Label.vue";
import { RadioGroup } from "@/Components/ui/radio-group";
import ValueAdjuster from "@/Components/ValueAdjuster.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, InertiaForm } from "@inertiajs/vue3";
import { Bed as BedIcon, Trash } from "lucide-vue-next";
import { computed, ref } from "vue";
import { Bed, Room, RoomWithBed } from "@/Pages/Admin/Room/room.types";
import Separator from "@/Components/ui/separator/Separator.vue";
import { Button } from "@/Components/ui/button";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import { editRoomData } from "./data";

type InsertRoomProps = {
    room: RoomWithBed | null;
};

const { room } = defineProps<InsertRoomProps>();

const counter = ref(room?.beds.length ?? 1);

type UpsertRoomForm = Partial<
    Room & {
        beds: Pick<Bed, "id" | "price" | "name">[];
    }
>;

const form: InertiaForm<UpsertRoomForm> = useForm({
    id: room?.id,
    name: room?.name,
    eligible_gender: room?.eligible_gender,
    beds: room?.beds,
});

const bedsLength = computed(() => {
    if (!form.beds) return 0;

    return form.beds.length;
});

function increaseBed() {
    if (!form.beds || !room) return;

    counter.value++;

    form.beds.push({
        id: Date.now(),
        name: `Bed #${counter.value}`,
        price: form.beds[0].price,
    });
}

function decreaseBed(id: number | string) {
    if (!form.beds) return;

    form.beds = form.beds.filter((bed) => bed.id !== id);
}

function removeLastBed() {
    if (form.beds && form.beds.length > 1) {
        form.beds.pop();
    }
}

function showSubmitConfirmation() {
    if (!room || !room.id) return;

    form.put(route("room.edit", { room: room }));
}
</script>

<template>
    <Head title="Edit Room" />
    <AuthenticatedLayout>
        <div class="flex justify-between min-h-12">
            <Breadcrumbs :items="editRoomData.breadcrumbs" />
            <BackLink />
        </div>

        <PageHeader>
            <template #icon><BedIcon /></template>
            <template #title>Edit Room</template>
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
                            v-for="item in editRoomData.eligibleGenderRadioButtons"
                            :value="item"
                            :label="item"
                            :active="form.eligible_gender === item"
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
                <!-- Increment and decrement number of bed(s) -->
                <div class="space-y-2">
                    <Label>Number of Bed(s)</Label>
                    <ValueAdjuster
                        :value="bedsLength"
                        :on-decrease="removeLastBed"
                        :on-increase="increaseBed"
                    />
                </div>

                <Separator class="my-2" />
                <!-- List of bed fields -->
                <div
                    v-for="(bed, index) in form.beds"
                    class="flex flex-1 gap-x-2"
                >
                    <div class="flex-1">
                        <Input
                            v-model="bed.name"
                            maxlength="8"
                            :invalid="!!(form.errors as any)[`beds.${index}.name`]"
                        />
                        <InputError
                            v-if="(form.errors as any)[`beds.${index}.name`]"
                        >
                            {{ (form.errors as any)[`beds.${index}.name`] }}
                        </InputError>
                    </div>

                    <div>
                        <Input
                            class="flex-1"
                            v-model.number="bed.price"
                            type="number"
                            step=".01"
                            :invalid="!!(form.errors as any)[`beds.${index}.price`]"
                        />
                        <InputError
                            v-if="(form.errors as any)[`beds.${index}.price`]"
                        >
                            {{ (form.errors as any)[`beds.${index}.price`] }}
                        </InputError>
                    </div>

                    <Button
                        v-if="bedsLength > 1"
                        @click="decreaseBed(bed.id)"
                        size="icon"
                        variant="ghost"
                        type="button"
                        class="text-red-500 hover:bg-red-50 hover:text-red-500"
                    >
                        <Trash />
                    </Button>
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
