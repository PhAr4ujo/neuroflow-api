<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            /**
             * Profile assigned to the new user. Admin-only. When omitted, the default User profile is used.
             */
            'profile_id' => ['sometimes', 'nullable', Rule::exists('profiles', 'id')],
            /**
             * Display name for the user.
             */
            'name' => ['required', 'string', 'max:255'],
            /**
             * Login email for the user. Admin-only on user creation and must be unique.
             */
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            /**
             * Email verification timestamp. Admin-only. Send `null` or omit it to keep the user unverified.
             */
            'email_verified_at' => ['sometimes', 'nullable', 'date'],
            /**
             * User password. Must be confirmed with `password_confirmation`.
             */
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ];
    }
}
