import type { Gender } from "@/Pages/RoomManagement/room.types";

export type Guest = {
    id: number;
    display_name: string;
    gender: Gender;
    office_id: number;
    created_at: string;
    updated_at: string;
}

export type ReservationStatus = 'pending' | 'canceled' | 'checked_in' | 'checked_out';

export type Reservation = {
    id: number;
    reservation_code: string;
    check_in_date: string;
    check_out_date: string;
    total_billing: number;
    remaining_balance: number;
    total_guests: number;
    status: ReservationStatus;
    first_name: string;
    middle_initial: string | null;
    last_name: string;
    phone: string;
    email: string | null;
    employee_identification: string;
    purpose_of_stay: string | null;
    guests: Guest[];
    guest_office_id: number;
    host_office_id: number;
}

export type Filters = {
    status: ReservationStatus | null;
    balance: 'paid' | 'has_balance' | null;
    search: string | undefined;
    sort_by: string | null;
    sort_order: 'asc' | 'desc' | null;
}
