<?php

namespace App\Providers;

use App\Services\Interfaces\IProfileService;
use App\Services\Interfaces\IUserService;
use App\Services\ProfileService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class SystemServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(IProfileService::class, ProfileService::class);
        $this->app->bind(IUserService::class, UserService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
