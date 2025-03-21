import { Gender } from '@/Pages/Guest/guest.types';

export type RoomStatus = 'available' | 'fully_occupied' | 'maintenance';

export type SortBy = 'beds_count' | 'available_beds' & keyof Pick<Room, 'eligible_gender' | 'name'>

export type RoomFilters = {
    check_in_date?: Date | undefined;
    check_out_date?: Date | undefined;
    eligible_gender?: RoomStatus | null;
    sort_by?: SortBy | null;
    sort_order?: 'asc' | 'desc';
}

export type Room = {
    id: number;
    name: string;
    eligible_gender: Gender;
}

export type BedStatus = 'available' | 'reserved' | 'occupied' | 'maintenance';

export type Bed = {
    id: number | string;
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
