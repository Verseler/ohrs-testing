<?php

namespace App\Jobs;

use App\Mail\ReservationCodeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendReservationCodeEmail implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public function __construct(
    private string $email,
    private array $details
  ) {
  }

  public function handle(): void
  {
    Mail::to($this->email)->send(new ReservationCodeMail($this->details));
  }
}
