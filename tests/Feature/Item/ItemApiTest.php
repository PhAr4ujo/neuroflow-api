<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\User;
use Database\Seeders\ItemProfileSeeder;
use Database\Seeders\ItemSeeder;
use Database\Seeders\ProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ItemApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ProfileSeeder::class);
        $this->seed(ItemSeeder::class);
        $this->seed(ItemProfileSeeder::class);
    }

    public function test_items_require_authentication(): void
    {
        $response = $this->getJson('/api/items');

        $response->assertUnauthorized();
    }

    public function test_admin_can_list_all_items(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $response = $this->getJson('/api/items');

        $response
            ->assertOk()
            ->assertJsonFragment(['name' => 'Users', 'route' => '/users'])
            ->assertJsonFragment(['name' => 'Core', 'route' => '/core'])
            ->assertJsonFragment(['name' => 'Flows', 'route' => '/flows'])
            ->assertJsonFragment(['name' => 'Settings', 'route' => 'settings']);
    }

    public function test_user_can_list_only_allowed_items(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/items');

        $response
            ->assertOk()
            ->assertJsonMissing(['name' => 'Users', 'route' => '/users'])
            ->assertJsonFragment(['name' => 'Core', 'route' => '/core'])
            ->assertJsonFragment(['name' => 'Flows', 'route' => '/flows'])
            ->assertJsonFragment(['name' => 'Settings', 'route' => 'settings']);
    }

    public function test_admin_can_view_any_item(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $item = Item::query()->where('name', 'Users')->firstOrFail();

        $response = $this->getJson("/api/items/{$item->id}");

        $response
            ->assertOk()
            ->assertJsonPath('id', $item->id)
            ->assertJsonPath('name', 'Users')
            ->assertJsonPath('route', '/users');
    }

    public function test_user_can_view_an_allowed_item(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $item = Item::query()->where('name', 'Core')->firstOrFail();

        $response = $this->getJson("/api/items/{$item->id}");

        $response
            ->assertOk()
            ->assertJsonPath('id', $item->id)
            ->assertJsonPath('name', 'Core')
            ->assertJsonPath('route', '/core');
    }

    public function test_user_cannot_view_an_unallowed_item(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $item = Item::query()->where('name', 'Users')->firstOrFail();

        $response = $this->getJson("/api/items/{$item->id}");

        $response->assertForbidden();
    }
}
