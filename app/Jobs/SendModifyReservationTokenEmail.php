<?php

namespace App\Jobs;

use App\Mail\ModifyReservationTokenMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendModifyReservationTokenEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $email,
        private array $details
    ) {
    }

    public function handle(): void
    {
        Mail::to($this->email)->send(new ModifyReservationTokenMail($this->details));
    }
}
