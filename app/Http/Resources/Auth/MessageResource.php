<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\ApiResource;
use Illuminate\Http\Request;

class MessageResource extends ApiResource
{
    public function __construct(
        private readonly string $message,
        int $status = 200,
    ) {
        parent::__construct(resource: null, status: $status);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => $this->message,
        ];
    }
}
