<?php

use App\Models\Office;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationSubmissionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//* Guests
Route::get('/', function () {
    $offices = Office::all();
    $host_office = Office::findOrFail(1); //TODO: make this dyamic

    return Inertia::render('LandingPage', [
        'canLogin' => Route::has('login'),
        'offices' => $offices,
        'hostOffice' => $host_office
    ]);
});

//! temporay
Route::post('/reservation', [ReservationSubmissionController::class, 'create'])->name('reservation.create');


//* Admins
Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');
});


require __DIR__ . '/room.php';
require __DIR__ . '/auth.php';
