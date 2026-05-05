<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $targetUser = $this->targetUser();

        return $targetUser !== null
            && ($this->user()?->can('update', $targetUser) ?? false);
    }

    protected function prepareForValidation(): void
    {
        $targetUser = $this->targetUser();

        if ($targetUser !== null && ($this->user()?->can('updateAdministrativeFields', $targetUser) ?? false)) {
            return;
        }

        $this->replace(Arr::except($this->all(), [
            'profile_id',
            'email',
            'email_verified_at',
        ]));
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            /**
             * Display name. Admins can update this for any user; regular users can update it only for themselves.
             */
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            /**
             * Password. Admins can update this for any user; regular users can update it only for themselves. Must be confirmed with `password_confirmation`.
             */
            'password' => ['sometimes', 'required', 'string', 'confirmed', Password::min(8)],
            /**
             * Profile assigned to the user. Admin-only on update. Ignored for regular users.
             */
            'profile_id' => ['sometimes', 'nullable', Rule::exists('profiles', 'id')],
            /**
             * Login email for the user. Admin-only on update and must be unique. Ignored for regular users.
             */
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->targetUser()?->id),
            ],
            /**
             * Email verification timestamp. Admin-only on update. Send `null` to mark the user as unverified. Ignored for regular users.
             */
            'email_verified_at' => ['sometimes', 'nullable', 'date'],
        ];
    }

    private function targetUser(): ?User
    {
        $user = $this->route('user');

        return $user instanceof User ? $user : null;
    }
}
