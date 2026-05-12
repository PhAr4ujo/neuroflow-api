<?php

namespace App\Services;

use App\Models\Mode;
use App\Repositories\Interfaces\IModeRepository;
use App\Services\Interfaces\IModeService;
use Illuminate\Support\Arr;

class ModeService extends Service implements IModeService
{
    public function __construct(
        private readonly IModeRepository $modeRepository,
    ) {
        parent::__construct($modeRepository);
    }

    public function createMode(array $data): Mode
    {
        return $this->modeRepository->create(Arr::only($data, [
            'name',
            'description',
            'color',
        ]));
    }

    public function updateMode(Mode $mode, array $data): Mode
    {
        $this->edit($mode->id, Arr::only($data, [
            'name',
            'description',
            'color',
        ]));

        $mode->refresh();

        return $mode;
    }

    public function deleteMode(Mode $mode): bool
    {
        return $this->delete($mode->id);
    }
}
