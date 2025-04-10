<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import { Input, InputDate, InputError } from "@/Components/ui/input";
import { Plus, Trash } from "lucide-vue-next";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { formatDate, removeItem } from "@/lib/utils";
import { TableCell, TableRow } from "@/Components/ui/table";
import { AutoComplete } from "@/Components/ui/auto-complete";
import InputLabel from "@/Components/ui/input/InputLabel.vue";
import { computed } from "vue";
import AutoFillButton from "@/Pages/Guest/ReservationForm/Partials/AutoFillButton.vue";

const { form } = defineProps({
    form: {
        type: Object,
        required: true,
    },
    offices: {
        type: Array,
        required: true,
    },
});

const firstGuestHasFilled = computed<Boolean>(() => {
    return Boolean(form.guests[0].first_name || form.guests[0].last_name);
});

function addEmployeeAsFirstGuest() {
    form.guests[0] = {
        first_name: form.first_name,
        last_name: form.last_name,
        gender: undefined,
        office: undefined,
        check_in_date: undefined as string | undefined,
        check_out_date: undefined as string | undefined,
    };
}

function clearErrors() {
    form.errors = {};
}

function removeGuest(index: number) {
    form.guests = removeItem(form.guests, index);

    clearErrors();
}

function addGuest() {
    form.guests.push({
        first_name: undefined,
        last_name: undefined,
        gender: undefined,
        office: undefined,
        check_in_date: undefined as Date | undefined,
        check_out_date: undefined as Date | undefined,
    });
}
</script>

<template>
    <TableRow class="relative grid gap-4 px-3 border-none">
        <AutoFillButton
            v-if="
                form.first_name &&
                form.last_name &&
                firstGuestHasFilled === false
            "
            :onClick="addEmployeeAsFirstGuest"
        />

        <div
            v-for="(guest, index) in form.guests"
            class="p-6 pt-3 border rounded-lg shadow-sm bg-neutral-50"
        >
            <!-- Heading -->
            <div class="flex items-center justify-between">
                <h3 class="px-2 text-lg font-bold">Guest {{ index + 1 }}</h3>
                <Button
                    @click="removeGuest(index)"
                    v-if="form.guests.length > 1"
                    size="icon"
                    variant="ghost"
                    type="button"
                    class="text-red-500 hover:bg-red-50 hover:text-red-500"
                >
                    <Trash />
                </Button>
            </div>

            <!-- Content -->
            <div class="grid md:grid-cols-3 gap-x-2">
                <TableCell class="space-y-2">
                    <InputLabel>First Name</InputLabel>
                    <Input
                        v-model="guest.first_name"
                        class="h-12 border border-green-800"
                        :invalid="!!form.errors[`guests.${index}.first_name`]"
                    />
                    <InputError
                        v-if="!!form.errors[`guests.${index}.first_name`]"
                    >
                        {{ form.errors[`guests.${index}.first_name`] }}
                    </InputError>
                </TableCell>
                <TableCell class="space-y-2">
                    <InputLabel>Last Name</InputLabel>
                    <Input
                        v-model="guest.last_name"
                        class="h-12 border border-green-800"
                        :invalid="!!form.errors[`guests.${index}.last_name`]"
                    />
                    <InputError
                        v-if="!!form.errors[`guests.${index}.last_name`]"
                    >
                        {{ form.errors[`guests.${index}.last_name`] }}
                    </InputError>
                </TableCell>
                <TableCell class="space-y-2">
                    <InputLabel>Gender</InputLabel>

                    <Select class="flex-1" v-model="guest.gender">
                        <SelectTrigger
                            class="h-12 rounded-md shadow-none border-primary-800"
                            :invalid="!!form.errors[`guests.${index}.gender`]"
                        >
                            <SelectValue placeholder="Select a gender" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="male"> Male </SelectItem>
                                <SelectItem value="female"> Female </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>

                    <InputError v-if="!!form.errors[`guests.${index}.gender`]">
                        {{ form.errors[`guests.${index}.gender`] }}
                    </InputError>
                </TableCell>

                <TableCell class="space-y-2">
                    <InputLabel>Check In</InputLabel>
                    <InputDate
                        v-model="guest.check_in_date"
                        :invalid="
                            !!form.errors[`guests.${index}.check_in_date`]
                        "
                        :min="formatDate(new Date())"
                        :max="guest.check_out_date"
                    />
                    <InputError
                        v-if="!!form.errors[`guests.${index}.check_in_date`]"
                    >
                        {{ form.errors[`guests.${index}.check_in_date`] }}
                    </InputError>
                </TableCell>
                <TableCell class="space-y-2">
                    <InputLabel>Check Out</InputLabel>
                    <InputDate
                        v-model="guest.check_out_date"
                        :invalid="
                            !!form.errors[`guests.${index}.check_out_date`]
                        "
                        :min="guest.check_in_date"
                        :disabled="!guest.check_in_date"
                    />
                    <InputError
                        v-if="!!form.errors[`guests.${index}.check_out_date`]"
                    >
                        {{ form.errors[`guests.${index}.check_out_date`] }}
                    </InputError>
                </TableCell>
                <TableCell class="space-y-2">
                    <InputLabel>Office</InputLabel>
                    <AutoComplete
                        v-model="guest.office"
                        :items="(offices as string[])"
                        :invalid="!!form.errors[`guests.${index}.office`]"
                    />
                    <InputError v-if="!!form.errors[`guests.${index}.office`]">
                        {{ form.errors[`guests.${index}.office`] }}
                    </InputError>
                </TableCell>
            </div>
        </div>

        <Button
            @click="addGuest"
            variant="outline"
            size="lg"
            type="button"
            class="w-full text-green-700 bg-transparent border-green-600 rounded shadow-none hover:bg-green-100"
        >
            <Plus /> Add
        </Button>
    </TableRow>
</template>
