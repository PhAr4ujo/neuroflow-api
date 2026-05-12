<?php

namespace App\Repositories;

use App\Models\Mode;
use App\Repositories\Interfaces\IModeRepository;

class ModeRepository extends Repository implements IModeRepository
{
    public function model(): string
    {
        return Mode::class;
    }

    public function updateMode(Mode $mode, array $data): bool
    {
        return $mode->update($data);
    }

    public function deleteMode(Mode $mode): bool
    {
        return (bool) $mode->delete();
    }
}
