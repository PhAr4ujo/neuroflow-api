<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Models\User;
use App\Services\Interfaces\IItemService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

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
        /** @var User $user */
        $user = $request->user();

        if (! $user->profile_id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return ItemResource::collection($this->itemService->getByProfile($user->profile_id));
    }

    /**
     * Show an item allowed for the authenticated user's profile.
     */
    public function show(Request $request, Item $item): ItemResource
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->profile_id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $item = $this->itemService->findByProfile($item->id, $user->profile_id);

        if (! $item) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return new ItemResource($item);
    }
}
