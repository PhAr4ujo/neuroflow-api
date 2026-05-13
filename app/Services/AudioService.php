<?php

namespace App\Services;

use App\Models\Audio;
use App\Repositories\Interfaces\IAudioRepository;
use App\Services\Interfaces\IAudioService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class AudioService extends Service implements IAudioService
{
    private const DIRECTORY = 'audios';

    public function __construct(
        private readonly IAudioRepository $audioRepository,
    ) {
        parent::__construct($audioRepository);
    }

    /**
     * @return Collection<int, Audio>
     */
    public function getAllAudios(): Collection
    {
        return $this->audioRepository->getAllWithMode();
    }

    public function paginateAudios(int $paginationAmount = 15): LengthAwarePaginator
    {
        return $this->audioRepository->paginateWithMode($paginationAmount);
    }

    /**
     * @return Collection<int, Audio>
     */
    public function getByMode(int $modeId): Collection
    {
        return $this->audioRepository->getByMode($modeId);
    }

    public function paginateByMode(int $modeId, int $paginationAmount = 15): LengthAwarePaginator
    {
        return $this->audioRepository->paginateByMode($modeId, $paginationAmount);
    }

    public function createAudio(array $data, UploadedFile $file): Audio
    {
        $path = $this->storeFile($file);

        try {
            $audio = DB::transaction(fn (): Audio => $this->audioRepository->create([
                ...Arr::only($data, ['name', 'mode_id']),
                'path' => $path,
            ]));
        } catch (Throwable $throwable) {
            Storage::delete($path);

            throw $throwable;
        }

        $audio->loadMissing('mode');

        return $audio;
    }

    public function updateAudio(Audio $audio, array $data, ?UploadedFile $file = null): Audio
    {
        $payload = Arr::only($data, ['name', 'mode_id']);
        $newPath = null;
        $oldPath = $audio->path;

        if ($file !== null) {
            $newPath = $this->storeFile($file);
            $payload['path'] = $newPath;
        }

        try {
            DB::transaction(fn () => $this->edit($audio->id, $payload));
        } catch (Throwable $throwable) {
            if ($newPath !== null) {
                Storage::delete($newPath);
            }

            throw $throwable;
        }

        if ($newPath !== null && $newPath !== $oldPath) {
            Storage::delete($oldPath);
        }

        $audio->refresh();
        $audio->loadMissing('mode');

        return $audio;
    }

    public function deleteAudio(Audio $audio): bool
    {
        $path = $audio->path;

        $deleted = DB::transaction(fn (): bool => $this->delete($audio->id));

        if ($deleted) {
            Storage::delete($path);
        }

        return $deleted;
    }

    private function storeFile(UploadedFile $file): string
    {
        $path = $file->store(self::DIRECTORY);

        if (! is_string($path)) {
            throw new RuntimeException('The audio file could not be stored.');
        }

        return $path;
    }
}
