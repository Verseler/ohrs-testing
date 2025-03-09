<?php

use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('rooms/available', [RoomController::class, 'getAvailableRooms'])->name('rooms.available');
