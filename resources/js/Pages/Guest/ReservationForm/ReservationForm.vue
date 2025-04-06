<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import Header from "@/Components/Header.vue";
import { Table, TableCell, TableRow, TableBody } from "@/Components/ui/table";
import TableSectionHeading from "@/Pages/Guest/ReservationForm/Partials/TableSectionHeading.vue";
import { Textarea } from "@/Components/ui/textarea";
import { Input, InputError } from "@/Components/ui/input";
import InputLabel from "@/Components/ui/input/InputLabel.vue";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import type { Office } from "@/Pages/Admin/Office/office.types";
import { computed, ref } from "vue";
import DatePicker from "@/Components/DatePicker.vue";
import GuestsDetailsInput from "@/Pages/Guest/ReservationForm/Partials/GuestsDetailsInput.vue";
import { Button } from "@/Components/ui/button";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import type { Gender } from "@/Pages/Guest/guest.types";
import { ArrowDown } from "lucide-vue-next";
import { validIds } from "@/Pages/Guest/ReservationForm/data";

type ReservationFormProps = {
    hostelOffice: Office;
};

const { hostelOffice } = defineProps<ReservationFormProps>();

const DEFAULT_FIRST_GUEST = {
    first_name: undefined as string | undefined,
    last_name: undefined as string | undefined,
    gender: undefined as Gender | undefined,
    office: undefined as string | undefined,
};

const form = useForm({
    //reservation details
    check_in_date: undefined,
    check_out_date: undefined,
    hostel_office_id: hostelOffice.id,
    guests: [DEFAULT_FIRST_GUEST],

    //contact info
    first_name: "",
    middle_initial: undefined,
    last_name: "",
    email: "",
    phone: undefined,
    id_type: undefined,
    employee_id: "",
    purpose_of_stay: "",
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
    };
}

//confirmation dialog
const confirmation = ref(false);

function showConfirmation() {
    confirmation.value = true;
}

function submit() {
    form.post(route("reservation.create"));
}
</script>

