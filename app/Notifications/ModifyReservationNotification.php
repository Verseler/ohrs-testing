<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ModifyReservationNotification extends Notification
{
    use Queueable;

    public $action;
    public $reservation;

    public function __construct($action, $reservation)
    {
        $this->action = $action;
        $this->reservation = $reservation;
    }

    public function databaseType(object $notifiable): string
    {
        return 'modify-reservation';
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {

        return [
            'message' => "Reservation [{$this->reservation->code}] booked by {$this->reservation->first_name} {$this->reservation->last_name} has been {$this->action}."
        ];
    }
}
