<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\User;
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
    }

    public function test_items_require_authentication(): void
    {
        $response = $this->getJson('/api/items');

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_list_navbar_items(): void
    {
        $this->seed(ItemSeeder::class);
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/items');

        $response
            ->assertOk()
            ->assertJsonFragment(['name' => 'Users', 'route' => '/users'])
            ->assertJsonFragment(['name' => 'Core', 'route' => '/core'])
            ->assertJsonFragment(['name' => 'Flows', 'route' => '/flows'])
            ->assertJsonFragment(['name' => 'Settings', 'route' => 'settings']);
    }

    public function test_authenticated_user_can_view_a_navbar_item(): void
    {
        $this->seed(ItemSeeder::class);
        Sanctum::actingAs(User::factory()->create());

        $item = Item::query()->where('name', 'Users')->firstOrFail();

        $response = $this->getJson("/api/items/{$item->id}");

        $response
            ->assertOk()
            ->assertJsonPath('id', $item->id)
            ->assertJsonPath('name', 'Users')
            ->assertJsonPath('route', '/users');
    }
}
