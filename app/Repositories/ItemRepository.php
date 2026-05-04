<?php

namespace App\Repositories;

use App\Models\Item;
use App\Repositories\Interfaces\IItemRepository;
use Illuminate\Database\Eloquent\Collection;

class ItemRepository extends Repository implements IItemRepository
{
    public function model(): string
    {
        return Item::class;
    }

    public function getByProfile(int $profileId): Collection
    {
        return $this->model->newQuery()
            ->whereHas('profiles', fn ($query) => $query->whereKey($profileId))
            ->get();
    }

    public function findByProfile(int $id, int $profileId): ?Item
    {
        return $this->model->newQuery()
            ->whereKey($id)
            ->whereHas('profiles', fn ($query) => $query->whereKey($profileId))
            ->first();
    }
}
