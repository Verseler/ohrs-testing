<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class OtpController extends Controller
{
    public function form() {
        return Inertia::render('Guest/ModifyReservation/OtpConfirmation', [
            'action' => session()->get("action"),
            'email' => session()->get("email"),
            'reservation_id' => session()->get("reservation_id"),
        ]);
    }
}

