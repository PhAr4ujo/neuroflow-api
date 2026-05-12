<?php

namespace Database\Seeders;

use App\Models\Mode;
use Illuminate\Database\Seeder;

class ModeSeeder extends Seeder
{
    /**
     * Seed the application's modes.
     */
    public function run(): void
    {
        foreach (Mode::defaults() as $mode) {
            Mode::query()->updateOrCreate(
                [
                    'name' => $mode['name'],
                    'description' => $mode['description'],
                ],
                ['color' => $mode['color']],
            );
        }
    }
}
