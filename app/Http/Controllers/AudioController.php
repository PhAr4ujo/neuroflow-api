<?php

namespace App\Http\Controllers;

use App\Http\Requests\Audio\StoreAudioRequest;
use App\Http\Requests\Audio\UpdateAudioRequest;
use App\Http\Resources\AudioResource;
use App\Models\Audio;
use App\Models\Mode;
use App\Services\Interfaces\IAudioService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
    public function index(Request $request): AnonymousResourceCollection
    {
        return AudioResource::collection(
            $this->audioService->paginateAudios($this->paginationAmount($request)),
        );
    }

    /**
     * List every available audio without pagination.
     */
    public function getAll(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Audio::class);

        return AudioResource::collection($this->audioService->getAllAudios());
    }

    /**
     * List the available audios for a mode.
     */
    public function byMode(Request $request, Mode $mode): AnonymousResourceCollection
    {
        return AudioResource::collection(
            $this->audioService->paginateByMode($mode->id, $this->paginationAmount($request)),
        );
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
     * Stream an audio file.
     */
    public function stream(Request $request, Audio $audio): StreamedResponse|HttpResponse
    {
        abort_unless(Storage::exists($audio->path), Response::HTTP_NOT_FOUND);

        $size = Storage::size($audio->path);
        $start = 0;
        $end = $size - 1;
        $status = Response::HTTP_OK;
        $headers = [
            'Accept-Ranges' => 'bytes',
            'Content-Type' => Storage::mimeType($audio->path) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.basename($audio->path).'"',
        ];

        if ($range = $request->header('Range')) {
            $range = $this->parseRange($range, $size);

            if ($range === null) {
                return response('', Response::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE, [
                    ...$headers,
                    'Content-Range' => "bytes */{$size}",
                ]);
            }

            [$start, $end] = $range;
            $status = Response::HTTP_PARTIAL_CONTENT;
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
        }

        $length = $end - $start + 1;
        $headers['Content-Length'] = (string) $length;

        return response()->stream(function () use ($audio, $start, $length): void {
            $stream = Storage::readStream($audio->path);

            if ($stream === false) {
                return;
            }

            fseek($stream, $start);

            $remaining = $length;

            while ($remaining > 0 && ! feof($stream)) {
                $chunk = fread($stream, min(8192, $remaining));

                if ($chunk === false) {
                    break;
                }

                echo $chunk;
                $remaining -= strlen($chunk);
                flush();
            }

            fclose($stream);
        }, $status, $headers);
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

    /**
     * @return array{0: int, 1: int}|null
     */
    private function parseRange(string $range, int $size): ?array
    {
        if (! preg_match('/^bytes=(\d*)-(\d*)$/', $range, $matches)) {
            return null;
        }

        if ($matches[1] === '' && $matches[2] === '') {
            return null;
        }

        if ($matches[1] === '') {
            $suffixLength = (int) $matches[2];

            if ($suffixLength <= 0) {
                return null;
            }

            return [max(0, $size - $suffixLength), $size - 1];
        }

        $start = (int) $matches[1];
        $end = $matches[2] === '' ? $size - 1 : (int) $matches[2];

        if ($start > $end || $start >= $size) {
            return null;
        }

        return [$start, min($end, $size - 1)];
    }
}
