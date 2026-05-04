<?php

namespace Tests\Feature\Profile;

use App\Models\Profile;
use App\Models\User;
use Database\Seeders\ProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_profiles_require_authentication(): void
    {
        $response = $this->getJson('/api/profiles');

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_list_application_profiles(): void
    {
        $this->seed(ProfileSeeder::class);
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/profiles');

        $response
            ->assertOk()
            ->assertJsonFragment(['name' => 'Admin', 'slug' => Profile::ADMIN_SLUG])
            ->assertJsonFragment(['name' => 'User', 'slug' => Profile::USER_SLUG]);
    }

    public function test_authenticated_user_can_view_an_application_profile(): void
    {
        $this->seed(ProfileSeeder::class);
        Sanctum::actingAs(User::factory()->create());

        $profile = Profile::query()->where('slug', Profile::ADMIN_SLUG)->firstOrFail();

        $response = $this->getJson("/api/profiles/{$profile->id}");

        $response
            ->assertOk()
            ->assertJsonPath('id', $profile->id)
            ->assertJsonPath('name', 'Admin')
            ->assertJsonPath('slug', Profile::ADMIN_SLUG);
    }
}
