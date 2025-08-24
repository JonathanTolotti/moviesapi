<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddMediaRequest;
use App\Http\Requests\MarkEpisodeAsPausedRequest;
use App\Http\Requests\MarkEpisodeAsWatchedRequest;
use App\Services\UserMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UserMediaController extends Controller
{
    public function __construct(private readonly UserMediaService $userMediaService)
    {
    }

    public function addMedia(AddMediaRequest $request): JsonResponse
    {
        $userMedia = $this->userMediaService->addMedia(
            $request->user(),
            $request->validated('tmdb_id'),
            $request->validated('media_type')
        );

        return response()->json($userMedia, Response::HTTP_CREATED);
    }

    public function toggleFavorite(string $uuid): JsonResponse
    {
        $userMedia = $this->userMediaService->toggleFavorite(auth()->user(), $uuid);

        return response()->json($userMedia);
    }

    public function markEpisodeAsWatched(MarkEpisodeAsWatchedRequest $request, string $uuid, string $episodeUuid): JsonResponse
    {
        $userEpisode = $this->userMediaService->markEpisodeAsWatched(
            $request->user(),
            $uuid,
            $episodeUuid
        );

        return response()->json($userEpisode);
    }

    public function markEpisodeAsPaused(MarkEpisodeAsPausedRequest $request, string $uuid, string $episodeUuid): JsonResponse
    {
        $userEpisode = $this->userMediaService->markEpisodeAsPaused(
            $request->user(),
            $uuid,
            $episodeUuid,
            $request->validated('watched_duration')
        );

        return response()->json($userEpisode);
    }

    public function index(): JsonResponse
    {
        $userMedia = $this->userMediaService->getAllUserMedia(auth()->user());

        return response()->json($userMedia);
    }

    public function unfinished(): JsonResponse
    {
        $userMedia = $this->userMediaService->getUnfinishedUserMedia(auth()->user());

        return response()->json($userMedia);
    }

    public function resetProgress(string $uuid): JsonResponse
    {
        $userMedia = $this->userMediaService->resetUserMediaProgress(auth()->user(), $uuid);

        return response()->json($userMedia);
    }

    public function getEpisodes(string $uuid): JsonResponse
    {
        $episodes = $this->userMediaService->getEpisodesWithWatchStatus(auth()->user(), $uuid);

        return response()->json($episodes);
    }
}