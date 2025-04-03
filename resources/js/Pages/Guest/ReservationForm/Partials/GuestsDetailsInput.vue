<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import { Input, InputError } from "@/Components/ui/input";
import { Plus, Trash } from "lucide-vue-next";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { isObjectEmpty, removeItem } from "@/lib/utils";
import { TableCell, TableRow } from "@/Components/ui/table";

const { form } = defineProps({
    form: {
        type: Object,
        required: true,
    },
});

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
    });
}
</script>

<template>
    <TableRow class="grid border-none">
        <div v-for="(guest, index) in form.guests">
            <TableCell class="grid grid-cols-4 gap-x-1.5">
                <Input
                    v-model="guest.first_name"
                    class="h-12 border border-green-800"
                    :invalid="!!form.errors[`guests.${index}.first_name`]"
                    placeholder="First Name"
                />
                <Input
                    v-model="guest.last_name"
                    class="h-12 border border-green-800"
                    :invalid="!!form.errors[`guests.${index}.last_name`]"
                    placeholder="Last Name"
                />
                <Input
                    v-model.number="guest.office"
                    class="h-12 border border-green-800"
                    :invalid="!!form.errors[`guests.${index}.office`]"
                    placeholder="Office"
                />
                <div class="flex items-center gap-x-1.5">
                    <Select class="flex-1" v-model="guest.gender">
                        <SelectTrigger
                            class="h-12 rounded-md shadow-none border-primary-800"
                            :invalid="!!form.errors[`guests.${index}.gender`]"
                        >
                            <SelectValue placeholder="Gender" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="male"> Male </SelectItem>
                                <SelectItem value="female"> Female </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>

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
            </TableCell>

            <TableCell
                v-if="isObjectEmpty(form.errors) === false"
                class="grid grid-cols-4 p-0 pl-1 -mt-1.5 gap-x-1.5"
            >
                <InputError>
                    {{ form.errors[`guests.${index}.first_name`] }}
                </InputError>

                <InputError>
                    {{ form.errors[`guests.${index}.last_name`] }}
                </InputError>

                <InputError>
                    {{ form.errors[`guests.${index}.office`] }}
                </InputError>

                <InputError>
                    {{ form.errors[`guests.${index}.gender`] }}
                </InputError>
            </TableCell>
        </div>

        <div class="px-2 mt-2">
            <Button
                @click="addGuest"
                variant="outline"
                size="lg"
                type="button"
                class="w-full text-green-700 bg-transparent border-green-700 rounded shadow-none hover:bg-green-100"
            >
                <Plus /> Add
            </Button>
        </div>
    </TableRow>
</template>
