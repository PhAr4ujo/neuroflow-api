<?php

namespace Tests\Feature\User;

use App\Models\Profile;
use App\Models\User;
use Database\Seeders\ProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ProfileSeeder::class);
    }

    public function test_users_require_authentication(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertUnauthorized();
    }

    public function test_only_admin_can_list_all_users(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/users')->assertForbidden();

        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/users');

        $response
            ->assertOk()
            ->assertJsonFragment(['id' => $admin->id, 'email' => 'admin@example.com'])
            ->assertJsonFragment(['id' => $user->id, 'email' => 'user@example.com']);
    }

    public function test_admin_can_create_a_user(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $adminProfile = Profile::query()->where('slug', Profile::ADMIN_SLUG)->firstOrFail();

        $response = $this->postJson('/api/users', [
            'profile_id' => $adminProfile->id,
            'name' => 'Created Admin',
            'email' => 'created-admin@example.com',
            'email_verified_at' => '2026-05-05 12:00:00',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('name', 'Created Admin')
            ->assertJsonPath('email', 'created-admin@example.com')
            ->assertJsonPath('profile.slug', Profile::ADMIN_SLUG);

        $user = User::query()->where('email', 'created-admin@example.com')->firstOrFail();

        $this->assertSame($adminProfile->id, $user->profile_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('Password123', $user->password));
    }

    public function test_regular_user_cannot_create_a_user(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/users', [
            'name' => 'Created User',
            'email' => 'created-user@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_view_self_but_not_other_users(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson("/api/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('id', $user->id);

        $this->getJson("/api/users/{$otherUser->id}")
            ->assertForbidden();
    }

    public function test_admin_can_view_other_users(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $user = User::factory()->create();

        $this->getJson("/api/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_user_can_update_self_without_changing_email_profile_or_verification_date(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'email_verified_at' => now(),
        ]);
        $adminProfile = Profile::query()->where('slug', Profile::ADMIN_SLUG)->firstOrFail();

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/users/{$user->id}", [
            'profile_id' => $adminProfile->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'email_verified_at' => null,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('name', 'New Name')
            ->assertJsonPath('email', 'old@example.com')
            ->assertJsonPath('profile.slug', Profile::USER_SLUG);

        $freshUser = $user->fresh();

        $this->assertSame('New Name', $freshUser->name);
        $this->assertSame('old@example.com', $freshUser->email);
        $this->assertNotSame($adminProfile->id, $freshUser->profile_id);
        $this->assertNotNull($freshUser->email_verified_at);
        $this->assertTrue(Hash::check('NewPassword123', $freshUser->password));
    }

    public function test_user_cannot_update_other_users(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $otherUser = User::factory()->create([
            'name' => 'Old Name',
        ]);

        $response = $this->patchJson("/api/users/{$otherUser->id}", [
            'name' => 'New Name',
        ]);

        $response->assertForbidden();
        $this->assertSame('Old Name', $otherUser->fresh()->name);
    }

    public function test_admin_can_update_profile_email_and_verification_date(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $user = User::factory()->unverified()->create([
            'email' => 'before@example.com',
        ]);
        $adminProfile = Profile::query()->where('slug', Profile::ADMIN_SLUG)->firstOrFail();

        $response = $this->patchJson("/api/users/{$user->id}", [
            'profile_id' => $adminProfile->id,
            'name' => 'Updated Admin',
            'email' => 'after@example.com',
            'email_verified_at' => '2026-05-05 13:00:00',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('name', 'Updated Admin')
            ->assertJsonPath('email', 'after@example.com')
            ->assertJsonPath('profile.slug', Profile::ADMIN_SLUG);

        $freshUser = $user->fresh();

        $this->assertSame($adminProfile->id, $freshUser->profile_id);
        $this->assertSame('after@example.com', $freshUser->email);
        $this->assertNotNull($freshUser->email_verified_at);
    }

    public function test_only_admin_can_delete_users(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();

        Sanctum::actingAs($user);

        $this->deleteJson("/api/users/{$targetUser->id}")
            ->assertForbidden();

        $targetUser->createToken('existing_token');
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->deleteJson("/api/users/{$targetUser->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
