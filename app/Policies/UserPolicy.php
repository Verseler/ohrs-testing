<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $user)
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user)
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user)
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user)
    {
        return $user->isSuperAdmin();
    }

    public function changePassword(User $user)
    {
        return $user->isSuperAdmin();
    }
}
