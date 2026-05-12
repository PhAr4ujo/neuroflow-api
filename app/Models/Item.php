<?php

namespace App\Models;

use Database\Factories\ItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'route'])]
class Item extends Model
{
    /** @use HasFactory<ItemFactory> */
    use HasFactory;

    /**
     * @return array<int, array{name: string, route: string}>
     */
    public static function defaults(): array
    {
        return [
            ['name' => 'Users', 'route' => '/users'],
            ['name' => 'Core', 'route' => '/core'],
            ['name' => 'Flows', 'route' => '/flows'],
            ['name' => 'Modes', 'route' => '/modes'],
            ['name' => 'Settings', 'route' => 'settings'],
        ];
    }

    /**
     * @return BelongsToMany<Profile, $this>
     */
    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class)->withTimestamps();
    }
}
