<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
