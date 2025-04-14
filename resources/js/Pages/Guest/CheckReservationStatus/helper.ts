import { ReservationStatus } from "@/Pages/Admin/Reservation/reservation.types";
import { CheckCircleIcon, ClockIcon, XCircleIcon } from "lucide-vue-next";

export function getStatusConfig(status: ReservationStatus) {
    if (!status) return null;

    const configs: Record<
        ReservationStatus,
        {
            icon: any;
            color: string;
            borderColor: string;
            title: string;
            description: string;
        }
    > = {
        pending: {
            icon: ClockIcon,
            color: "bg-yellow-100 text-yellow-800",
            borderColor: "border-yellow-200",
            title: "Pending",
            description:
                "Your reservation is waiting for approval. Please check back for updates.",
        },
        confirmed: {
            icon: CheckCircleIcon,
            color: "bg-green-100 text-green-800",
            borderColor: "border-green-200",
            title: "Confirmed",
            description:
                "Your reservation is confirmed. We look forward to your stay.",
        },
        checked_in: {
            icon: CheckCircleIcon,
            color: "bg-blue-100 text-blue-800",
            borderColor: "border-blue-200",
            title: "Checked In",
            description:
                "Your stay has commenced. We hope you enjoy your time with us.",
        },
        checked_out: {
            icon: CheckCircleIcon,
            color: "bg-blue-100 text-blue-800",
            borderColor: "border-blue-200",
            title: "Checked Out",
            description:
                "Your stay has concluded. We appreciate you choosing our hostel.",
        },
        canceled: {
            icon: XCircleIcon,
            color: "bg-red-100 text-red-800",
            borderColor: "border-red-200",
            title: "Canceled",
            description: "This reservation has been canceled.",
        },
    };

    return configs[status];
}


