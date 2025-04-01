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
import { watch, ref, computed } from "vue";
import { Gender } from "@/Pages/Guest/guest.types";
import GenderBadge from "@/Components/GenderBadge.vue";
import LinkButton from "@/Components/LinkButton.vue";
import { Check, RefreshCw } from "lucide-vue-next";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import { InputError } from "@/Components/ui/input";
import { getLengthOfStay } from "@/lib/utils";
import { roomScheduledEligibleGender } from "@/Pages/Admin/WaitingList/helpers";

type AssignGuestListProps = {
    reservation: ReservationWithBeds;
    availableBeds: Bed[];
};

const { reservation, availableBeds: defaultAvailableBeds } =
    defineProps<AssignGuestListProps>();

/**
 * Default guest list derived from the reservation's guests.
 * Each guest is initialized with their ID, name, gender, and no assigned bed.
 */
const DEFAULT_GUESTS = reservation.guests.map((guest) => ({
    id: guest.id,
    name: `${guest.first_name} ${guest.last_name}`,
    gender: guest.gender as Gender,
    bed_id: null as number | null,
}));

/**
 * Default mapping of room IDs to their eligible genders.
 * If no specific schedule exists, the default eligible gender is used.
 */
const DEFAULT_ROOM_ELIGIBLE_GENDERS = new Map(
    defaultAvailableBeds.map((bed) => [
        bed.room.id,
        bed.room.eligible_gender_schedules?.[0]?.eligible_gender ||
            bed.room.eligible_gender,
    ])
);

const form = useForm({
    reservation_id: reservation.id,
    guests: DEFAULT_GUESTS,
});

const assignedBedIds = ref<number[]>([]);
const roomEligibleGenders = ref(DEFAULT_ROOM_ELIGIBLE_GENDERS);

/**
 * Computed property to dynamically update available beds.
 * Ensures that room eligible genders are updated based on current state.
 */
const availableBeds = computed(() =>
    defaultAvailableBeds.map((bed) => ({
        ...bed,
        room: {
            ...bed.room,
            eligible_gender:
                roomEligibleGenders.value.get(bed.room.id) ||
                bed.room.eligible_gender,
        },
    }))
);

/**
 * Updates the list of assigned bed IDs based on the current form state.
 */
const updateAssignedBeds = () => {
    assignedBedIds.value = form.guests
        .filter((guest) => guest.bed_id)
        .map((guest) => guest.bed_id!);
};

/**
 * Resets the eligible genders for rooms that do not have assigned beds.
 */
const resetRoomGenders = () => {
    const assignedRooms = new Set(
        assignedBedIds.value.map(
            (bedId) =>
                availableBeds.value.find((bed) => bed.id === bedId)?.room.id
        )
    );

    roomEligibleGenders.value.forEach((defaultGender, roomId) => {
        if (!assignedRooms.has(roomId)) {
            roomEligibleGenders.value.set(
                roomId,
                defaultAvailableBeds.find((bed) => bed.room.id === roomId)?.room
                    .eligible_gender || "any"
            );
        }
    });
};

/**
 * Watches changes to the form's guests and updates assigned beds and room genders.
 */
watch(
    form.guests,
    () => {
        updateAssignedBeds();
        resetRoomGenders();
    },
    { deep: true }
);

/**
 * Handles bed selection for a guest.
 * Updates the guest's assigned bed and adjusts the room's eligible gender if necessary.
 */
const onBedSelect = (guestId: number, bedId: number) => {
    const guest = form.guests.find((g) => g.id === guestId);
    if (guest) {
        guest.bed_id = bedId;
        const bed = availableBeds.value.find((b) => b.id === bedId);
        if (bed && bed.room.eligible_gender === "any") {
            roomEligibleGenders.value.set(bed.room.id, guest.gender);
        }
    }
};

const getAvailableBeds = () =>
    availableBeds.value.filter((bed) => !assignedBedIds.value.includes(bed.id));

const filterBedsByGender = (beds: Bed[], gender: Gender) =>
    beds
        .filter(
            (bed) =>
                bed.room.eligible_gender === "any" ||
                bed.room.eligible_gender === gender
        )
        .sort((a, b) => b.id - a.id);

const getBedName = (bedId: number) => {
    const bed = availableBeds.value.find((b) => b.id === bedId);
    return bed ? `${bed.room.name} - ${bed.name}` : "";
};

const lengthOfStay = computed(() =>
    getLengthOfStay(reservation.check_in_date, reservation.check_out_date)
);

const totalPrice = computed(() => {
    const totalBedPrice = form.guests.reduce((price, currentGuest) => {
        return (
            price + (currentGuest.bed_id ? getBedPrice(currentGuest.bed_id) : 0)
        );
    }, 0);

    return totalBedPrice * lengthOfStay.value;
});

function getBedPrice(id: number): number {
    const bed = availableBeds.value.find((bed) => bed.id === id);

    if (!bed) return 0;

    return bed.price;
}

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
        <div class="flex items-center justify-between">
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
        <div
            v-for="(guest, index) in form.guests"
            :key="guest.id"
            class="grid items-center grid-cols-8 gap-x-2"
        >
            <div class="flex justify-between col-span-5 gap-x-2">
                <p
                    class="flex items-center flex-1 w-full px-2 capitalize border rounded-md"
                >
                    {{ guest.name }}
                </p>
                <GenderBadge
                    class="h-12 text-sm min-w-20"
                    :gender="guest.gender"
                />
            </div>

            <Select
                v-bind:modelValue="guest.bed_id"
                @update:modelValue="
                    (value) => onBedSelect(guest.id, Number(value))
                "
            >
                <SelectTrigger
                    class="h-12 col-span-3 rounded-sm shadow-none border-primary-700"
                    :invalid="(form.errors as any)[`guests.${index}.bed_id`]"
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
                class="col-start-6 col-end-10 mt-1"
                v-if="(form.errors as any)[`guests.${index}.bed_id`]"
            >
                {{ (form.errors as any)[`guests.${index}.bed_id`] }}
            </InputError>
        </div>

        <p class="mt-2 text-sm text-end text-neutral-600">
            Total Price: ₱{{ totalPrice }}
        </p>

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
