<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModifyReservationController;
use App\Http\Controllers\UpdateReservationCheckoutController;
use App\Http\Controllers\GenerateReportController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReservationAssignBedsController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationProcessController;
use App\Http\Controllers\ReservationStatusController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPasswordController;
use App\Models\Office;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $hostels = Office::where('has_hostel', true)->get();

    return Inertia::render('LandingPage', [
        'hostels' => $hostels
    ]);
})->name('landingPage');
Route::get('/rooms/available-beds', [RoomController::class, 'getAvailableRooms'])->name('room.checkAvailableBeds');

//* Guest Reservation
Route::get('/reservation', [ReservationProcessController::class, 'form'])->name('reservation.form');
Route::post('/reservation', [ReservationProcessController::class, 'create'])->name('reservation.create');
Route::get('/reservation/confirmation', [ReservationProcessController::class, 'confirmation'])->name('reservation.confirmation');
Route::get('/reservation/status/form', [ReservationStatusController::class, 'checkStatusForm'])->name('reservation.checkStatusForm');
Route::get('/reservation/status/{code}', [ReservationStatusController::class, 'checkStatus'])->name('reservation.checkStatus');
Route::get('reservation/search/{search}/{hostel_id}', [ReservationStatusController::class, 'search'])->name('reservation.search');

//* Guest Modify Reservation    
Route::post('/reservation/request-modify', [ModifyReservationController::class, 'requestModify'])->name('reservation.requestModify');
Route::get('/reservation/verify-edit/{reservation_id}/{token}', [ModifyReservationController::class, 'verifyEdit'])->name('reservation.verifyEdit');
Route::post('/reservation/request-cancel', [ModifyReservationController::class, 'requestCancel'])->name('reservation.requestCancel');
Route::get('/reservation/verify-cancel/{reservation_id}/{token}', [ModifyReservationController::class, 'verifyCancel'])->name('reservation.verifyCancel');
Route::put('/reservation/edit', [ModifyReservationController::class, 'edit'])->name('reservation.edit');
Route::get('/reservation/otp', [OtpController::class, 'form'])->name('reservation.otpForm');
Route::post('/reservation/otp', [OtpController::class, 'verify'])->name('reservation.otpVerify');



//* Admin Reservation
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/reservations', [ReservationController::class, 'list'])->name('reservation.list');
    Route::get('/reservations/{id}/edit-checkout', [UpdateReservationCheckoutController::class, 'updateCheckoutForm'])->name('reservation.updateCheckoutForm');
    Route::get('/reservations/{id}/edit-bed-assignment', [ReservationAssignBedsController::class, 'editBedAssignmentForm'])->name('reservation.editBedAssignmentForm');
    Route::put('/reservations/edit-assign-bed', [ReservationAssignBedsController::class, 'editAssignBed'])->name('reservation.editAssignBed');
    Route::get('/reservations/{id}/edit-assign-bed', [ReservationAssignBedsController::class, 'editAssignBedForm'])->name('reservation.editAssignBedForm');
    Route::get('/reservations/{id}', [ReservationController::class, 'show'])->name('reservation.show');
});

//* Admin Reservation Status
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/reservations/edit-checkout', [UpdateReservationCheckoutController::class, 'updateCheckout'])->name('reservation.updateCheckout');
    Route::put('/reservations/edit-status', [ReservationStatusController::class, 'editAllStatus'])->name('reservation.editAllStatus');
    Route::put('/reservations/edit-status/guest', [ReservationStatusController::class, 'editStatus'])->name('reservation.editStatus');
    Route::get('/reservations/{id}/edit-status/guest', [ReservationStatusController::class, 'editStatusForm'])->name('reservation.editStatusForm');
    Route::get('/reservations/{id}/edit-status', [ReservationStatusController::class, 'editAllStatusForm'])->name('reservation.editAllStatusForm');
    Route::put('/reservations/{id}/cancel', [ReservationStatusController::class, 'cancel'])->name('reservation.cancel');
});

