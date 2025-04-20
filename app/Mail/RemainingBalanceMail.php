<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RemainingBalanceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }


    public function build()
    {
        return $this->subject($this->data['title'])
            ->with([
                'reservation_code' => $this->data['reservation_code'],
                'remaining_balance' => $this->data['remaining_balance'],
                'date' => $this->data['date'],
            ])
            ->view('emails.remaining_balance');
    }
}
