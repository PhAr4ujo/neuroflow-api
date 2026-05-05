<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\IUserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;

class UserRepository extends Repository implements IUserRepository
{
    public function model(): string
    {
        return User::class;
    }

    public function getAllWithProfile(): Collection
    {
        return $this->model->newQuery()
            ->with('profile')
            ->get();
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->newQuery()->where('email', $email)->first();
    }

    public function createAccessToken(User $user, string $tokenName): NewAccessToken
    {
        return $user->createToken($tokenName);
    }

    public function deleteAccessTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    public function deleteCurrentAccessToken(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function markEmailAsVerified(User $user): bool
    {
        return $user->markEmailAsVerified();
    }

    public function updatePassword(User $user, string $password): bool
    {
        return $user->forceFill([
            'password' => $password,
            'remember_token' => Str::random(60),
        ])->save();
    }

    public function updateUser(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function deleteUser(User $user): bool
    {
        return (bool) $user->delete();
    }
}
