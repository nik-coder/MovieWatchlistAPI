<?php

namespace App\Services;

use App\Exceptions\MovieNotFoundException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class OmdbMovieService
{
    public function fetch(string $identifier): array
    {
        $baseUrl = rtrim(config('services.omdb.base_url', env('OMDB_BASE_URL', 'https://www.omdbapi.com')), '/');
        $apiKey = config('services.omdb.key', env('OMDB_API_KEY'));

        $response = Http::timeout((int) config('services.omdb.timeout', env('OMDB_TIMEOUT', 10)))
            ->get($baseUrl, [
                'apikey' => $apiKey,
                'i' => $identifier,
                't' => $identifier,
                'r' => 'json',
            ]);

        $payload = $response->json();

        if (($payload['Response'] ?? 'False') !== 'True') {
            throw new MovieNotFoundException($payload['Error'] ?? 'Movie not found.');
        }

        return [
            'imdb_id' => data_get($payload, 'imdbID'),
            'title' => data_get($payload, 'Title'),
            'year' => data_get($payload, 'Year'),
            'released_at' => $this->normalizeDate(data_get($payload, 'Released')),
            'poster_url' => $this->normalizePoster(data_get($payload, 'Poster')),
            'runtime' => data_get($payload, 'Runtime'),
            'genre' => data_get($payload, 'Genre'),
            'director' => data_get($payload, 'Director'),
            'actors' => data_get($payload, 'Actors'),
            'plot' => data_get($payload, 'Plot'),
            'language' => data_get($payload, 'Language'),
            'country' => data_get($payload, 'Country'),
            'imdb_rating' => data_get($payload, 'imdbRating'),
            'type' => data_get($payload, 'Type'),
        ];
    }

    private function normalizeDate(?string $released): ?string
    {
        if (blank($released) || $released === 'N/A') {
            return null;
        }

        try {
            return Carbon::createFromFormat('d M Y', $released)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function normalizePoster(?string $poster): ?string
    {
        if (blank($poster) || $poster === 'N/A') {
            return null;
        }

        return $poster;
    }
}
