<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Sanctum\NewAccessToken;

interface IUserRepository extends IRepository
{
    public function getAllWithProfile(): Collection;

    public function findByEmail(string $email): ?User;

    public function createAccessToken(User $user, string $tokenName): NewAccessToken;

    public function deleteAccessTokens(User $user): void;

    public function deleteCurrentAccessToken(User $user): void;

    public function markEmailAsVerified(User $user): bool;

    public function updatePassword(User $user, string $password): bool;
}
