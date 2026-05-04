<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Services\Interfaces\IItemService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ItemController extends Controller
{
    public function __construct(
        private readonly IItemService $itemService,
    ) {}

    /**
     * List the available items.
     */
    public function index(): AnonymousResourceCollection
    {
        return ItemResource::collection($this->itemService->getAll());
    }

    /**
     * Show an item.
     */
    public function show(Item $item): ItemResource
    {
        return new ItemResource($item);
    }
}
