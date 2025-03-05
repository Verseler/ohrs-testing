<script setup lang="ts">
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import Button from "@/Components/ui/button/Button.vue";
import type { Office, SharedData } from "@/types";
import { Label } from "@/Components/ui/label";
import { Input, InputError } from "@/Components/ui/input";
import DatePickerField from "@/Components/DatePickerField.vue";
import { Table, TableRow, TableCell } from "@/Components/ui/table";
import GuestField from "@/Components/GuestField.vue";
import { toast } from "vue-sonner";
import { Toaster } from "@/Components/ui/sonner";
import { Textarea } from "@/Components/ui/textarea";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select'
=
import { watch } from "vue";
import { formatDate } from "@/lib/utils";

type LandingPageProps = {
    canLogin: boolean;
    offices: Office[];
    hostOffice: Office
}


const { canLogin, offices, hostOffice } = defineProps<LandingPageProps>();

const page = usePage<SharedData>();


const form  = useForm({
    total_guests: 0,
    total_males: 0,
    total_females: 0,
    check_in_date: undefined,
    check_out_date: undefined,
    first_name: "",
    middle_initial: undefined,
    last_name: "",
    phone: undefined,
    email: undefined,
    guest_office_id: undefined,
    host_office: 1, //"regional executive office",
    employee_identification: "",
    purpose_of_stay: undefined,
    host_office_id: hostOffice.id
});



function increaseFemales() {
    form.total_females++;
    form.total_guests++;
}

function decreaseFemales() {
    if (form.total_females > 0) {
        form.total_females--;
        form.total_guests--;
    }
}

function increaseMales() {
    form.total_males++;
    form.total_guests++;
}

function decreaseMales() {
    if (form.total_males > 0) {
        form.total_males--;
        form.total_guests--;
    }
}

