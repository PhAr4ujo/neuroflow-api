<?php

namespace App\Services\Interfaces;

use App\Models\Mode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface IModeService extends IService
{
    public function getAllModes(): Collection;

    public function paginateModes(int $paginationAmount = 15): LengthAwarePaginator;

    public function createMode(array $data): Mode;

    public function updateMode(Mode $mode, array $data): Mode;

    public function deleteMode(Mode $mode): bool;
}
