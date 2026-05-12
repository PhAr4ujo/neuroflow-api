<?php

namespace Tests\Feature\Mode;

use App\Models\Mode;
use App\Models\User;
use Database\Seeders\ProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ModeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ProfileSeeder::class);
    }

    public function test_modes_require_authentication(): void
    {
        $this->getJson('/api/modes')->assertUnauthorized();
        $this->postJson('/api/modes')->assertUnauthorized();
    }

    public function test_authenticated_user_can_list_modes(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Mode::factory()->create([
            'name' => 'Focus',
            'description' => 'Focused audio and flow behavior.',
            'color' => '#3366FF',
        ]);
        Mode::factory()->create([
            'name' => 'Calm',
            'description' => 'Gentler audio and flow behavior.',
            'color' => '#22AA88',
        ]);

        $response = $this->getJson('/api/modes');

        $response
            ->assertOk()
            ->assertJsonFragment(['name' => 'Focus', 'color' => '#3366FF'])
            ->assertJsonFragment(['name' => 'Calm', 'color' => '#22AA88']);
    }

    public function test_authenticated_user_can_view_a_mode(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $mode = Mode::factory()->create([
            'name' => 'Deep Work',
            'description' => 'Mode for uninterrupted flow sessions.',
            'color' => '#111AAA',
        ]);

        $response = $this->getJson("/api/modes/{$mode->id}");

        $response
            ->assertOk()
            ->assertJsonPath('id', $mode->id)
            ->assertJsonPath('name', 'Deep Work')
            ->assertJsonPath('description', 'Mode for uninterrupted flow sessions.')
            ->assertJsonPath('color', '#111AAA');
    }

    public function test_regular_user_cannot_create_update_or_delete_modes(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $mode = Mode::factory()->create([
            'name' => 'Original',
            'description' => 'Original description.',
            'color' => '#123456',
        ]);

        $this->postJson('/api/modes', [
            'name' => 'Blocked',
            'description' => 'Should not be created.',
            'color' => '#654321',
        ])->assertForbidden();

        $this->patchJson("/api/modes/{$mode->id}", [
            'name' => 'Blocked Update',
            'description' => 'Should not be updated.',
            'color' => '#ABCDEF',
        ])->assertForbidden();

        $this->deleteJson("/api/modes/{$mode->id}")
            ->assertForbidden();

        $this->assertDatabaseMissing('modes', ['name' => 'Blocked']);
        $this->assertDatabaseHas('modes', [
            'id' => $mode->id,
            'name' => 'Original',
            'description' => 'Original description.',
            'color' => '#123456',
        ]);
    }

    public function test_admin_can_create_a_mode(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $response = $this->postJson('/api/modes', [
            'name' => 'Focus',
            'description' => 'Focused audio and flow behavior.',
            'color' => '#3366FF',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('name', 'Focus')
            ->assertJsonPath('description', 'Focused audio and flow behavior.')
            ->assertJsonPath('color', '#3366FF');

        $this->assertDatabaseHas('modes', [
            'name' => 'Focus',
            'description' => 'Focused audio and flow behavior.',
            'color' => '#3366FF',
        ]);
    }

    public function test_admin_can_update_a_mode(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $mode = Mode::factory()->create([
            'name' => 'Original',
            'description' => 'Original description.',
            'color' => '#123456',
        ]);

        $response = $this->patchJson("/api/modes/{$mode->id}", [
            'name' => 'Updated',
            'description' => 'Updated description.',
            'color' => '#ABCDEF',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('name', 'Updated')
            ->assertJsonPath('description', 'Updated description.')
            ->assertJsonPath('color', '#ABCDEF');

        $this->assertDatabaseHas('modes', [
            'id' => $mode->id,
            'name' => 'Updated',
            'description' => 'Updated description.',
            'color' => '#ABCDEF',
        ]);
    }

    public function test_admin_can_delete_a_mode(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $mode = Mode::factory()->create();

        $this->deleteJson("/api/modes/{$mode->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('modes', ['id' => $mode->id]);
    }

    public function test_mode_fields_are_required_and_color_must_be_hex(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/modes', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'description', 'color']);

        $this->postJson('/api/modes', [
            'name' => 'Focus',
            'description' => 'Focused audio and flow behavior.',
            'color' => '3366FF',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['color']);
    }
}
