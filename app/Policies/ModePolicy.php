<?php

namespace App\Policies;

use App\Models\Mode;
use App\Models\User;

class ModePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Mode $mode): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Mode $mode): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Mode $mode): bool
    {
        return $user->isAdmin();
    }
}
