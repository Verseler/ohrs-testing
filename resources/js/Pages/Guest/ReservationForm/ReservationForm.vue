<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { Table, TableCell, TableRow, TableBody } from "@/Components/ui/table";
import TableSectionHeading from "@/Pages/Guest/ReservationForm/Partials/TableSectionHeading.vue";
import { Textarea } from "@/Components/ui/textarea";
import { Input, InputDate, InputError } from "@/Components/ui/input";
import InputLabel from "@/Components/ui/input/InputLabel.vue";
import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import type { Office } from "@/Pages/Admin/Office/office.types";
import { ref, watch } from "vue";
import GuestsDetailsInput from "@/Pages/Guest/ReservationForm/Partials/GuestsDetailsInput.vue";
import { Button } from "@/Components/ui/button";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import type { Gender } from "@/Pages/Guest/guest.types";
import { validIds } from "@/Pages/Guest/ReservationForm/data";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Label } from "@/Components/ui/label";
import type { ReservationType } from "@/Pages/Admin/Reservation/reservation.types";
import { formatDate } from "@/lib/utils";
import { AutoComplete } from "@/Components/ui/auto-complete";

type ReservationFormProps = {
    hostelOffice: Office;
    offices: Office[];
};

const { hostelOffice, offices } = defineProps<ReservationFormProps>();

const DEFAULT_FIRST_GUEST = {
    first_name: undefined as string | undefined,
    last_name: undefined as string | undefined,
    gender: undefined as Gender | undefined,
    office: undefined as string | undefined,
    check_in_date: undefined as string | undefined,
    check_out_date: undefined as string | undefined,
};

const form = useForm({
    first_name: "",
    middle_initial: undefined,
    last_name: "",
    phone: undefined,
    email: "",
    hostel_office_id: hostelOffice.id,
    id_type: undefined,
    employee_id: "",
    purpose_of_stay: "",
    guests: [DEFAULT_FIRST_GUEST],
});

const reservationType = ref<ReservationType>('solo');

watch([
() => form.first_name,
() => form.last_name
], () => {
    if (reservationType.value === 'solo') {
        form.guests[0].first_name = form.first_name || '';
        form.guests[0].last_name = form.last_name || '';
    }
});

//confirmation dialog
const confirmation = ref(false);

function showConfirmation() {
    confirmation.value = true;
}

function submit() {
    //if reservation type is solo make sure the name matched
    if (reservationType.value === 'solo') {
        form.guests[0].first_name = form.first_name || '';
        form.guests[0].last_name = form.last_name || '';
        
    }

    form.post(route("reservation.create"));
}
</script>

