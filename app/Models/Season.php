<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // New import
use Illuminate\Database\Eloquent\Relations\HasMany; // New import

class Season extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'media_id',
        'tmdb_id',
        'season_number',
        'name',
        'overview',
        'poster_path',
        'air_date',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }
}