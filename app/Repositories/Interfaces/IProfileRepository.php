<?php

namespace App\Repositories\Interfaces;

use App\Models\Profile;

interface IProfileRepository extends IRepository
{
    public function findBySlug(string $slug): ?Profile;
}
