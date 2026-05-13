<?php

namespace App\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface IAudioRepository extends IRepository
{
    public function getAllWithMode(): Collection;

    public function paginateWithMode(int $paginationAmount): LengthAwarePaginator;

    public function getByMode(int $modeId): Collection;

    public function paginateByMode(int $modeId, int $paginationAmount): LengthAwarePaginator;
}
