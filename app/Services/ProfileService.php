<?php

namespace App\Services;

use App\Repositories\Interfaces\IProfileRepository;
use App\Services\Interfaces\IProfileService;

class ProfileService extends Service implements IProfileService
{
    public function __construct(
        private readonly IProfileRepository $profileRepository,
    ) {
        parent::__construct($profileRepository);
    }
}