// Display flash success or error message as sonner or toast
watch(
    () => page.props.flash.error,
    () => {
        if (page.props.flash.error) {
            toast.warning(page.props.flash.error, {
                style: {
                    background: "#eab308",
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


function submit() {
    form.check_in_date = formatDate(form.check_in_date);
    form.check_out_date = formatDate(form.check_out_date);

    form.post(route("reservation.create"), {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Hostel Reservation" />

    <div class="w-full min-h-screen">
        <header
            class="container flex items-center justify-between w-full p-2 md:px-4 mx-auto bg-white"
        >
            <Link href="/">
                <div class="flex items-center gap-x-2">
                    <ApplicationLogo />
                    <p class="text-xl font-bold text-primary-500">
                        <span class="text-yellow-300">H</span>ostel
                        <span class="text-yellow-300">R</span>eservation
                        <span class="text-yellow-300">S</span>ystem
                    </p>
                </div>
            </Link>

            <div class="inline-flex items-center gap-2">
                <nav v-if="canLogin">
                    <Link
                        v-if="page.props.auth.user"
                        :href="route('dashboard')"
                    >
                        <Button class="px-6">Dashboard</Button>
                    </Link>

                    <template v-else>
                        <Link :href="route('login')">
                            <Button class="px-6">Log in</Button>
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <div  class="container mx-auto px-2 py-4 md:p-8 flex flex-col-reverse lg:flex-row items-start gap-10">
            <form @submit.prevent="submit">
                <Table class="max-w-3xl">
                    <TableRow class="border-none">
                        <TableCell class="text-lg font-bold">
                            Reservation
                        </TableCell>
                    </TableRow>
                    <TableRow
                        class="grid md:grid-cols-3 border-t rounded border-e border-s border-primary-700"
                    >
                        <TableCell class="p-0 border-e border-primary-700">
                            <GuestField
                                :female="form.total_females"
                                :increase-female="increaseFemales"
                                :decrease-female="decreaseFemales"
                                :male="form.total_males"
                                :increase-male="increaseMales"
                                :decrease-male="decreaseMales"
                                :error-total-guests="!!form.errors.total_guests"
                                :error-total-females="!!form.errors.total_females"
                                :error-total-males="!!form.errors.total_males"
                            />
                        </TableCell>
                        <TableCell class="p-0 border-e border-primary-700">
                            <DatePickerField
                                v-model="form.check_in_date"
                                label="Check-in"
                                class="hover:bg-primary-50"
                                :invalid="!!form.errors.check_in_date"
                                :max-value="form.check_out_date"
                            />
                        </TableCell>
                        <TableCell class="p-0">
                            <DatePickerField
                                v-model="form.check_out_date"
                                label="Check-out"
                                class="hover:bg-primary-50"
                                :invalid="!!form.errors.check_out_date"
                                :min-value="form.check_in_date"
                            />
                        </TableCell>
                    </TableRow>

                    <TableRow class="grid grid-cols-3 border-none">
                        <TableCell>
                            <InputError v-if="form.errors.total_guests">
                                {{ form.errors.total_guests }}
                            </InputError>
                        </TableCell>
                        <TableCell>
                            <InputError v-if="form.errors.check_in_date">
                                {{ form.errors.check_in_date }}
                            </InputError>
                        </TableCell>
                        <TableCell>
                            <InputError v-if="form.errors.check_out_date">
                                {{ form.errors.check_out_date }}
                            </InputError>
                        </TableCell>
                    </TableRow>

                    <!-- <TableRow>
                        Calendar
                    </TableRow> -->

                    <TableRow class="border-none">
                        <TableCell class="pt-5 text-lg font-bold">
                            Contact Info
                        </TableCell>
                    </TableRow>

                    <TableRow class="grid grid-cols-1 md:grid-cols-5 border-none">
                        <TableCell class="col-span-2 space-y-2">
                            <Label>First Name</Label>
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
                            <Label>
                                M.I.
                                <span class="text-xs italic text-neutral-500">
                                    (optional)
                                </span>
                            </Label>
                            <Input
                                v-model="form.middle_initial"
                                class="h-12 rounded-sm shadow-none border-primary-700"
                                :invalid="!!form.errors.middle_initial"
                            />
                            <InputError v-if="form.errors.middle_initial">
                                {{ form.errors.middle_initial }}
                            </InputError>
                        </TableCell>

                        <TableCell class="col-span-2 space-y-2">
                            <Label>Last Name</Label>
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

                    <TableRow class="grid md:grid-cols-2 border-none">
                        <TableCell class="space-y-2">
                            <Label>Phone #</Label>
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
                            <Label>
                                Email
                                <span class="text-xs italic text-neutral-500">
                                    (optional)
                                </span>
                            </Label>

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

                    <TableRow class="grid pb-5 border-none">
                        <TableCell class="space-y-2">
                            <Label>Guest's Office</Label>
                            <Select v-model="form.guest_office_id">
                                <SelectTrigger
                                    class="h-12 rounded-sm shadow-none border-primary-700"
                                    :invalid="!!form.errors.guest_office_id"
                                >
                                     <SelectValue placeholder="Select guest's office" />
                                </SelectTrigger>
                                <SelectContent>
                                <SelectGroup>
                                    <SelectItem v-for="office in offices" :Key="office.id" :value="office.id">
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

                    <TableRow class="grid pb-5 border-none">
                        <TableCell class="space-y-2">
                            <Label>Employee ID</Label>
                            <!-- Change this to Select later -->
                            <Input
                                v-model="form.employee_identification"
                                class="h-12 rounded-sm shadow-none border-primary-700"
                                :invalid="!!form.errors.employee_identification"
                            />
                            <InputError v-if="form.errors.employee_identification">
                                {{ form.errors.employee_identification }}
                            </InputError>
                        </TableCell>
                    </TableRow>

                    <TableRow class="grid border-none">
                        <TableCell class="space-y-2">
                            <Label>Purpose of stay
                                <span class="text-xs italic text-neutral-500">
                                    (optional)
                                </span>
                            </Label>
                            <Textarea v-model="form.purpose_of_stay"  class="border border-primary-800" rows="4" cols="50" placeholder="Purpose of stay . . ." />
                        </TableCell>
                    </TableRow>

                    <TableRow class="border-none">
                        <TableCell>
                            <Button size="lg" class="w-full rounded-sm">
                                Submit
                            </Button>
                        </TableCell>
                    </TableRow>
                </Table>
            </form>

        </div>

        <Toaster />
    </div>
</template>
