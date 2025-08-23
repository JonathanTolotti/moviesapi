<?php

namespace App\Services;

use App\Models\Episode;
use App\Models\Media;
use App\Models\Season;
use App\Models\User;
use App\Models\UserEpisode;
use App\Models\UserMedia;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection; // New import
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserMediaService
{
    public function __construct(private readonly TheMovieDbService $theMovieDbService)
    {
    }

    public function addMedia(User $user, int $tmdbId, string $mediaType): UserMedia
    {
        $mediaDetails = $this->fetchMediaDetailsFromTmdb($tmdbId, $mediaType);

        $media = Media::firstOrCreate(
            ['tmdb_id' => $tmdbId, 'media_type' => $mediaType],
            [
                'title' => $mediaDetails['title'] ?? $mediaDetails['name'],
                'overview' => $mediaDetails['overview'],
                'poster_path' => $mediaDetails['poster_path'] ?? $mediaDetails['backdrop_path'],
                'release_date' => $mediaDetails['release_date'] ?? $mediaDetails['first_air_date'],
            ]
        );

        if ($mediaType === 'tv') {
            $this->saveTvShowSeasonsAndEpisodes($media, $mediaDetails);
        }

        return UserMedia::firstOrCreate(
            ['user_id' => $user->id, 'media_id' => $media->id],
            ['is_favorite' => false, 'status' => 'watching']
        );
    }

    public function toggleFavorite(User $user, string $uuid): UserMedia
    {
        $userMedia = UserMedia::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->first();

        if (!$userMedia) {
            throw new ModelNotFoundException('UserMedia not found.');
        }

        $userMedia->is_favorite = !$userMedia->is_favorite;
        $userMedia->save();

        return $userMedia;
    }

    public function markEpisodeAsWatched(User $user, string $userMediaUuid, string $episodeUuid): UserEpisode
    {
        $userMedia = UserMedia::where('uuid', $userMediaUuid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $episode = Episode::where('uuid', $episodeUuid)->firstOrFail();

        $season = $episode->season;
        $media = $season->media;

        if ($media->media_type !== 'tv') {
            throw new \InvalidArgumentException('Cannot mark a movie as an episode.');
        }

        $episodeDetails = $this->theMovieDbService->getTvShowEpisodeDetails(
            $media->tmdb_id,
            $season->season_number,
            $episode->episode_number
        );

        $runtime = $episodeDetails['runtime'] ?? 0;

        $userEpisode = UserEpisode::firstOrCreate(
            ['user_media_id' => $userMedia->id, 'episode_id' => $episode->id]
        );

        $userEpisode->status = 'watched';
        $userEpisode->watched_at = Carbon::now();
        $userEpisode->watched_duration = $runtime;
        $userEpisode->save();

        $this->checkAndSetUserMediaCompletionStatus($userMedia);

        return $userEpisode;
    }

    public function markEpisodeAsPaused(User $user, string $userMediaUuid, string $episodeUuid, int $watchedDuration): UserEpisode
    {
        $userMedia = UserMedia::where('uuid', $userMediaUuid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $episode = Episode::where('uuid', $episodeUuid)->firstOrFail();

        $userEpisode = UserEpisode::firstOrCreate(
            ['user_media_id' => $userMedia->id, 'episode_id' => $episode->id]
        );

        $userEpisode->status = 'paused';
        $userEpisode->watched_at = null;
        $userEpisode->watched_duration = $watchedDuration;
        $userEpisode->save();

        $userMedia->is_completed = false;
        $userMedia->save();

        return $userEpisode;
    }

    public function getAllUserMedia(User $user): Collection
    {
        return UserMedia::where('user_id', $user->id)
            ->with(['media.seasons.episodes', 'userEpisodes'])
            ->get()
            ->map(function ($userMedia) {
                return $this->calculateUserMediaProgress($userMedia);
            });
    }

    public function getUnfinishedUserMedia(User $user): Collection
    {
        return UserMedia::where('user_id', $user->id)
            ->where('is_completed', false)
            ->with(['media.seasons.episodes', 'userEpisodes'])
            ->get()
            ->map(function ($userMedia) {
                return $this->calculateUserMediaProgress($userMedia);
            });
    }

    public function resetUserMediaProgress(User $user, string $uuid): UserMedia
    {
        $userMedia = UserMedia::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $userMedia->userEpisodes()->delete(); // Delete all associated user episodes
        $userMedia->is_completed = false;
        $userMedia->status = 'watching';
        $userMedia->save();

        return $userMedia;
    }

    private function calculateUserMediaProgress(UserMedia $userMedia): UserMedia
    {
        if ($userMedia->media->media_type === 'tv') {
            $totalEpisodes = 0;
            foreach ($userMedia->media->seasons as $season) {
                if ($season->season_number > 0) {
                    $totalEpisodes += $season->episodes->count();
                }
            }

            $watchedEpisodesCount = $userMedia->userEpisodes
                ->where('status', 'watched')
                ->count();

            $userMedia->progress = [
                'total_episodes' => $totalEpisodes,
                'watched_episodes' => $watchedEpisodesCount,
                'percentage' => $totalEpisodes > 0 ? round(($watchedEpisodesCount / $totalEpisodes) * 100) : 0,
            ];
        } else {
            // For movies, progress is either 0% or 100%
            $userMedia->progress = [
                'total_episodes' => 1,
                'watched_episodes' => $userMedia->is_completed ? 1 : 0,
                'percentage' => $userMedia->is_completed ? 100 : 0,
            ];
        }

        return $userMedia;
    }

    private function checkAndSetUserMediaCompletionStatus(UserMedia $userMedia): void
    {
        if ($userMedia->media->media_type === 'movie') {
            $userMedia->is_completed = true;
            $userMedia->save();
            return;
        }

        $totalEpisodes = 0;
        foreach ($userMedia->media->seasons as $season) {
            if ($season->season_number > 0) {
                $totalEpisodes += $season->episodes->count();
            }
        }

        $watchedEpisodesCount = UserEpisode::where('user_media_id', $userMedia->id)
            ->where('status', 'watched')
            ->count();

        if ($totalEpisodes > 0 && $watchedEpisodesCount >= $totalEpisodes) {
            $userMedia->is_completed = true;
        } else {
            $userMedia->is_completed = false;
        }
        $userMedia->save();
    }

    private function fetchMediaDetailsFromTmdb(int $tmdbId, string $mediaType): array
    {
        if ($mediaType === 'movie') {
            return $this->theMovieDbService->getMovieDetails($tmdbId);
        } elseif ($mediaType === 'tv') {
            return $this->theMovieDbService->getTvShowDetails($tmdbId);
        }

        throw new \InvalidArgumentException('Invalid media type provided.');
    }

    private function saveTvShowSeasonsAndEpisodes(Media $media, array $tvShowDetails): void
    {
        foreach ($tvShowDetails['seasons'] as $seasonData) {
            $season = Season::firstOrCreate(
                ['media_id' => $media->id, 'tmdb_id' => $seasonData['id']],
                [
                    'season_number' => $seasonData['season_number'],
                    'name' => $seasonData['name'],
                    'overview' => $seasonData['overview'],
                    'poster_path' => $seasonData['poster_path'],
                    'air_date' => $seasonData['air_date'] ? Carbon::parse($seasonData['air_date']) : null,
                ]
            );

            if ($season->season_number > 0) {
                $seasonDetails = $this->theMovieDbService->getTvShowSeasonDetails($media->tmdb_id, $season->season_number);

                foreach ($seasonDetails['episodes'] as $episodeData) {
                    Episode::firstOrCreate(
                        ['season_id' => $season->id, 'tmdb_id' => $episodeData['id']],
                        [
                            'episode_number' => $episodeData['episode_number'],
                            'name' => $episodeData['name'],
                            'overview' => $episodeData['overview'],
                            'still_path' => $episodeData['still_path'],
                            'air_date' => $episodeData['air_date'] ? Carbon::parse($episodeData['air_date']) : null,
                        ]
                    );
                }
            }
        }
    }
}