<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Interfaces\IUserService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly IUserService $userService,
    ) {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * List all users.
     */
    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection($this->userService->getAllUsers());
    }

    /**
     * Create a user.
     */
    public function store(StoreUserRequest $request): UserResource
    {
        $user = $this->userService->createUser($request->validated());

        return new UserResource($user, Response::HTTP_CREATED);
    }

    /**
     * Show a user.
     */
    public function show(User $user): UserResource
    {
        $user->loadMissing('profile');

        return new UserResource($user);
    }

    /**
     * Update a user.
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $user = $this->userService->updateUser($user, $request->validated());

        return new UserResource($user);
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user): HttpResponse
    {
        $this->userService->deleteUser($user);

        return response()->noContent();
    }
}
