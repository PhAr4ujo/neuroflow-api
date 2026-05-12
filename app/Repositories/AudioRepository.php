<?php

namespace App\Repositories;

use App\Models\Audio;
use App\Repositories\Interfaces\IAudioRepository;
use Illuminate\Database\Eloquent\Collection;

class AudioRepository extends Repository implements IAudioRepository
{
    public function model(): string
    {
        return Audio::class;
    }

    /**
     * @return Collection<int, Audio>
     */
    public function getAllWithMode(): Collection
    {
        return Audio::query()->with('mode')->get();
    }

    /**
     * @return Collection<int, Audio>
     */
    public function getByMode(int $modeId): Collection
    {
        return Audio::query()
            ->with('mode')
            ->where('mode_id', $modeId)
            ->get();
    }
}
