<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface IAudioRepository extends IRepository
{
    public function getAllWithMode(): Collection;

    public function getByMode(int $modeId): Collection;
}
