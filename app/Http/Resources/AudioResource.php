<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class AudioResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'url' => URL::temporarySignedRoute('audios.stream', now()->addHour(), ['audio' => $this->id]),
            'mode_id' => $this->mode_id,
            'mode' => ModeResource::make($this->whenLoaded('mode')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
