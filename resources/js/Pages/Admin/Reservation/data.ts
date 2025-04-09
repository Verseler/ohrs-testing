
export const data = {
    breadcrumbs: [
        { label: 'Reservation Management', href: null }
    ],
    filterStatus: [
        {value: 'confirmed', label: 'Confirmed'},
        {value: 'checked_in', label: 'Checked In'},
        {value: 'checked_out', label: 'Checked Out'},
        {value: 'canceled', label: 'Canceled'},
    ],
    filterPaymentType: [
        {value: 'full_payment', label: 'Full Payment'},
        {value: 'pay_later', label: 'Pay Later'},
    ],
    filterBalance: [
        {value: 'paid', label: 'Paid'},
        {value: 'has_balance', label: 'Has Balance'},
    ],
    sortBy: [
        { value: "code", label: "Reservation code" },
        { value: "first_name", label: "Booked by" },
        { value: "total_billings", label: "Total billings" },
        { value: "general_status", label: "Status" },
    ],
    tableHeads: [
        "Reservation Code",
        "Booked by",
        "Check in",
        "Check out",
        "Total Billings",
        "Remaining Balance",
        "Number of Guests",
        "Payment Type",
        "Status",
        ""
    ],
};
