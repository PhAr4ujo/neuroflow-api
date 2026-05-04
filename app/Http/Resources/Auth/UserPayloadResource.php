<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\ApiResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserPayloadResource extends ApiResource
{
    public function __construct(
        private readonly string $message,
        private readonly User $user,
        int $status = 200,
    ) {
        parent::__construct(resource: $user, status: $status);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => $this->message,
            'data' => UserResource::make($this->user),
        ];
    }
}
