<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserPasswordController extends Controller
{
    public function changePassForm(int $id)
    {
        $user = User::findOrFail($id);

        return Inertia::render('Admin/User/ChangePasswordUser', [
            'user' => $user,
        ]);
    }

    public function changePass(Request $request)
    {
        $user = User::findOrFail($request->id);

        $validated = $request->validate([
            'new_password' => ['required', Rules\Password::defaults()],
            'confirm_password' => ['required', 'same:new_password']
        ]);

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return to_route('user.list')->with('success', 'Password changed successfully.');
    }
}
