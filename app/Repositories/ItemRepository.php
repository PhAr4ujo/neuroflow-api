<?php

namespace App\Repositories;

use App\Models\Item;
use App\Repositories\Interfaces\IItemRepository;

class ItemRepository extends Repository implements IItemRepository
{
    public function model(): string
    {
        return Item::class;
    }
}
