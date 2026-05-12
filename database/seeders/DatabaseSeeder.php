<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(ProfileSeeder::class);
        $this->call(ItemSeeder::class);
        $this->call(ItemProfileSeeder::class);
        $this->call(ModeSeeder::class);

        $userProfile = Profile::query()->where('slug', Profile::USER_SLUG)->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'profile_id' => $userProfile->id,
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => 'password',
            ],
        );
    }
}
