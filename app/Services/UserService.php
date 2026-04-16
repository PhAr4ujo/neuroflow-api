<?php

namespace App\Services;

use App\Actions\AttachEmpresasToUserAction;
use App\Actions\AttachSecretariasToUserAction;
use App\Models\User;
use App\Repositories\Interfaces\IUserRepository;
use App\Services\Interfaces\IUserService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class UserService extends Service implements IUserService
{
    private IUserRepository $userRepository;

    public function __construct(
        IUserRepository $repository,
    ) {
        parent::__construct($repository);
        $this->userRepository = $repository;
    }
}
