<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TheMovieDbService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.api_key');
        $this->baseUrl = config('services.tmdb.base_url');
    }

    public function search(string $query): array
    {
        $response = Http::get($this->baseUrl . '/search/multi', [
            'api_key' => $this->apiKey,
            'query' => $query,
            'language' => 'pt-BR',
        ]);

        $response->throw();

        return $response->json();
    }

    public function getMovieDetails(int $tmdbId): array
    {
        $response = Http::get($this->baseUrl . '/movie/' . $tmdbId, [
            'api_key' => $this->apiKey,
            'language' => 'pt-BR',
        ]);

        $response->throw();

        return $response->json();
    }

    public function getTvShowDetails(int $tmdbId): array
    {
        $response = Http::get($this->baseUrl . '/tv/' . $tmdbId, [
            'api_key' => $this->apiKey,
            'language' => 'pt-BR',
        ]);

        $response->throw();

        return $response->json();
    }

    public function getTvShowSeasonDetails(int $tmdbId, int $seasonNumber): array
    {
        $response = Http::get($this->baseUrl . '/tv/' . $tmdbId . '/season/' . $seasonNumber, [
            'api_key' => $this->apiKey,
            'language' => 'pt-BR',
        ]);

        $response->throw();

        return $response->json();
    }
}