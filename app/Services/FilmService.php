<?php

namespace App\Services;

use App\Http\Resources\FilmResource;
use App\Models\Film;
use App\repositories\classes\FilmRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FilmService
{
    public function __construct(private readonly FilmRepository $repository)
    {
    }

    public function requestFilm(string $filmId): array
    {
        $response = $this->repository->getFilm($filmId);

        if ($response['Response'] === 'False') {
            Log::error('Неверный imdb_id');
            return [];
        }

        return $response;
    }


    public function getSimilar(Film $film): JsonResponse
    {
        $filmGenres = is_array($film->genre) ? $film->genre : [json_decode($film->genre)];
        $films = Film::query()
            ->where('id', '!=', $film->id)
            ->where(function ($query) use ($filmGenres) {
                foreach ($filmGenres as $genre) {
                    $query->orWhereJsonContains('genre', strtolower($genre));
                }
            })
            ->take(config('app.api.similar.limit'))
            ->get();

        return FilmResource::collection($films)->response();
    }

    public function transformImdbData($imdbData): ?array
    {
        $data = [];

        $data['film'] = [
            'name' => $imdbData['Title'] ?? '',
            'backgroundColor' => '#000000',
            'description' => $imdbData['Plot'] ?? '',
            'director' => $imdbData['Director'] ?? '',
            'starring' => $imdbData['Actors'] ?? '',
            'runTime' => (int) filter_var($imdbData['Runtime'] ?? 0, FILTER_SANITIZE_NUMBER_INT),
            'genre' => $imdbData['Genre'] ?? '',
            'released' => $imdbData['Year'] ?? 0,
            'status' => Film::STATUS_MODERATE,
        ];

        $data['links'] = [
            'posterImage' => $imdbData['Poster'] ?? 'https://placehold.co/600x300',
            'previewImage' => $imdbData['Poster'] ?? 'https://placehold.co/600x300',
            'backgroundImage' => $imdbData['Poster'] ?? 'https://placehold.co/600x300',
        ];

        return $data;
    }

    public function saveFile(string $url, string $type, string $name): ?string
    {
        $response = Http::get($url);

        if ($response->failed() || $response->status() !== 200) {
            return 'https://placehold.co/600x300';
        }

        $file = $response->body();

        $ext = pathinfo($url, PATHINFO_EXTENSION);
        $path = $type . DIRECTORY_SEPARATOR . $name . ".$ext";

        Storage::disk('public')->put($path, $file);

        return Storage::disk('public')->url($path);
    }
}
