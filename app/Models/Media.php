<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // New import

class Media extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'tmdb_id',
        'title',
        'overview',
        'poster_path',
        'release_date',
        'media_type',
    ];

    public function userMedia(): HasMany
    {
        return $this->hasMany(UserMedia::class);
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }
}