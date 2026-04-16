<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\ApiResource;
use Illuminate\Http\Request;

class MessageResource extends ApiResource
{
    /**
     * @return array<string, string>
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => $this['message'],
        ];
    }
}
