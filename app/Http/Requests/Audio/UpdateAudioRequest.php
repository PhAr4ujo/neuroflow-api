<?php

namespace App\Http\Requests\Audio;

use App\Models\Audio;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAudioRequest extends FormRequest
{
    public function authorize(): bool
    {
        $audio = $this->targetAudio();

        return $audio !== null
            && ($this->user()?->can('update', $audio) ?? false);
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
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('audios', 'name')->ignore($this->targetAudio()?->id),
            ],
            /**
             * Mode linked to this audio. Send `null` to remove the mode assignment.
             */
            'mode_id' => ['sometimes', 'nullable', Rule::exists('modes', 'id')],
            /**
             * Replacement audio file uploaded to Laravel storage. Maximum size is 200 MB.
             */
            'file' => ['sometimes', 'required', 'file', 'max:204800'],
        ];
    }

    private function targetAudio(): ?Audio
    {
        $audio = $this->route('audio');

        return $audio instanceof Audio ? $audio : null;
    }
}