<template>
    <Head title="Reservation Form" />

    <GuestLayout>
        <div class="container px-2 py-4 mx-auto md:p-8">
            <form @submit.prevent="showConfirmation">
                <Table class="overflow-hidden">
                    <TableRow class="border-none">
                        <TableCell class="text-2xl font-bold">
                            {{ hostelOffice.hostel_name || hostelOffice.name }}
                        </TableCell>
                    </TableRow>

                    <TableBody>
                        <TableSectionHeading>
                            Reservation Type
                        </TableSectionHeading>
                        <RadioGroup
                                v-model="reservationType"
                                class="mx-3 mb-6"
                            >
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="solo" value="solo" />
                                    <Label for="solo">Solo</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem id="group" value="group" />
                                    <Label for="group">Group</Label>
                                </div>
                            </RadioGroup>

                        <TableSectionHeading>
                            Contact Information of Person Making the Reservation
                        </TableSectionHeading>

                        <div class="grid mb-6 lg:grid-cols-2">
                            <!-- Left Column -->
                            <div>
                                <TableRow
                                    class="grid border-none md:grid-cols-5"
                                >
                                    <TableCell class="space-y-2 md:col-span-2">
                                        <InputLabel>First Name</InputLabel>
                                        <Input
                                            v-model="form.first_name"
                                            class="h-12 rounded-sm shadow-none border-primary-700"
                                            :invalid="!!form.errors.first_name"
                                        />
                                        <InputError
                                            v-if="form.errors.first_name"
                                        >
                                            {{ form.errors.first_name }}
                                        </InputError>
                                    </TableCell>

                                    <TableCell class="space-y-2">
                                        <InputLabel>M.I.</InputLabel>
                                        <Input
                                            v-model="form.middle_initial"
                                            class="h-12 rounded-sm shadow-none border-primary-700"
                                            :invalid="
                                                !!form.errors.middle_initial
                                            "
                                            maxlength="1"
                                        />
                                        <InputError
                                            v-if="form.errors.middle_initial"
                                        >
                                            {{ form.errors.middle_initial }}
                                        </InputError>
                                    </TableCell>

                                    <TableCell class="space-y-2 md:col-span-2">
                                        <InputLabel>Last Name</InputLabel>
                                        <Input
                                            v-model="form.last_name"
                                            class="h-12 rounded-sm shadow-none border-primary-700"
                                            :invalid="!!form.errors.last_name"
                                        />
                                        <InputError
                                            v-if="form.errors.last_name"
                                        >
                                            {{ form.errors.last_name }}
                                        </InputError>
                                    </TableCell>
                                </TableRow>

                                <TableRow class="grid border-none">
                                    <TableCell class="space-y-2">
                                        <InputLabel>Phone #</InputLabel>
                                        <div class="relative">
                                            <span
                                                class="absolute top-[15.3px] text-neutral-700 left-2"
                                            >
                                                (+63)
                                            </span>
                                            <Input
                                                type="number"
                                                v-model.number="form.phone"
                                                class="pl-11 h-12 rounded-sm shadow-none border-primary-700"
                                                :invalid="!!form.errors.phone"
                                            />
                                        </div>
                                        <InputError v-if="form.errors.phone">
                                            {{ form.errors.phone }}
                                        </InputError>
                                    </TableCell>
                                </TableRow>

                                <TableRow
                                    class="grid border-none md:grid-cols-2"
                                >
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
                                        <InputError
                                            v-if="form.errors.employee_id"
                                        >
                                            {{ form.errors.employee_id }}
                                        </InputError>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="reservationType === 'solo'" class="grid md:grid-cols-2">
                                    <TableCell class="space-y-2">
                                        <InputLabel>Check In</InputLabel>
                                        <InputDate
                                            v-model="form.guests[0].check_in_date"
                                            :invalid="!!(form.errors as Record<string, string>)[`guests.0.check_in_date`]"
                                            :min="formatDate(new Date())"
                                            :max="form.guests[0].check_out_date"
                                        />
                                        <InputError
                                            v-if="!!(form.errors as Record<string, string>)[`guests.0.check_in_date`]"
                                        >
                                            {{ (form.errors as Record<string, string>)[`guests.0.check_in_date`] }}
                                        </InputError>
                                    </TableCell>

                                    <TableCell class="space-y-2">
                                        <InputLabel>Check Out</InputLabel>
                                        <InputDate
                                            v-model="form.guests[0].check_out_date"
                                            :invalid="
                                                !!(form.errors as Record<string, string>)[`guests.0.check_out_date`]
                                            "
                                            :min="form.guests[0].check_in_date"
                                            :disabled="!form.guests[0].check_in_date"
                                        />
                                        <InputError
                                            v-if="!!(form.errors as Record<string, string>)[`guests.0.check_out_date`]"
                                        >
                                            {{ (form.errors as Record<string, string>)[`guests.0.check_out_date`] }}
                                        </InputError>
                                    </TableCell>
                                </TableRow>
                            </div>

                            <!-- Right Column -->
                            <div class="grid grid-cols-1">
                                <TableCell class="space-y-2">
                                    <InputLabel>Email</InputLabel>

                                    <Input
                                        v-model="form.email"
                                        class="h-12 rounded-sm shadow-none border-primary-700"
                                        :invalid="!!form.errors.email"
                                        placeholder="example@gmail.com"
                                    />
                                    <InputError v-if="form.errors.email">
                                        {{ form.errors.email }}
                                    </InputError>
                                </TableCell>

                                <TableCell class="space-y-2">
                                    <InputLabel>Purpose of stay</InputLabel>
                                    <Textarea
                                        v-model="form.purpose_of_stay"
                                        :invalid="!!form.errors.purpose_of_stay"
                                        class="border border-primary-800"
                                        placeholder="Describe the purpose of your stay..."
                                        rows="6"
                                        cols="50"
                                    />
                                    <InputError
                                        v-if="!!form.errors.purpose_of_stay"
                                    >
                                        {{ form.errors.purpose_of_stay }}
                                    </InputError>
                                </TableCell>

                                <TableRow v-if="reservationType === 'solo'" class="grid md:grid-cols-2">
                                    <TableCell class="space-y-2">
                                        <InputLabel>Gender</InputLabel>

                                        <Select class="flex-1" v-model="form.guests[0].gender">
                                            <SelectTrigger
                                                class="h-12 rounded-md shadow-none border-primary-800"
                                                :invalid="!!(form.errors as Record<string, string>)[`guests.0.gender`]"
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

                                        <InputError v-if="!!(form.errors as Record<string, string>)[`guests.0.gender`]">
                                            {{ (form.errors as Record<string, string>)[`guests.0.gender`] }}
                                        </InputError>
                                    </TableCell>

                                    <TableCell class="space-y-2">
                                        <InputLabel>Office</InputLabel>
                                        <AutoComplete
                                            v-model="form.guests[0].office as (Office | undefined)"
                                            :items="offices"
                                            :invalid="!!(form.errors as Record<string, string>)[`guests.0.office`]"
                                        />
                                        <InputError v-if="!!(form.errors as Record<string, string>)[`guests.0.office`]">
                                            {{ (form.errors as Record<string, string>)[`guests.0.office`] }}
                                        </InputError>
                                    </TableCell>
                                </TableRow>
                            </div>
                        </div>

                        <TableSectionHeading v-if="reservationType === 'group'">
                            Guests Details
                        </TableSectionHeading>

                        <GuestsDetailsInput v-if="reservationType === 'group'" :form="form" :offices="offices" />

                        <div class="px-2">
                            <Button
                                type="submit"
                                class="mt-6 w-full h-12 text-base"
                                :disabled="form.processing"
                            >
                                {{
                                    form.processing
                                        ? "Submitting..."
                                        : "Submit Reservation"
                                }}
                            </Button>
                        </div>
                    </TableBody>
                </Table>
            </form>
        </div>
        <Alert
            :open="confirmation"
            @update:open="confirmation = $event"
            :onConfirm="submit"
            title="Are you sure you want to submit this reservation?"
            description="Please confirm that all the details are correct before proceeding."
            confirm-label="Confirm"
        />
    </GuestLayout>
</template>
