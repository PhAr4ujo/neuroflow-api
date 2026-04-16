<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\IUserRepository;

class UserRepository extends Repository implements IUserRepository
{
    public function model()
    {
        return User::class;
    }
}
