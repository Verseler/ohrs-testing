<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ModifyReservationTokenMail extends Mailable
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
        ->with(['content' => $this->data['content']])
        ->view('emails.modify_reservation_code');
    }
}
