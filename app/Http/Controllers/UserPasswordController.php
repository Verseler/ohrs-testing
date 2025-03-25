<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserPasswordController extends Controller
{
    use AuthorizesRequests;

    public function changePassForm(int $id)
    {
        $this->authorize('changePassword', User::class);

        $user = User::findOrFail($id);

        return Inertia::render('Admin/User/ChangePasswordUser', [
            'user' => $user,
        ]);
    }

    public function changePass(Request $request)
    {
        $this->authorize('changePassword', User::class);
        
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
