<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // New import

class UserEpisode extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'user_media_id',
        'episode_id',
        'status',
        'watched_at',
        'watched_duration', // Changed from paused_at
    ];

    public function userMedia(): BelongsTo
    {
        return $this->belongsTo(UserMedia::class);
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }
}