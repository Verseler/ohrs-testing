<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $user)
    {
        return $user->isSystemAdmin();
    }

    public function update(User $user)
    {
        return $user->isSystemAdmin();
    }

    public function create(User $user)
    {
        return $user->isSystemAdmin();
    }

    public function delete(User $user)
    {
        return $user->isSystemAdmin();
    }

    public function changePassword(User $user)
    {
        return $user->isSystemAdmin();
    }
}
