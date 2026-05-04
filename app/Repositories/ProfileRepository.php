<?php

namespace App\Repositories;

use App\Models\Profile;
use App\Repositories\Interfaces\IProfileRepository;

class ProfileRepository extends Repository implements IProfileRepository
{
    public function model(): string
    {
        return Profile::class;
    }

    public function findBySlug(string $slug): ?Profile
    {
        return $this->model->newQuery()->where('slug', $slug)->first();
    }
}
