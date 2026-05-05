<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;

class ItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->profile_id !== null;
    }

    public function view(User $user, Item $item): bool
    {
        if ($user->profile_id === null) {
            return false;
        }

        return $item->profiles()
            ->whereKey($user->profile_id)
            ->exists();
    }
}
