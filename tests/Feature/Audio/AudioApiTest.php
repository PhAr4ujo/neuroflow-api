<?php

namespace Tests\Feature\Audio;

use App\Models\Audio;
use App\Models\Mode;
use App\Models\User;
use Database\Seeders\ProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AudioApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ProfileSeeder::class);
    }

    public function test_audios_require_authentication(): void
    {
        $this->getJson('/api/audios')->assertUnauthorized();
        $this->getJson('/api/modes/1/audios')->assertUnauthorized();
        $this->postJson('/api/audios')->assertUnauthorized();
    }

    public function test_authenticated_user_can_list_audios(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $mode = Mode::factory()->create(['name' => 'Focus']);
        Audio::factory()->create([
            'name' => 'Focus Track',
            'path' => 'audios/focus-track.mp3',
            'mode_id' => $mode->id,
        ]);
        Audio::factory()->withoutMode()->create([
            'name' => 'Neutral Track',
            'path' => 'audios/neutral-track.mp3',
        ]);

        $response = $this->getJson('/api/audios');

        $response
            ->assertOk()
            ->assertJsonFragment(['name' => 'Focus Track', 'path' => 'audios/focus-track.mp3'])
            ->assertJsonFragment(['name' => 'Neutral Track', 'path' => 'audios/neutral-track.mp3'])
            ->assertJsonFragment(['name' => 'Focus']);
    }

    public function test_authenticated_user_can_list_audios_by_mode(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $focusMode = Mode::factory()->create(['name' => 'Focus']);
        $calmMode = Mode::factory()->create(['name' => 'Calm']);
        Audio::factory()->create([
            'name' => 'Focus Track One',
            'path' => 'audios/focus-track-one.mp3',
            'mode_id' => $focusMode->id,
        ]);
        Audio::factory()->create([
            'name' => 'Focus Track Two',
            'path' => 'audios/focus-track-two.mp3',
            'mode_id' => $focusMode->id,
        ]);
        Audio::factory()->create([
            'name' => 'Calm Track',
            'path' => 'audios/calm-track.mp3',
            'mode_id' => $calmMode->id,
        ]);
        Audio::factory()->withoutMode()->create([
            'name' => 'Neutral Track',
            'path' => 'audios/neutral-track.mp3',
        ]);

        $response = $this->getJson("/api/modes/{$focusMode->id}/audios");

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'Focus Track One', 'mode_id' => $focusMode->id])
            ->assertJsonFragment(['name' => 'Focus Track Two', 'mode_id' => $focusMode->id])
            ->assertJsonMissing(['name' => 'Calm Track'])
            ->assertJsonMissing(['name' => 'Neutral Track']);
    }

    public function test_authenticated_user_can_view_an_audio(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $mode = Mode::factory()->create(['name' => 'Calm']);
        $audio = Audio::factory()->create([
            'name' => 'Rain Loop',
            'path' => 'audios/rain-loop.mp3',
            'mode_id' => $mode->id,
        ]);

        $response = $this->getJson("/api/audios/{$audio->id}");

        $response
            ->assertOk()
            ->assertJsonPath('id', $audio->id)
            ->assertJsonPath('name', 'Rain Loop')
            ->assertJsonPath('path', 'audios/rain-loop.mp3')
            ->assertJsonPath('mode.id', $mode->id)
            ->assertJsonPath('mode.name', 'Calm');
    }

    public function test_regular_user_cannot_create_update_or_delete_audios(): void
    {
        Storage::fake($this->audioDisk());
        Sanctum::actingAs(User::factory()->create());

        $audio = Audio::factory()->create([
            'name' => 'Original',
            'path' => 'audios/original.mp3',
        ]);

        $this->post('/api/audios', [
            'name' => 'Blocked',
            'file' => UploadedFile::fake()->create('blocked.mp3', 100, 'audio/mpeg'),
        ], ['Accept' => 'application/json'])->assertForbidden();

        $this->patch("/api/audios/{$audio->id}", [
            'name' => 'Blocked Update',
        ], ['Accept' => 'application/json'])->assertForbidden();

        $this->deleteJson("/api/audios/{$audio->id}")
            ->assertForbidden();

        $this->assertDatabaseMissing('audios', ['name' => 'Blocked']);
        $this->assertDatabaseHas('audios', [
            'id' => $audio->id,
            'name' => 'Original',
            'path' => 'audios/original.mp3',
        ]);
    }

    public function test_admin_can_create_an_audio_and_store_the_file(): void
    {
        Storage::fake($this->audioDisk());
        Sanctum::actingAs(User::factory()->admin()->create());

        $mode = Mode::factory()->create();

        $response = $this->post('/api/audios', [
            'name' => 'Morning Focus',
            'mode_id' => $mode->id,
            'file' => UploadedFile::fake()->create('morning-focus.mp3', 1024, 'audio/mpeg'),
        ], ['Accept' => 'application/json']);

        $response
            ->assertCreated()
            ->assertJsonPath('name', 'Morning Focus')
            ->assertJsonPath('mode_id', $mode->id)
            ->assertJsonPath('mode.id', $mode->id);

        $path = $response->json('path');

        $this->assertIsString($path);
        $this->assertStringStartsWith('audios/', $path);
        Storage::disk($this->audioDisk())->assertExists($path);

        $this->assertDatabaseHas('audios', [
            'name' => 'Morning Focus',
            'path' => $path,
            'mode_id' => $mode->id,
        ]);
    }

    public function test_admin_can_update_an_audio_and_replace_the_file(): void
    {
        Storage::fake($this->audioDisk());
        Sanctum::actingAs(User::factory()->admin()->create());

        $mode = Mode::factory()->create();
        $audio = Audio::factory()->create([
            'name' => 'Before',
            'path' => 'audios/before.mp3',
        ]);
        Storage::disk($this->audioDisk())->put($audio->path, 'old-audio');

        $response = $this->patch("/api/audios/{$audio->id}", [
            'name' => 'After',
            'mode_id' => $mode->id,
            'file' => UploadedFile::fake()->create('after.mp3', 2048, 'audio/mpeg'),
        ], ['Accept' => 'application/json']);

        $response
            ->assertOk()
            ->assertJsonPath('name', 'After')
            ->assertJsonPath('mode_id', $mode->id);

        $newPath = $response->json('path');

        $this->assertIsString($newPath);
        $this->assertNotSame('audios/before.mp3', $newPath);
        Storage::disk($this->audioDisk())->assertMissing('audios/before.mp3');
        Storage::disk($this->audioDisk())->assertExists($newPath);

        $this->assertDatabaseHas('audios', [
            'id' => $audio->id,
            'name' => 'After',
            'path' => $newPath,
            'mode_id' => $mode->id,
        ]);
    }

    public function test_admin_can_delete_an_audio_and_remove_the_file(): void
    {
        Storage::fake($this->audioDisk());
        Sanctum::actingAs(User::factory()->admin()->create());

        $audio = Audio::factory()->create([
            'path' => 'audios/delete-me.mp3',
        ]);
        Storage::disk($this->audioDisk())->put($audio->path, 'audio');

        $this->deleteJson("/api/audios/{$audio->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('audios', ['id' => $audio->id]);
        Storage::disk($this->audioDisk())->assertMissing('audios/delete-me.mp3');
    }

    public function test_audio_fields_are_validated(): void
    {
        Storage::fake($this->audioDisk());
        Sanctum::actingAs(User::factory()->admin()->create());

        Audio::factory()->create(['name' => 'Existing Audio']);

        $this->post('/api/audios', [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'file']);

        $this->post('/api/audios', [
            'name' => 'Existing Audio',
            'mode_id' => 999,
            'file' => UploadedFile::fake()->create('duplicate.mp3', 100, 'audio/mpeg'),
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'mode_id']);

        $this->post('/api/audios', [
            'name' => 'Too Large',
            'file' => UploadedFile::fake()->create('too-large.mp3', 204801, 'audio/mpeg'),
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    private function audioDisk(): string
    {
        return (string) config('filesystems.default');
    }
}
