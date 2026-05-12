<?php

namespace App\Repositories\Interfaces;

use App\Models\Mode;

interface IModeRepository extends IRepository
{
    public function updateMode(Mode $mode, array $data): bool;

    public function deleteMode(Mode $mode): bool;
}
