<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Seed the application's items.
     */
    public function run(): void
    {
        foreach (Item::defaults() as $item) {
            Item::query()->updateOrCreate(
                ['name' => $item['name']],
                ['route' => $item['route']],
            );
        }
    }
}
