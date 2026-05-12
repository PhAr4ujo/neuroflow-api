<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Profile;
use Illuminate\Database\Seeder;

class ItemProfileSeeder extends Seeder
{
    /**
     * Seed the item access for each profile.
     */
    public function run(): void
    {
        $items = Item::query()->get()->keyBy('name');

        $accessByProfile = [
            Profile::ADMIN_SLUG => $items->pluck('id')->all(),
            Profile::USER_SLUG => $items->whereIn('name', ['Core', 'Flows', 'Modes', 'Settings'])->pluck('id')->all(),
        ];

        foreach ($accessByProfile as $profileSlug => $itemIds) {
            $profile = Profile::query()->where('slug', $profileSlug)->firstOrFail();

            $profile->items()->sync($itemIds);
        }
    }
}
