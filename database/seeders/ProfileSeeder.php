<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfileSeeder extends Seeder
{
    /**
     * Seed the application's profiles.
     */
    public function run(): void
    {
        $userProfile = null;

        foreach (Profile::defaults() as $profile) {
            $savedProfile = Profile::query()->updateOrCreate(
                ['slug' => $profile['slug']],
                ['name' => $profile['name']],
            );

            if ($profile['slug'] === Profile::USER_SLUG) {
                $userProfile = $savedProfile;
            }
        }

        if ($userProfile !== null) {
            DB::table('users')
                ->whereNull('profile_id')
                ->update(['profile_id' => $userProfile->id]);
        }
    }
}
