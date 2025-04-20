<?php

namespace App\Jobs;

use App\Mail\RemainingBalanceMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendRemainingBalanceMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function __construct(
        private string $email,
        private array $details
    ) {
    }

    public function handle(): void
    {
        //send email after 2 days
        Mail::to($this->email)->later(now()->addDays(2), new RemainingBalanceMail($this->details));
    }
}
