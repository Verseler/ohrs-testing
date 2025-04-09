<script setup lang="ts">
import { GuestAssignment, ReservationWithBeds } from "@/Pages/Admin/Reservation/reservation.types";
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
import { Check, RefreshCw } from "lucide-vue-next";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { InputError } from "@/Components/ui/input";
import { roomScheduledEligibleGender } from "@/Pages/Admin/WaitingList/helpers";
import { formatDateString } from "@/lib/utils";

type AssignGuestListProps = {
    reservation: ReservationWithBeds & { guestAssignment: GuestAssignment[] };
    availableBeds: Record<number, Bed[]>;
};

const { reservation, availableBeds: defaultAvailableBeds } =
    defineProps<AssignGuestListProps>();

const form = useForm({
    reservation_id: reservation.id,
    guests: reservation.guestAssignment,
});

const assignedBedIds = ref<number[]>([]);
const modifiedBeds = ref<Record<number, Bed[]>>({});

// Initialize modified beds with a copy of the originals
Object.keys(defaultAvailableBeds).forEach(key => {
    const guestId = Number(key);
    modifiedBeds.value[guestId] = JSON.parse(JSON.stringify(defaultAvailableBeds[guestId] || []));
});

// Update assigned beds when form changes
const updateAssignedBeds = () => {
    assignedBedIds.value = form.guests
        .filter((guest) => guest.bed_id)
        .map((guest) => guest.bed_id!);
};

// Watch for changes in guest bed assignments
watch(form.guests, updateAssignedBeds, { deep: true });

const getAvailableBeds = (guestId: number) => {
    // Get beds for this guest, filtering out ones already assigned to other guests
    return (modifiedBeds.value[guestId] || [])
        .filter(bed => !assignedBedIds.value.includes(bed.id) ||
            form.guests.find(g => g.id === guestId)?.bed_id === bed.id);
};

const filterBedsByGender = (beds: Bed[], gender: Gender) => {
    return beds
        .filter(
            (bed) =>
                bed.room.eligible_gender === "any" ||
                bed.room.eligible_gender === gender
        )
        .sort((a, b) => {
            if (a.room.eligible_gender === b.room.eligible_gender) {
                return b.id - a.id;
            }
            return b.room.eligible_gender.localeCompare(a.room.eligible_gender);
        });
};

const getBedName = (bedId: number, guestId: number) => {
    const beds = modifiedBeds.value[guestId] || [];
    const bed = beds.find((b) => b.id === bedId);
    return bed ? `${bed.room.name} - ${bed.name}` : "";
};

// Get formatted bed name for display
const onBedSelect = (guestId: number, bedId: number) => {
    // First, clear previous selection if any
    const previousGuest = form.guests.find(g => g.id === guestId);

    if (previousGuest) {
        previousGuest.bed_id = bedId;
    }

    // If selecting a bed
    if (bedId) {
        // Find the selected bed
        Object.keys(modifiedBeds.value).forEach(key => {
            const currentGuestId = Number(key);
            const beds = modifiedBeds.value[currentGuestId];

            const selectedBed = beds.find(b => b.id === bedId);
            if (selectedBed && selectedBed.room.eligible_gender === "any") {
                // Update all beds in this room to match the guest's gender
                const guestGender = form.guests.find(g => g.id === guestId)?.gender;
                if (guestGender) {
                    const roomId = selectedBed.room.id;

                    // Update for all guests
                    Object.keys(modifiedBeds.value).forEach(guestKey => {
                        modifiedBeds.value[Number(guestKey)].forEach(bed => {
                            if (bed.room.id === roomId) {
                                bed.room.eligible_gender = guestGender;
                            }
                        });
                    });
                }
            }
        });
    }

    // Update assigned beds list
    updateAssignedBeds();
};

const submitConfirmation = ref(false);

function showSubmitConfirmation() {
    submitConfirmation.value = true;
}

function submit() {
    form.post(route("reservation.assignBeds"));
}
</script>

<template>
    <div class="space-y-2">
        <div class="flex justify-between items-center">
            <p class="text-2xl font-bold text-primary-600">Guests</p>
            <LinkButton
                variant="outline"
                :href="
                    route('reservation.assignBedsForm', { id: reservation.id })
                "
                class="text-red-500 border-red-500 hover:bg-red-50 hover:text-red-600"
            >
                <RefreshCw />Reset
            </LinkButton>
        </div>

        <!-- List Items -->
        <div
            v-for="(guest, index) in form.guests"
            :key="guest.id"
            class="grid grid-cols-8 gap-x-2 items-center"
        >
            <div class="flex col-span-5 gap-x-2 justify-between">
                <p
                    class="flex flex-1 items-center px-2 w-full capitalize rounded-md border"
                >
                    {{ guest.name }}
                </p>
                <p class="flex items-center px-3 text-sm rounded-md border">
                    <span class='text-blue-500'>{{ formatDateString(guest.check_in_date) }}</span>
                     <span class='mx-1'>to</span>
                     <span class='text-red-500'>{{ formatDateString(guest.check_out_date) }}</span>
                </p>

                <GenderBadge
                    class="h-12 text-sm min-w-20"
                    :gender="guest.gender"
                />
            </div>

            <Select
                v-bind:modelValue="guest.bed_id"
                @update:modelValue="(value) => onBedSelect(guest.id, Number(value))"
            >
                <SelectTrigger
                    class="col-span-3 h-12 rounded-sm shadow-none border-primary-700"
                    :invalid="(form.errors as any)[`guests.${index}.bed_id`]"
                >
                    <SelectValue placeholder="Select bed">{{
                        guest.bed_id ? getBedName(guest.bed_id, guest.id) : "Select bed"
                    }}</SelectValue>
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectItem
                            v-for="bed in filterBedsByGender(
                                getAvailableBeds(guest.id),
                                guest.gender
                            )"
                            :key="bed.id"
                            :value="bed.id"
                        >
                            <div class="flex justify-between items-center">
                                <span class="block text-sm">
                                    {{ `${bed.room.name} - ${bed.name}` }}
                                </span>

                                <GenderBadge
                                    v-if="roomScheduledEligibleGender(bed)"
                                    :gender="roomScheduledEligibleGender(bed)"
                                />
                                <GenderBadge
                                    v-else
                                    :gender="bed.room.eligible_gender"
                                />
                            </div>
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>

            <InputError
                class="col-start-6 col-end-9 mt-1"
                v-if="(form.errors as Record<string, string>)[`guests.${index}.bed_id`]"
            >
                {{ (form.errors as Record<string, string>)[`guests.${index}.bed_id`] }}
            </InputError>
        </div>

        <Button
            @click="showSubmitConfirmation"
            type="button"
            class="w-full h-12 text-base border border-primary-600"
        >
            <Check class="mr-1" />
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
