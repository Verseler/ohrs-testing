import type { Guest } from '@/Pages/Guest/guest.types';
import type { Bed, BedWithGuest, Room } from '@/Pages/Admin/Room/room.types';
import type { Office } from '@/Pages/Admin/Office/office.types';

export type ReservationStatus = 'pending' | 'confirmed' | 'canceled' | 'checked_in' | 'checked_out';

export type PaymentType = 'full_payment' | 'pay_later'

export type Reservation = {
    id: number;
    reservation_code: string;
    check_in_date: string;
    check_out_date: string;
    daily_rate: number;
    total_billings: number;
    remaining_balance: number;
    status: ReservationStatus;
    payment_type: PaymentType;
    first_name: string;
    middle_initial: string | null;
    last_name: string;
    phone: string;
    email: string | null;
    id_type: string;
    employee_id: string;
    purpose_of_stay: string | null;
    guests: Guest[];
    extended_reservations: ExtendedReservation[]
    guest_office_id: number;
    hostel_office_id: number;
    reserved_beds: Bed[];
    created_at: string;
    updated_at: string;
}

export type ReservationFilters = {
    status: ReservationStatus | null;
    balance: 'paid' | 'has_balance' | null;
    search: string | undefined;
    sort_by: string | null;
    sort_order: 'asc' | 'desc' | null;
}

export type WaitingListFilers = Omit<ReservationFilters, 'status' | 'balance'>;

export type ReservationWithBeds = Omit<
    Reservation,
    "guest_office_id" | "host_office_id"
> & {
    guest_office: Office;
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
