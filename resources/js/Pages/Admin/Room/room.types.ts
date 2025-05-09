import type { Gender, Guest } from '@/Pages/Guest/guest.types';
import type { StayDetails } from '@/Pages/Admin/Reservation/reservation.types';

export type RoomStatus = 'available' | 'fully_occupied' | 'maintenance';

export type SortBy = 'beds_count' | 'available_beds' & keyof Pick<Room, 'eligible_gender' | 'name'>

export type RoomFilters = {
    check_in_date?: Date | undefined;
    check_out_date?: Date | undefined;
    eligible_gender?: RoomStatus | null;
    sort_by?: SortBy | null;
    sort_order?: 'asc' | 'desc';
}

export type EligibleGenderSchedule = {
    id: number;
    start_date: Date | string;
    end_date: Date | string;
    eligible_gender: Gender;
    room_id: number;
}

export type Room = {
    id: number;
    name: string;
    eligible_gender: Gender;
    eligible_gender_schedules: EligibleGenderSchedule[];
}

export type BedStatus = 'available' | 'reserved' | 'occupied' | 'maintenance';

export type Bed = {
    id: number;
    name: string;
    price: number;
    room_id: number;
    room: Room;
}

export type BedWithRoom = Bed & {
    room: Room;
}

export type RoomWithBedCounts = Room & {
    beds?: Bed[] | null;
    beds_count?: number | null;
    available_beds?: number | null;
    occupied_beds?: number | null;
    under_maintenance_beds?: number | null;
};

export type RoomWithBed = Room & {
    beds: Bed[]
}


export type BedWithGuest = StayDetails & {
    bed: Bed;
    guest: Guest;
}
