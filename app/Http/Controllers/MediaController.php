<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchMediaRequest;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;

class MediaController extends Controller
{
    public function __construct(private readonly MediaService $mediaService)
    {
    }

    public function search(SearchMediaRequest $request): JsonResponse
    {
        $results = $this->mediaService->search($request->query('query'));

        return response()->json($results);
    }
}