//* Admin Reservation Payment
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/reservations/payment', [PaymentController::class, 'payment'])->name('reservation.payment');
    Route::post('/reservations/pay-later', [PaymentController::class, 'payLater'])->name('reservation.payLater');
    Route::get('/reservations/payment/{id}/history', [PaymentController::class, 'paymentHistory'])->name('reservation.paymentHistory');
    Route::get('/reservations/payment/{id}', [PaymentController::class, 'paymentForm'])->name('reservation.paymentForm');
});

Route::middleware(['auth', 'verified', 'role:super_admin,system_admin'])->group(function () {
    Route::post('/reservations/payment/exempt', [PaymentController::class, 'exemptPayment'])->name('reservation.exemptPayment');
    Route::get('/reservations/payment/{id}/exempt', [PaymentController::class, 'exemptPaymentForm'])->name('reservation.exemptPaymentForm');
});

//* Waiting List Management
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/waiting-list', [ReservationController::class, 'waitingList'])->name('reservation.waitingList');
    Route::post('/waiting-list/assign-bed', [ReservationAssignBedsController::class, 'assignBeds'])->name('reservation.assignBeds');
    Route::get('/waiting-list/assign-bed/{id}', [ReservationAssignBedsController::class, 'assignBedsForm'])->name('reservation.assignBedsForm');
});

//* Admin Room Management
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/rooms', [RoomController::class, 'list'])->name('room.list');
    Route::inertia('/rooms/create', 'Admin/Room/CreateRoom')->name('room.createForm');
    Route::post('/rooms/create', [RoomController::class, 'create'])->name('room.create');
    Route::get('/rooms/edit/{id}', [RoomController::class, 'editForm'])->name('room.editForm');
    Route::put('/rooms/edit/{room}', [RoomController::class, 'edit'])->name('room.edit');
    Route::delete('/rooms/{id}', [RoomController::class, 'delete'])->name('room.delete');
});

//* Admin Guest Management
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/guests', [GuestController::class, 'list'])->name('guest.list');
});

//* Admin analytics and reports
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports', [GenerateReportController::class, 'list'])->name('report.list');
    Route::get('/reports/download/{selected_date}/{type}', [GenerateReportController::class, 'download'])->name('report.download');
    Route::get('/reports/print/{selected_date}/{type}', [GenerateReportController::class, 'print'])->name('report.print');
});

//* Admin Notifications
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'list'])->name('notification.list');
    Route::put('/notifications/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('notification.markAsRead');
    Route::put('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notification.markAllAsRead');
    Inertia::share('unreadNotificationCount', function () {
        return Auth::user()?->unreadNotifications()?->count();
    });
});

//* Office Management
Route::middleware(['auth', 'verified', 'role:system_admin'])->group(function () {
    Route::get('/offices', [OfficeController::class, 'list'])->name('office.list');
    Route::get('/offices/form/{id?}', [OfficeController::class, 'upsertForm'])->name('office.upsertForm');
    Route::post('/offices/upsert/{id?}', [OfficeController::class, 'upsert'])->name('office.upsert');
    Route::delete('/offices/{id}', [OfficeController::class, 'delete'])->name('office.delete');
});


//* User Management
Route::middleware(['auth', 'verified', 'role:system_admin'])->group(function () {
    Route::get('/users', [UserController::class, 'list'])->name('user.list');
    Route::get('/users/create', [UserController::class, 'createForm'])->name('user.createForm');
    Route::post('/users/create', [UserController::class, 'create'])->name('user.create');
    Route::put('/users/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::get('/users/edit/{id}', [UserController::class, 'editForm'])->name('user.editForm');
    Route::put('/users/change-password', [UserPasswordController::class, 'changePass'])->name('user.changePass');
    Route::get('/users/change-password/{id}', [UserPasswordController::class, 'changePassForm'])->name('user.changePassForm');
    Route::delete('/users/delete/{id}', [UserController::class, 'delete'])->name('user.delete');
});

require __DIR__ . '/auth.php';

