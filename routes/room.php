<?php
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/rooms', [RoomController::class, 'list'])->name('room.list');
    Route::inertia('/rooms/create', 'RoomManagement/CreateRoom')->name('room.createForm');
    Route::post('/rooms/create', [RoomController::class, 'create'])->name('room.create');
    Route::get('/rooms/edit/{id}', [RoomController::class, 'editForm'])->name('room.editForm');
    Route::put('/rooms/edit/{room}', [RoomController::class, 'edit'])->name('room.edit');
    Route::get('/rooms/{id}', [RoomController::class, 'show'])->name('room.show');
    Route::delete('/rooms/{id}', [RoomController::class, 'delete'])->name('room.delete');
});
