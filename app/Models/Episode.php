<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Episode extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'season_id',
        'tmdb_id',
        'episode_number',
        'name',
        'overview',
        'still_path',
        'air_date',
        'runtime', // Added this
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function userEpisodes(): HasMany
    {
        return $this->hasMany(UserEpisode::class);
    }
}