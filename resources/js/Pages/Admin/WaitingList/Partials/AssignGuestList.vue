<script setup lang="ts">
import { ReservationWithBeds } from "@/Pages/Admin/Reservation/reservation.types";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Button } from "@/Components/ui/button";
import { Bed } from "@/Pages/Admin/Room/room.types";
import { useForm } from "@inertiajs/vue3";
import { watch, ref } from "vue";
import { Gender } from "@/Pages/Guest/guest.types";
import GenderBadge from "@/Components/GenderBadge.vue";
import LinkButton from "@/Components/LinkButton.vue";
import { RefreshCw } from "lucide-vue-next";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";

type AssignGuestListProps = {
    reservation: ReservationWithBeds;
    availableBeds: Bed[];
};

// Initialize component data
const { reservation, availableBeds } = defineProps<AssignGuestListProps>();

// Prepare guest data for the form
const prepareGuests = () => {
    return reservation.guests.map((guest) => ({
        id: guest.id,
        name: `${guest.first_name} ${guest.last_name}`,
        gender: guest.gender as Gender,
        bed_id: null as number | null,
    }));
};

// Initialize form with prepared guests
const form = useForm({
    guests: prepareGuests(),
});

// Track assigned bed IDs
const assignedBedIds = ref<number[]>([]);

// Update assigned beds when form changes
const updateAssignedBeds = () => {
    assignedBedIds.value = form.guests
        .filter((guest) => guest.bed_id)
        .map((guest) => guest.bed_id!);
};

// Watch for changes in guest bed assignments
watch(form.guests, updateAssignedBeds, { deep: true });

// Get available beds excluding assigned ones
const getAvailableBeds = () => {
    return availableBeds.filter(
        (bed) => !assignedBedIds.value.includes(Number(bed.id))
    );
};

// Filter beds by gender compatibility
const filterBedsByGender = (beds: Bed[], gender: Gender) => {
    return beds.filter(
        (bed) =>
            bed.room.eligible_gender === "any" ||
            bed.room.eligible_gender === gender
    );
};

// Update room gender when first bed is assigned
const updateRoomGender = (bedId: number, gender: Gender) => {
    const bed = availableBeds.find((b) => b.id === bedId);
    if (bed?.room.eligible_gender === "any") {
        availableBeds
            .filter((b) => b.room.id === bed.room.id)
            .forEach((b) => (b.room.eligible_gender = gender));
    }
};

// Get formatted bed name for display
const getBedName = (bedId: number) => {
    const bed = availableBeds.find((b) => b.id === bedId);
    return bed ? `${bed.room.name} - ${bed.name}` : "";
};

// Handle bed selection
const onBedSelect = (guestId: number, bedId: number) => {
    const guest = form.guests.find((g) => g.id === guestId);
    if (guest) {
        guest.bed_id = bedId;
        updateRoomGender(bedId, guest.gender);
    }
};

//* SUBMIT
const submitConfirmation = ref(false);

function showSubmitConfirmation() {
    submitConfirmation.value = true;
}

function submit() {
    console.log("handle submit");
}
</script>

<template>
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <p class="text-2xl font-bold text-primary-600">Guests</p>
            <LinkButton
                variant="outline"
                :href="route('reservation.assignBeds', { id: reservation.id })"
                class="text-red-500 border-red-500 hover:bg-red-50 hover:text-red-600"
            >
                <RefreshCw />Reset
            </LinkButton>
        </div>

        <div
            v-for="(guest, index) in form.guests"
            :key="guest.id"
            class="grid items-center grid-cols-10 gap-x-2"
        >
            <p class="col-span-3 capitalize">
                <span class="text-lg font-bold text-primary-600"
                    >{{ index + 1 }}.</span
                >
                {{ guest.name }}
            </p>
            <div
                class="grid h-full col-span-2 px-4 capitalize border rounded place-content-center"
                :class="{
                    'bg-blue-50 text-blue-500 border-blue-500':
                        guest.gender === 'male',
                    'bg-red-50 text-red-500 border-red-500':
                        guest.gender === 'female',
                }"
            >
                <p>{{ guest.gender }}</p>
            </div>

            <Select
                v-bind:modelValue="guest.bed_id"
                @update:modelValue="
                    (value) => onBedSelect(guest.id, Number(value))
                "
            >
                <SelectTrigger
                    class="h-12 col-span-5 rounded-sm shadow-none border-primary-700"
                >
                    <SelectValue placeholder="Select bed">{{
                        guest.bed_id ? getBedName(guest.bed_id) : "Select bed"
                    }}</SelectValue>
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectItem
                            v-for="bed in filterBedsByGender(
                                getAvailableBeds(),
                                guest.gender
                            )"
                            :key="bed.id"
                            :value="bed.id"
                        >
                            <div class="flex items-center justify-between">
                                <span class="block text-sm">
                                    {{ `${bed.room.name} - ${bed.name}` }}
                                </span>

                                <GenderBadge
                                    :gender="bed.room.eligible_gender"
                                />
                            </div>
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>

        <Button
            @click="showSubmitConfirmation"
            type="button"
            class="w-full h-12 text-base border border-primary-600"
        >
            Confirm Reservation
        </Button>

        <Alert
            :open="submitConfirmation"
            @update:open="submitConfirmation = $event"
            :onConfirm="submit"
            title="Are you sure you want to submit?"
            description="This action will set the reservation as confirmed and reserved all selected beds for each guests."
            confirm-label="Confirm"
        />
    </div>
</template>
