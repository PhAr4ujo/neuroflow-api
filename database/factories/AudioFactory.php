<?php

namespace Database\Factories;

use App\Models\Audio;
use App\Models\Mode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Audio>
 */
class AudioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => Str::headline($name),
            'path' => 'audios/'.fake()->unique()->uuid().'.mp3',
            'mode_id' => Mode::factory(),
        ];
    }

    /**
     * Indicate that the audio is not assigned to a mode.
     */
    public function withoutMode(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode_id' => null,
        ]);
    }
}
