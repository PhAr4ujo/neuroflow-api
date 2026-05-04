<?php

namespace App\Services;

use App\Models\Item;
use App\Repositories\Interfaces\IItemRepository;
use App\Services\Interfaces\IItemService;
use Illuminate\Database\Eloquent\Collection;

class ItemService extends Service implements IItemService
{
    public function __construct(
        private readonly IItemRepository $itemRepository,
    ) {
        parent::__construct($itemRepository);
    }

    public function getByProfile(int $profileId): Collection
    {
        return $this->itemRepository->getByProfile($profileId);
    }

    public function findByProfile(int $id, int $profileId): ?Item
    {
        return $this->itemRepository->findByProfile($id, $profileId);
    }
}
