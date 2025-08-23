<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEpisode extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'user_media_id',
        'episode_id',
        'status',
        'watched_at',
        'paused_at',
    ];
}
