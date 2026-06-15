<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WatchlistItem;
use App\Services\OmdbMovieService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class WatchlistController extends Controller
{
    public function __construct(protected OmdbMovieService $omdbMovieService)
    {
    }

    public function index(Request $request)
    {
        $query = $request->user()->watchlistItems();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('favorite')) {
            $query->where('favorite', (bool) $request->boolean('favorite'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('genre', 'like', "%{$search}%")
                    ->orWhere('director', 'like', "%{$search}%")
                    ->orWhere('actors', 'like', "%{$search}%" );
            });
        }

        $sortColumn = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        $validSorts = ['created_at', 'title', 'year', 'rating', 'imdb_rating', 'watched_at'];

        if (! in_array($sortColumn, $validSorts, true)) {
            $sortColumn = 'created_at';
        }

        $query->orderBy($sortColumn, $direction);

        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'status' => ['nullable', Rule::in(['to_watch', 'watching', 'watched', 'dropped'])],
            'favorite' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $existing = $request->user()->watchlistItems()->where('imdb_id', $this->normalizeIdentifier($data['identifier']))->first();
        if ($existing) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'identifier' => ['This movie is already in your watchlist.'],
            ]);
        }

        $movie = $this->omdbMovieService->fetch($data['identifier']);

        $item = $request->user()->watchlistItems()->create([
            'imdb_id' => $movie['imdb_id'],
            'title' => $movie['title'],
            'status' => $data['status'] ?? 'to_watch',
            'favorite' => $data['favorite'] ?? false,
            'notes' => $data['notes'] ?? null,
            'released_at' => $movie['released_at'],
            'poster_url' => $movie['poster_url'],
            'imdb_rating' => $movie['imdb_rating'],
            'type' => $movie['type'],
            'year' => $movie['year'],
            'runtime' => $movie['runtime'],
            'genre' => $movie['genre'],
            'director' => $movie['director'],
            'actors' => $movie['actors'],
            'plot' => $movie['plot'],
            'language' => $movie['language'],
            'country' => $movie['country'],
        ]);

        return response()->json(['data' => $item], 201);
    }

    public function show(Request $request, WatchlistItem $watchlistItem)
    {
        abort_unless($watchlistItem->user_id === $request->user()->id, 404);

        return response()->json(['data' => $watchlistItem]);
    }

    public function update(Request $request, WatchlistItem $watchlistItem)
    {
        abort_unless($watchlistItem->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'status' => ['nullable', Rule::in(['to_watch', 'watching', 'watched', 'dropped'])],
            'rating' => ['nullable', 'integer', 'min:0', 'max:10'],
            'notes' => ['nullable', 'string'],
            'favorite' => ['nullable', 'boolean'],
            'watched_at' => ['nullable', 'date'],
        ]);

        $watchlistItem->fill($data)->save();

        return response()->json(['data' => $watchlistItem->fresh()]);
    }

    public function destroy(Request $request, WatchlistItem $watchlistItem)
    {
        abort_unless($watchlistItem->user_id === $request->user()->id, 404);

        $watchlistItem->delete();

        return response()->json(null, 204);
    }

    private function normalizeIdentifier(string $identifier): string
    {
        return str_starts_with($identifier, 'tt') ? $identifier : $identifier;
    }
}
