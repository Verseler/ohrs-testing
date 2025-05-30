<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReservationNotification extends Notification
{
    use Queueable;

    public $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function databaseType(object $notifiable): string
    {
        return 'new-reservation';
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $bookedBy = $this->reservation->first_name . ' ' . $this->reservation->last_name;
        $reservationCode = $this->reservation->code;

        return [
            'message' => "A new reservation [$reservationCode] has been submitted by $bookedBy.",
        ];
    }
}
