<script setup lang="ts">
import { computed, ref } from "vue";
import { UserIcon, ShieldCheckIcon, Home, CreditCard } from "lucide-vue-next";
import { Button } from "@/Components/ui/button";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import BackLink from "@/Components/BackLink.vue";
import PageHeader from "@/Components/PageHeader.vue";
import type { Reservation } from "@/Pages/Admin/Reservation/reservation.types";
import { useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/ui/input/InputLabel.vue";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Textarea } from "@/Components/ui/textarea";
import { Guest, GuestBeds } from "@/Pages/Guest/guest.types";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { InputError } from "@/Components/ui/input";

type ExemptPaymentFormProps = {
    reservation: Reservation;
};

const { reservation } = defineProps<ExemptPaymentFormProps>();

const form = useForm({
    reservation_id: reservation.id,
    selected_guest_id: null,
    reason: "",
});

type SelectedGuest = Guest & {
    guest_beds: GuestBeds[];
};

const selectedGuest = computed<SelectedGuest | null>(() => {
    const guest = reservation.guests.find(
        (guest) => guest.id === form.selected_guest_id
    );

    return guest ? (guest as SelectedGuest) : null;
});

// Dialog Confirmation
const confirmation = ref(false);

function showConfirmation() {
    confirmation.value = true;
}

function submit() {
    form.post(route("reservation.exemptPayment"));
}
</script>

<template>
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
                        <BreadcrumbLink :href="route('reservation.list')">
                            Reservation Management
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbLink
                            :href="
                                route('reservation.show', {
                                    id: reservation.id,
                                })
                            "
                        >
                            Reservation Details
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>Exempt Guest</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <BackLink
                :href="route('reservation.show', { id: reservation.id })"
            />
        </div>

        <PageHeader>
            <template #icon><CreditCard /></template>
            <template #title>Exempt Guest Payment</template>
        </PageHeader>

        <form
            @submit.prevent="showConfirmation"
            class="flex-1 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8"
        >
            <!-- Guest selection -->
            <div
                v-if="selectedGuest"
                class="mb-6 text-white rounded-lg shadow bg-primary-500"
            >
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium">Selected Guest</h3>
                    <div class="flex items-center mt-4">
                        <div
                            class="flex items-center justify-center rounded-full size-14 bg-primary-100"
                        >
                            <UserIcon class="text-primary-500" />
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-medium">
                                {{ selectedGuest.first_name }}
                                {{ selectedGuest.last_name }}
                            </h4>
                            <p class="text-sm text-neutral-100">
                                Room:
                                {{ selectedGuest.guest_beds[0].bed.room.name }}
                                • Bed:
                                {{ selectedGuest.guest_beds[0].bed.name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment exemption form -->
            <div class="overflow-hidden bg-white rounded-lg shadow">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">
                        Exemption Details
                    </h3>

                    <!-- Conditional fields -->
                    <div class="space-y-6">
                        <!-- Reason selection -->
                        <div>
                            <InputLabel> Guest </InputLabel>
                            <Select v-model="form.selected_guest_id">
                                <SelectTrigger
                                    class="h-12"
                                    :invalid="!!form.errors.selected_guest_id"
                                >
                                    <SelectValue placeholder="Select a guest" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Guests</SelectLabel>
                                        <SelectItem
                                            v-for="guest in reservation.guests"
                                            :key="guest.id"
                                            :value="guest.id"
                                        >
                                            {{ guest.first_name }}
                                            {{ guest.last_name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError v-if="!!form.errors.selected_guest_id">
                                {{ form.errors.selected_guest_id }}
                            </InputError>
                        </div>

                        <!-- Notes field -->
                        <div>
                            <InputLabel
                                for="notes"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Reason
                            </InputLabel>
                            <Textarea
                                id="notes"
                                v-model="form.reason"
                                rows="3"
                                class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Add any additional information about this exemption..."
                            ></Textarea>
                            <InputError v-if="!!form.errors.reason">
                                {{ form.errors.reason }}
                            </InputError>
                        </div>

                        <!-- Approval section -->
                        <div class="p-4 rounded-md bg-gray-50">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <ShieldCheckIcon
                                        class="w-5 h-5 text-blue-500"
                                    />
                                </div>
                                <div class="ml-3">
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Super Admin Only
                                    </h4>
                                    <p class="text-sm text-gray-500">
                                        Payment exemptions are restricted to
                                        super administrators only. All actions
                                        will be logged to ensure accountability
                                        and transparency.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid place-content-end">
                            <Button type="submit" size="lg" class="ml-auto">
                                Confirm
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <Alert
            :open="confirmation"
            @update:open="confirmation = $event"
            :onConfirm="submit"
            title="Are you sure you want to exempt this guest's payment?"
            description="Exempting the payment will waive the charges for the selected guest. This cannot be undone."
        />
    </AuthenticatedLayout>
</template>
