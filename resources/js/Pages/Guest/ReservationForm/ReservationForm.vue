<script setup lang="ts">
import { Head, useForm, usePage } from "@inertiajs/vue3";
import Header from "@/Components/Header.vue";
import { Table, TableCell, TableRow } from "@/Components/ui/table";
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
import type { Office, Region } from "@/Pages/Admin/Office/office.types";
import type { SharedData } from "@/types";
import { computed, ref, watch } from "vue";
import DatePicker from "@/Components/DatePicker.vue";
import { yesterdayDate } from "@/lib/utils";
import GuestsDetailsInput from "@/Pages/Guest/ReservationForm/Partials/GuestsDetailsInput.vue";
import { Button } from "@/Components/ui/button";

type ReservationFormProps = {
    canLogin: boolean;
    regions: Region[];
    offices: Office[];
    hostelOffice: Office;
};

const { canLogin, offices, hostelOffice } = defineProps<ReservationFormProps>();

const page = usePage<SharedData>();

const DEFAULT_FIRST_GUEST = {
    first_name: undefined,
    last_name: undefined,
    gender: undefined,
    phone: null,
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
    guest_office_id: undefined,
    employee_id: "",
    purpose_of_stay: "",
});

const selectedRegionId = ref<Region["id"] | null>(null);

const officesInARegion = computed(() =>
    offices.filter((office) => office.region_id === selectedRegionId.value)
);

//clear selected office if region is changed
watch(selectedRegionId, () => {
    form.guest_office_id = undefined;
});

function submit() {
    form.post(route("reservation.create"));
}
</script>

<template>
    <Head title="Reservation Form" />

    <div class="w-full min-h-screen">
        <Header :can-login="canLogin" :user="page.props.auth.user" />

        <div class="px-2 py-4 md:p-8">
            <form @submit.prevent="submit">
                <Table class="max-w-3xl">
                    <TableRow class="border-none">
                        <TableCell class="text-2xl font-bold">
                            {{ hostelOffice.name }} Reservation
                        </TableCell>
                    </TableRow>

                    <TableRow class="grid border-none md:grid-cols-2">
                        <TableCell class="space-y-2">
                            <InputLabel>Check In</InputLabel>
                            <DatePicker
                                v-model="form.check_in_date"
                                :invalid="!!form.errors.check_in_date"
                                :min-value="yesterdayDate"
                                :max-value="form.check_out_date"
                            />
                            <InputError v-if="form.errors.check_in_date">
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
                            <InputError v-if="form.errors.check_out_date">
                                {{ form.errors.check_out_date }}
                            </InputError>
                        </TableCell>
                    </TableRow>

                    <TableSectionHeading>
                        Contact Person Info
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
                            <InputError v-if="form.errors.middle_initial">
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
                            <Input
                                type="number"
                                v-model.number="form.phone"
                                class="h-12 rounded-sm shadow-none border-primary-700"
                                :invalid="!!form.errors.phone"
                            />
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

                    <TableRow class="grid border-none md:grid-cols-2">
                        <TableCell class="space-y-2">
                            <InputLabel>Guest's Region</InputLabel>
                            <Select v-model="selectedRegionId">
                                <SelectTrigger
                                    class="h-12 rounded-sm shadow-none border-primary-700"
                                    :invalid="!!form.errors.guest_office_id"
                                >
                                    <SelectValue
                                        placeholder="Select guest's region"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem
                                            v-for="region in regions"
                                            :Key="region.id"
                                            :value="region.id"
                                        >
                                            {{ region.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError v-if="form.errors.guest_office_id">
                                {{ form.errors.guest_office_id }}
                            </InputError>
                        </TableCell>
                        <TableCell class="space-y-2">
                            <InputLabel>Guest's Office</InputLabel>
                            <Select
                                v-model="form.guest_office_id"
                                :disabled="Boolean(selectedRegionId) === false"
                            >
                                <SelectTrigger
                                    class="h-12 rounded-sm shadow-none border-primary-700"
                                    :invalid="!!form.errors.guest_office_id"
                                >
                                    <SelectValue
                                        placeholder="Select guest's office"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem
                                            v-for="office in officesInARegion"
                                            :Key="office.id"
                                            :value="office.id"
                                        >
                                            {{ office.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError v-if="form.errors.guest_office_id">
                                {{ form.errors.guest_office_id }}
                            </InputError>
                        </TableCell>
                    </TableRow>
                    <TableRow class="grid border-none">
                        <TableCell class="space-y-2">
                            <InputLabel>Employee ID</InputLabel>
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
                            <InputLabel optional>Purpose of stay</InputLabel>
                            <Textarea
                                v-model="form.purpose_of_stay"
                                class="border border-primary-800"
                                rows="4"
                                cols="50"
                            />
                        </TableCell>
                    </TableRow>

                    <TableSectionHeading>
                        <div class="flex items-center justify-between gap-x-1">
                            <p>Guests Details</p>
                            <p
                                class="text-xs text-center pt-1.5 text-white rounded h-7 min-w-7 p-2 bg-primary-500"
                            >
                                {{ form.guests.length }}
                            </p>
                        </div>
                    </TableSectionHeading>

                    <GuestsDetailsInput :form="form" />

                    <TableRow class="border-b-0">
                        <TableCell>
                            <Button
                                type="submit"
                                class="w-full h-12 mt-4 text-base"
                                >Submit</Button
                            >
                        </TableCell>
                    </TableRow>
                </Table>
            </form>
        </div>
    </div>
</template>