<template>
    <Head title="Reservation Form" />

    <div class="w-full min-h-screen">
        <Header />

        <div class="container px-2 py-4 mx-auto md:p-8">
            <form @submit.prevent="showConfirmation">
                <Table>
                    <TableRow class="border-none">
                        <TableCell class="text-2xl font-bold">
                            Region {{ hostelOffice.region.name }} -
                            {{ hostelOffice.name }} Reservation
                        </TableCell>
                    </TableRow>

                    <TableBody class="grid grid-cols-1 gap-4 md:grid-cols-12">
                        <div class="md:col-span-5">
                            <TableRow class="grid border-none md:grid-cols-2">
                                <TableCell class="space-y-2">
                                    <InputLabel>Check In</InputLabel>
                                    <DatePicker
                                        v-model="form.check_in_date"
                                        :invalid="!!form.errors.check_in_date"
                                        :min-value="new Date()"
                                        :max-value="form.check_out_date"
                                    />
                                    <InputError
                                        v-if="form.errors.check_in_date"
                                    >
                                        {{ form.errors.check_in_date }}
                                    </InputError>
                                </TableCell>
                                <TableCell class="space-y-2">
                                    <InputLabel>Check Out</InputLabel>
                                    <DatePicker
                                        v-model="form.check_out_date"
                                        :invalid="!!form.errors.check_out_date"
                                        :min-value="form.check_in_date"
                                    />
                                    <InputError
                                        v-if="form.errors.check_out_date"
                                    >
                                        {{ form.errors.check_out_date }}
                                    </InputError>
                                </TableCell>
                            </TableRow>

                            <TableSectionHeading>
                                Employee Making the Reservation
                            </TableSectionHeading>
                            <TableRow
                                class="grid grid-cols-1 border-none md:grid-cols-5"
                            >
                                <TableCell class="col-span-2 space-y-2">
                                    <InputLabel>First Name</InputLabel>
                                    <Input
                                        v-model="form.first_name"
                                        class="h-12 rounded-sm shadow-none border-primary-700"
                                        :invalid="!!form.errors.first_name"
                                    />
                                    <InputError v-if="form.errors.first_name">
                                        {{ form.errors.first_name }}
                                    </InputError>
                                </TableCell>

                                <TableCell class="col-span-1 space-y-2">
                                    <InputLabel optional>M.I.</InputLabel>
                                    <Input
                                        v-model="form.middle_initial"
                                        class="h-12 rounded-sm shadow-none border-primary-700"
                                        :invalid="!!form.errors.middle_initial"
                                        maxlength="1"
                                    />
                                    <InputError
                                        v-if="form.errors.middle_initial"
                                    >
                                        {{ form.errors.middle_initial }}
                                    </InputError>
                                </TableCell>

                                <TableCell class="col-span-2 space-y-2">
                                    <InputLabel>Last Name</InputLabel>
                                    <Input
                                        v-model="form.last_name"
                                        class="h-12 rounded-sm shadow-none border-primary-700"
                                        :invalid="!!form.errors.last_name"
                                    />
                                    <InputError v-if="form.errors.last_name">
                                        {{ form.errors.last_name }}
                                    </InputError>
                                </TableCell>
                            </TableRow>

                            <TableRow class="grid border-none md:grid-cols-2">
                                <TableCell class="space-y-2">
                                    <InputLabel>Phone #</InputLabel>
                                    <div class="relative">
                                        <span
                                            class="absolute top-[15.3px] text-neutral-700 left-2"
                                            >(+63)</span
                                        >
                                        <Input
                                            type="number"
                                            v-model.number="form.phone"
                                            class="h-12 rounded-sm shadow-none pl-11 border-primary-700"
                                            :invalid="!!form.errors.phone"
                                        />
                                    </div>
                                    <InputError v-if="form.errors.phone">
                                        {{ form.errors.phone }}
                                    </InputError>
                                </TableCell>
                                <TableCell class="space-y-2">
                                    <InputLabel>Email</InputLabel>

                                    <Input
                                        v-model="form.email"
                                        class="h-12 rounded-sm shadow-none border-primary-700"
                                        :invalid="!!form.errors.email"
                                    />
                                    <InputError v-if="form.errors.email">
                                        {{ form.errors.email }}
                                    </InputError>
                                </TableCell>
                            </TableRow>

                            <TableRow class="grid grid-cols-2 border-none">
                                <TableCell class="space-y-2">
                                    <InputLabel>ID Type</InputLabel>
                                    <Select v-model="form.id_type">
                                        <SelectTrigger
                                            class="h-12 rounded-sm shadow-none border-primary-700"
                                            :invalid="!!form.errors.id_type"
                                        >
                                            <SelectValue
                                                placeholder="Select ID type"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem
                                                    v-for="id in validIds"
                                                    :key="id.id"
                                                    :value="id.name"
                                                >
                                                    {{ id.name }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <InputError v-if="form.errors.id_type">
                                        {{ form.errors.id_type }}
                                    </InputError>
                                </TableCell>

                                <TableCell class="space-y-2">
                                    <InputLabel>ID Number</InputLabel>
                                    <Input
                                        v-model="form.employee_id"
                                        class="h-12 rounded-sm shadow-none border-primary-700"
                                        :invalid="!!form.errors.employee_id"
                                    />
                                    <InputError v-if="form.errors.employee_id">
                                        {{ form.errors.employee_id }}
                                    </InputError>
                                </TableCell>
                            </TableRow>

                            <TableRow class="grid border-none">
                                <TableCell class="space-y-2">
                                    <InputLabel>Purpose of stay</InputLabel>
                                    <Textarea
                                        v-model="form.purpose_of_stay"
                                        class="border border-primary-800"
                                        rows="4"
                                        cols="50"
                                    />
                                </TableCell>
                            </TableRow>
                        </div>

                        <div class="md:col-span-7">
                            <div
                                class="flex items-center justify-between h-8 px-2 gap-x-10"
                            >
                                <p class="text-lg font-medium text-neutral-800">
                                    Guests Details
                                </p>
                                <Button
                                    v-if="
                                        form.first_name &&
                                        form.last_name &&
                                        firstGuestHasFilled === false
                                    "
                                    type="button"
                                    @click="addEmployeeAsFirstGuest"
                                    class="bg-blue-500 hover:bg-blue-600 animate-bounce"
                                >
                                    <ArrowDown />Add employee as first guest
                                </Button>
                            </div>

                            <GuestsDetailsInput :form="form" />

                            <div class="px-2">
                                <Button
                                    type="submit"
                                    class="w-full h-12 mt-6 text-base"
                                    :disabled="form.processing"
                                >
                                    {{
                                        form.processing
                                            ? "Submitting..."
                                            : "Submit Reservation"
                                    }}
                                </Button>
                            </div>
                        </div>
                    </TableBody>
                </Table>
            </form>
        </div>
    </div>

    <Alert
        :open="confirmation"
        @update:open="confirmation = $event"
        :onConfirm="submit"
        title="Are you sure you want to submit this reservation?"
        description="Please confirm that all the details are correct before proceeding."
        confirm-label="Confirm"
    />
</template>
