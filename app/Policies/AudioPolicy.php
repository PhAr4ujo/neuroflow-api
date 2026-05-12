<?php

namespace App\Policies;

use App\Models\Audio;
use App\Models\User;

class AudioPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Audio $audio): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Audio $audio): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Audio $audio): bool
    {
        return $user->isAdmin();
    }
}
