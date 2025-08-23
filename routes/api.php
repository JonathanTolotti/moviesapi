<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\UserMediaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/media/search', [MediaController::class, 'search']);

    Route::prefix('user-media')->group(function () {
        Route::post('/', [UserMediaController::class, 'addMedia']);
        Route::post('/{uuid}/favorite', [UserMediaController::class, 'toggleFavorite']);
        Route::post('/{uuid}/episode/{episodeUuid}/watch', [UserMediaController::class, 'markEpisodeAsWatched']);
        Route::post('/{uuid}/episode/{episodeUuid}/pause', [UserMediaController::class, 'markEpisodeAsPaused']);

        Route::get('/', [UserMediaController::class, 'index']); // New route
        Route::get('/unfinished', [UserMediaController::class, 'unfinished']); // New route
        Route::post('/{uuid}/reset-progress', [UserMediaController::class, 'resetProgress']); // New route
    });
});