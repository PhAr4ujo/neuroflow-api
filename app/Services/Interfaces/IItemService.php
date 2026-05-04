<?php

namespace App\Services\Interfaces;

use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;

interface IItemService extends IService
{
    /**
     * @return Collection<int, Item>
     */
    public function getByProfile(int $profileId): Collection;

    public function findByProfile(int $id, int $profileId): ?Item;
}
