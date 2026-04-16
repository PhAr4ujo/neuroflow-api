<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\ApiResource;
use Illuminate\Http\Request;

class ResetPasswordTokenResource extends ApiResource
{
    public function __construct(
        private readonly string $message,
        private readonly string $token,
        private readonly ?string $email,
        int $status = 200,
    ) {
        parent::__construct(resource: null, status: $status);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => $this->message,
            'data' => [
                'token' => $this->token,
                'email' => $this->email,
            ],
        ];
    }
}
