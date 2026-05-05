<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Models\User;
use App\Services\Interfaces\IItemService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ItemController extends Controller
{
    public function __construct(
        private readonly IItemService $itemService,
    ) {}

    /**
     * List the items allowed for the authenticated user's profile.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Item::class);

        /** @var User $user */
        $user = $request->user();

        return ItemResource::collection($this->itemService->getByProfile($user->profile_id));
    }

    /**
     * Show an item allowed for the authenticated user's profile.
     */
    public function show(Item $item): ItemResource
    {
        $this->authorize('view', $item);

        return new ItemResource($item);
    }
}
