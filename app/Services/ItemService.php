<?php

namespace App\Services;

use App\Repositories\Interfaces\IItemRepository;
use App\Services\Interfaces\IItemService;

class ItemService extends Service implements IItemService
{
    public function __construct(
        private readonly IItemRepository $itemRepository,
    ) {
        parent::__construct($itemRepository);
    }
}
