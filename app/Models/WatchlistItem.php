<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'imdb_id',
        'title',
        'status',
        'rating',
        'notes',
        'favorite',
        'watched_at',
        'released_at',
        'poster_url',
        'imdb_rating',
        'type',
        'year',
        'runtime',
        'genre',
        'director',
        'actors',
        'plot',
        'language',
        'country',
    ];

    protected $casts = [
        'favorite' => 'boolean',
        'rating' => 'integer',
        'watched_at' => 'datetime',
        'released_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
