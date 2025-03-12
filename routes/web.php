<?php

use App\Http\Controllers\OfficeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationSubmissionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//* All
Route::get('/', function () {
return Inertia::render('LandingPage');
});


//* Guest Reservation
Route::middleware('guest')->group(function () {
    Route::get('/reservation', [ReservationSubmissionController::class, 'form'])->name('reservation.form');
    Route::post('/reservation', [ReservationSubmissionController::class, 'create'])->name('reservation.create');
    Route::get('/reservation/confirmation', [ReservationSubmissionController::class, 'confirmation'])->name('reservation.confirmation');
});

//* Admin Reservation Management
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/reservations', [ReservationController::class, 'list'])->name('reservation.list');
    Route::get('/reservations/{id}', [ReservationController::class, 'show'])->name('reservation.show');
    Route::get('/reservations/form/{id}', [ReservationController::class, 'editForm'])->name('reservation.editForm');
    Route::put('/reservations/edit/{reservation}', [ReservationController::class, 'edit'])->name('reservation.edit');
    Route::get('/reservations/payment/receipt', [PaymentController::class, 'paymentReceipt'])->name('reservation.paymentReceipt');
    Route::get('/reservations/payment/{id}', [PaymentController::class, 'paymentForm'])->name('reservation.paymentForm');
    Route::post('/reservations/payment', [PaymentController::class, 'payment'])->name('reservation.payment');
   
});

//* Admin Room Management
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/rooms', [RoomController::class, 'list'])->name('room.list');
    Route::inertia('/rooms/create', 'RoomManagement/CreateRoom')->name('room.createForm');
    Route::post('/rooms/create', [RoomController::class, 'create'])->name('room.create');
    Route::get('/rooms/edit/{id}', [RoomController::class, 'editForm'])->name('room.editForm');
    Route::put('/rooms/edit/{room}', [RoomController::class, 'edit'])->name('room.edit');
    Route::delete('/rooms/{id}', [RoomController::class, 'delete'])->name('room.delete');
});


//* Admin Office Management
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/offices', [OfficeController::class, 'list'])->name('office.list');
    Route::get('/offices/form/{id?}', [OfficeController::class, 'upsertForm'])->name('office.upsertForm');
    Route::post('/offices/upsert/{id?}', [OfficeController::class, 'upsert'])->name('office.upsert');
    Route::delete('/offices/{id}', [OfficeController::class, 'delete'])->name('office.delete');
});


//* Admins
Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__ . '/auth.php';
