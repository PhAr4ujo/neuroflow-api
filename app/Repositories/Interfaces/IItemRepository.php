<?php

namespace App\Repositories\Interfaces;

use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;

interface IItemRepository extends IRepository
{
    /**
     * @return Collection<int, Item>
     */
    public function getByProfile(int $profileId): Collection;

    public function findByProfile(int $id, int $profileId): ?Item;
}
