<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\ApiResource;
use Illuminate\Http\Request;

class PayloadResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => $this['message'],
            'data' => $this['data'],
        ];
    }
}
