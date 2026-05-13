<?php

namespace App\Services\Interfaces;

use App\Models\Audio;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface IAudioService extends IService
{
    public function getAllAudios(): Collection;

    public function paginateAudios(int $paginationAmount = 15): LengthAwarePaginator;

    public function getByMode(int $modeId): Collection;

    public function paginateByMode(int $modeId, int $paginationAmount = 15): LengthAwarePaginator;

    public function createAudio(array $data, UploadedFile $file): Audio;

    public function updateAudio(Audio $audio, array $data, ?UploadedFile $file = null): Audio;

    public function deleteAudio(Audio $audio): bool;
}
