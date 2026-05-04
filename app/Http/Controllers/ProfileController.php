<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use App\Services\Interfaces\IProfileService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProfileController extends Controller
{
    public function __construct(
        private readonly IProfileService $profileService,
    ) {}

    /**
     * List the available application profiles.
     */
    public function index(): AnonymousResourceCollection
    {
        return ProfileResource::collection($this->profileService->getAll());
    }

    /**
     * Show an application profile.
     */
    public function show(Profile $profile): ProfileResource
    {
        return new ProfileResource($profile);
    }
}
