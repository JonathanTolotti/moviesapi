<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // New import
use Illuminate\Database\Eloquent\Relations\HasMany; // New import

class UserMedia extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'user_id',
        'media_id',
        'is_favorite',
        'status',
        'is_completed', // Added this
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function userEpisodes(): HasMany
    {
        return $this->hasMany(UserEpisode::class);
    }
}