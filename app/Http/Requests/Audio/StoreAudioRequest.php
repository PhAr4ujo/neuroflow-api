<?php

namespace App\Http\Requests\Audio;

use App\Models\Audio;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAudioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Audio::class) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            /**
             * Display name for the audio. Must be unique.
             */
            'name' => ['required', 'string', 'max:255', Rule::unique('audios', 'name')],
            /**
             * Mode linked to this audio. Send `null` or omit it to keep the audio unassigned.
             */
            'mode_id' => ['sometimes', 'nullable', Rule::exists('modes', 'id')],
            /**
             * Audio file uploaded to Laravel storage. Maximum size is 200 MB.
             */
            'file' => ['required', 'file', 'max:204800'],
        ];
    }
}
