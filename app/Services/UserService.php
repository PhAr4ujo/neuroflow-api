<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use App\Repositories\Interfaces\IProfileRepository;
use App\Repositories\Interfaces\IUserRepository;
use App\Services\Interfaces\IUserService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class UserService extends Service implements IUserService
{
    public function __construct(
        private readonly IUserRepository $userRepository,
        private readonly IProfileRepository $profileRepository,
    ) {
        parent::__construct($userRepository);
    }

    public function getAllUsers(): Collection
    {
        return $this->userRepository->getAllWithProfile();
    }

    public function createUser(array $data): User
    {
        $user = DB::transaction(function () use ($data): User {
            if (! array_key_exists('profile_id', $data)) {
                $userProfile = $this->profileRepository->findBySlug(Profile::USER_SLUG);

                if (! $userProfile) {
                    throw new RuntimeException('The default User profile has not been seeded.');
                }

                $data['profile_id'] = $userProfile->id;
            }

            return $this->userRepository->create(Arr::only($data, [
                'profile_id',
                'name',
                'email',
                'email_verified_at',
                'password',
            ]));
        });

        $user->loadMissing('profile');

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        DB::transaction(function () use ($user, $data): void {
            $this->userRepository->updateUser($user, Arr::only($data, [
                'profile_id',
                'name',
                'email',
                'email_verified_at',
                'password',
            ]));
        });

        $user->refresh();
        $user->loadMissing('profile');

        return $user;
    }

    public function deleteUser(User $user): bool
    {
        return DB::transaction(function () use ($user): bool {
            $this->userRepository->deleteAccessTokens($user);

            return $this->userRepository->deleteUser($user);
        });
    }

    public function register(array $data): User
    {
        $user = DB::transaction(function () use ($data): User {
            $userProfile = $this->profileRepository->findBySlug(Profile::USER_SLUG);

            if (! $userProfile) {
                throw new RuntimeException('The default User profile has not been seeded.');
            }

            return $this->userRepository->create([
                ...Arr::only($data, ['name', 'email', 'password']),
                'profile_id' => $userProfile->id,
            ]);
        });

        $user->sendEmailVerificationNotification();
        $user->refresh();
        $user->loadMissing('profile');

        return $user;
    }

    public function login(array $credentials): array
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Your email address is not verified.'],
            ]);
        }

        $user->loadMissing('profile');

        $token = $this->userRepository->createAccessToken(
            $user,
            $credentials['device_name'] ?? 'auth_token'
        );

        return [
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $user,
        ];
    }

    public function resendEmailVerification(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['We could not find a user with that email address.'],
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['This email address is already verified.'],
            ]);
        }

        $user->sendEmailVerificationNotification();
    }

    public function verifyEmail(int $userId, string $hash): User
    {
        $user = $this->userRepository->find($userId);

        if (! $user) {
            throw ValidationException::withMessages([
                'user' => ['The user could not be found.'],
            ]);
        }

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            throw ValidationException::withMessages([
                'hash' => ['The verification link is invalid.'],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            $this->userRepository->markEmailAsVerified($user);
            event(new Verified($user));
        }

        $user->refresh();
        $user->loadMissing('profile');

        return $user;
    }

    public function sendPasswordResetLink(string $email): void
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    public function resetPassword(array $data): void
    {
        $status = Password::reset(
            Arr::only($data, ['email', 'password', 'password_confirmation', 'token']),
            function (User $user, string $password): void {
                DB::transaction(function () use ($user, $password): void {
                    $this->userRepository->updatePassword($user, $password);
                    $this->userRepository->deleteAccessTokens($user);
                });

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    public function logout(User $user): void
    {
        $this->userRepository->deleteCurrentAccessToken($user);
    }
}
