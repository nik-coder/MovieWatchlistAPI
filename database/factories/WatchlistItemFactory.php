<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WatchlistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class WatchlistItemFactory extends Factory
{
    protected $model = WatchlistItem::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'imdb_id' => 'tt'.fake()->randomNumber(7),
            'title' => fake()->sentence(3),
            'status' => 'to_watch',
            'rating' => null,
            'notes' => null,
            'favorite' => false,
            'released_at' => fake()->date(),
            'poster_url' => null,
            'imdb_rating' => '7.0',
            'type' => 'movie',
            'year' => '2024',
            'runtime' => '120 min',
            'genre' => 'Drama',
            'director' => 'Director',
            'actors' => 'Actor',
            'plot' => fake()->paragraph(),
            'language' => 'English',
            'country' => 'United States',
        ];
    }
}
