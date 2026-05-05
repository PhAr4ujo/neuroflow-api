<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
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

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'password' => ['sometimes', 'required', 'string', 'confirmed', Password::min(8)],
        ];

        $targetUser = $this->targetUser();

        if ($targetUser !== null && ($this->user()?->can('updateAdministrativeFields', $targetUser) ?? false)) {
            $rules['profile_id'] = ['sometimes', 'nullable', Rule::exists('profiles', 'id')];
            $rules['email'] = [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->targetUser()?->id),
            ];
            $rules['email_verified_at'] = ['sometimes', 'nullable', 'date'];
        }

        return $rules;
    }

    private function targetUser(): ?User
    {
        $user = $this->route('user');

        return $user instanceof User ? $user : null;
    }
}
