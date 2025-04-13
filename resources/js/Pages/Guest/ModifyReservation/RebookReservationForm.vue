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
import { computed, ref } from "vue";
import GuestsDetailsInput from "@/Pages/Guest/ReservationForm/Partials/GuestsDetailsInput.vue";
import { Button } from "@/Components/ui/button";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { validIds } from "@/Pages/Guest/ReservationForm/data";
import type { Reservation } from "@/Pages/Admin/Reservation/reservation.types";
import type { Office } from "@/Pages/Admin/Office/office.types";

type RebookReservationFormProps = {
    reservation: Reservation;
    offices: Office[];
};

const { reservation, offices } = defineProps<RebookReservationFormProps>();

const defaultGuests = computed(() => {
    const formattedGuests = reservation.guests.map((guest) => {
        return {
            first_name: guest.first_name,
            last_name: guest.last_name,
            gender: guest.gender,
            office: guest.office,
            check_in_date: guest.stay_details.check_in_date,
            check_out_date: guest.stay_details.check_out_date,
        };
    });

    return JSON.parse(JSON.stringify(formattedGuests));
});

const form = useForm({
    prev_reservation_id: reservation.id,
    first_name: reservation.first_name,
    middle_initial: reservation.middle_initial,
    last_name: reservation.last_name,
    phone: reservation.phone,
    email: reservation.email,
    hostel_office_id: reservation.hostel_office.id,
    id_type: reservation.id_type,
    employee_id: reservation.employee_id,
    purpose_of_stay: reservation.purpose_of_stay,
    guests: defaultGuests.value,
});

//confirmation dialog
const confirmation = ref(false);

function showConfirmation() {
    confirmation.value = true;
}

function submit() {
    form.post(route("reservation.rebook"));
}
</script>

<template>
    <Head title="Reservation Form" />

    <div class="w-full min-h-screen">
        <Header />
        <div class="container px-2 py-4 mx-auto md:p-8">
            <form @submit.prevent="showConfirmation">
                <Table class="overflow-hidden">
                    <TableRow class="border-none">
                        <TableCell class="text-2xl font-bold">
                            {{
                                reservation.hostel_office.hostel_name ||
                                reservation.hostel_office.name
                            }}
                            Reservation Rebook
                        </TableCell>
                    </TableRow>

                    <TableBody>
                        <TableSectionHeading>
                            Contact Information of Person Making the Reservation
                        </TableSectionHeading>

                        <div class="grid mb-8 lg:grid-cols-2">
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
                                                class="h-12 rounded-sm shadow-none pl-11 border-primary-700"
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
                            </div>
                        </div>

                        <TableSectionHeading>
                            Guests Details
                        </TableSectionHeading>

                        <GuestsDetailsInput :form="form" :offices="offices" />

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
                    </TableBody>
                </Table>
            </form>
        </div>
    </div>

    <Alert
        :open="confirmation"
        @update:open="confirmation = $event"
        :onConfirm="submit"
        title="Are you sure you want to rebook your reservation?"
        description="This will cancel your previous reservation."
        confirm-label="Confirm"
    />
</template>
