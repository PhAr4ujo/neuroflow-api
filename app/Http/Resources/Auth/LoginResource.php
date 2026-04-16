<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\ApiResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class LoginResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => $this['message'],
            'access_token' => $this['access_token'],
            'token_type' => $this['token_type'],
            'user' => UserResource::make($this['user']),
        ];
    }
}
