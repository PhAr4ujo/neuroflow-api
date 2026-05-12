<?php

namespace App\Http\Controllers;

use App\Http\Requests\Audio\StoreAudioRequest;
use App\Http\Requests\Audio\UpdateAudioRequest;
use App\Http\Resources\AudioResource;
use App\Models\Audio;
use App\Models\Mode;
use App\Services\Interfaces\IAudioService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class AudioController extends Controller
{
    public function __construct(
        private readonly IAudioService $audioService,
    ) {
        $this->authorizeResource(Audio::class, 'audio');
    }

    /**
     * List the available audios.
     */
    public function index(): AnonymousResourceCollection
    {
        return AudioResource::collection($this->audioService->getAllAudios());
    }

    /**
     * List the available audios for a mode.
     */
    public function byMode(Mode $mode): AnonymousResourceCollection
    {
        return AudioResource::collection($this->audioService->getByMode($mode->id));
    }

    /**
     * Create an audio.
     */
    public function store(StoreAudioRequest $request): AudioResource
    {
        $audio = $this->audioService->createAudio(
            $request->validated(),
            $request->file('file'),
        );

        return new AudioResource($audio, Response::HTTP_CREATED);
    }

    /**
     * Show an audio.
     */
    public function show(Audio $audio): AudioResource
    {
        $audio->loadMissing('mode');

        return new AudioResource($audio);
    }

    /**
     * Update an audio.
     */
    public function update(UpdateAudioRequest $request, Audio $audio): AudioResource
    {
        $audio = $this->audioService->updateAudio(
            $audio,
            $request->validated(),
            $request->file('file'),
        );

        return new AudioResource($audio);
    }

    /**
     * Delete an audio.
     */
    public function destroy(Audio $audio): HttpResponse
    {
        $this->audioService->deleteAudio($audio);

        return response()->noContent();
    }
}
