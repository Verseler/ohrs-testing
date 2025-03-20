
export type PaymentOption = "full" | "custom";

export type PaymentMethod = "cash" | "online";

export type Payment = {
    id: number;
    amount: number;
    payment_method: PaymentMethod;
    or_number: string;
    or_date: Date;
    reservation_id: number;
    transaction_id: string;
    created_at: string;
    updated_at: string;
}
