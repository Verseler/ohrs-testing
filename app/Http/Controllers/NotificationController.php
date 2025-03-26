<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function list()
    {
        return Inertia::render('Admin/Notification/NotificationList', [
            'notifications' => Auth::user()->notifications()->paginate(20),
            'unreadCount' => Auth::user()->unreadNotifications()->count()
        ]);
    }

    public function markAsRead($id)
    {
        Auth::user()->notifications()->where('id', $id)->update(['read_at' => now()]);
        return back();
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back();
    }
}
