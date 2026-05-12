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
}
