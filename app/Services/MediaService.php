<?php

namespace App\Services;

class MediaService
{
    public function __construct(private readonly TheMovieDbService $theMovieDbService)
    {
    }

    public function search(string $query): array
    {
        return $this->theMovieDbService->search($query);
    }
}
