import type { Gender, Guest } from '@/Pages/Guest/guest.types';
import type { Bed, BedWithGuest, Room } from '@/Pages/Admin/Room/room.types';
import type { Office } from '@/Pages/Admin/Office/office.types';

export type ReservationStatus = 'pending' | 'confirmed' | 'canceled' | 'checked_in' | 'checked_out';

export type PaymentType = 'full_payment' | 'pay_later'

export type Reservation = {
    id: number;
    code: string;
    total_billings: number;
    remaining_balance: number;
    payment_type: PaymentType;
    first_name: string;
    middle_initial: string;
    last_name: string;
    phone: string;
    email: string;
    guests: Guest[];
    extended_reservations: ExtendedReservation[]
    hostel_office_id: number;
    hostel_office: Office;
    id_type: string;
    employee_id: string;
    reserved_beds: Bed[];
    purpose_of_stay: string;
    general_status: ReservationStatus;
    min_check_in_date?: string | null;
    max_check_out_date?: string | null;
    created_at: string;
    updated_at: string;
}

export type StayDetails = {
    id: number;
    check_in_date: string;
    check_out_date: string;
    individual_billings: number;
    is_exempted: boolean;
    status: ReservationStatus;
    bed_id: number;
    bed: Bed;
    reservation_id: number;
    reservation: Reservation;
    guest_id: number;
    guest: Guest;
    created_at: string;
    updated_at: string;
}

export type ReservationFilters = {
    general_status: ReservationStatus | null;
    balance: 'paid' | 'has_balance' | null;
    payment_type: PaymentType | null;
    search: string | undefined;
    sort_by: string | null;
    sort_order: 'asc' | 'desc' | null;
}

export type WaitingListFilers = Omit<ReservationFilters, 'status' | 'balance' | 'payment_type'>;

export type ReservationWithBeds = Omit<
    Reservation,
    "host_office_id"
> & {
    hostel_office: Office;
    beds: (Bed & { room: Room })[];
    reservedBeds: Bed[];
    reserved_beds_with_guests: BedWithGuest[]

};


export type ExtendedReservation = {
    id: number;
    check_in_date: string;
    old_check_out_date: string;
    new_check_out_date: string;
    days_extended: number;
    reservation_id: number;
    created_at: string;
    updated_at: string;
}


export type GuestAssignment = {
    id: number;
    name: string;
    check_in_date: string;
    check_out_date: string;
    gender: Gender;
    bed_id: number | null;
}


export type ReservationType = "solo" | "group"
