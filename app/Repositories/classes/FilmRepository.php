<?php

namespace App\Repositories\classes;

use App\Models\Film;
use App\Repositories\interfaces\FilmRepositoryInterface;
use App\Services\HttpClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Psr\Http\Client\ClientExceptionInterface;

class FilmRepository implements FilmRepositoryInterface
{
    private readonly string $key;
    private string $baseApiUrl = 'http://www.omdbapi.com';

    public function __construct(private readonly HttpClientService $service)
    {
        $this->key = config('services.omdb.key');
    }

    public function getFilm(string $imdb_id): ?array
    {
        $uri = $this->baseApiUrl . "/?apikey={$this->key}&i=$imdb_id";

        try {
            return $this->service->sendRequest(
                $this->service->createRequest('GET', $uri)
            );
        } catch (ClientExceptionInterface $exception) {
            Log::error($exception->getMessage());
        }

        return null;
    }

    public function getCommentsByFilm(string $imdb_id): Redirector|RedirectResponse
    {
        return redirect(route('comments.show', [
            'film' => Film::query()->where(['imdb_id' => $imdb_id])->value('id')
        ]));
    }
}
