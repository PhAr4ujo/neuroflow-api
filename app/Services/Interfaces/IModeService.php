<?php

namespace App\Services\Interfaces;

use App\Models\Mode;

interface IModeService extends IService
{
    public function createMode(array $data): Mode;

    public function updateMode(Mode $mode, array $data): Mode;

    public function deleteMode(Mode $mode): bool;
}
