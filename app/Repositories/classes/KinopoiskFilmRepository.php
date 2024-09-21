<?php

namespace App\Repositories\classes;

use App\Repositories\interfaces\FilmRepositoryInterface;
use App\Services\HttpClientService;
use Psr\Http\Client\ClientExceptionInterface;

class KinopoiskFilmRepository implements FilmRepositoryInterface
{
    private readonly string $key;
    private string $baseApiUrl = 'https://api.kinopoisk.dev/v1.4';

    private array $baseHeaders;

    public function __construct(private readonly HttpClientService $service)
    {
        $this->key = config('services.kinopoisk.key');
        $this->baseHeaders = [
            'X-API-KEY' => $this->key,
            'accept' => 'application/json'
        ];
    }

    public function getCommentsByFilm(string $imdb_id): ?array
    {
        $film = $this->getFilm($imdb_id);
        if (!$film || empty($film['docs'][0]['id'])) {
            return null;
        }

        $film_id = $film['docs'][0]['id'];
        $allComments = [];
        $page = 1;

        do {
            $uri = "{$this->baseApiUrl}/review?" .
                "page=$page" .
                '&limit=250' .
                '&selectFields=id' .
                '&selectFields=type' .
                '&selectFields=review' .
                '&selectFields=createdAt' .
                '&notNullFields=type' .
                '&notNullFields=review' .
                '&notNullFields=id' .
                "&movieId=$film_id";


            $comments = $this->service->sendRequest(
                $this->service->createRequest('GET', $uri, $this->baseHeaders)
            );

            $allComments = array_merge($allComments, $comments['docs']);

            $page++;
        } while ($page <= $comments['pages']);

        return $allComments;
    }

    /**
     * Для нашего rest api нужно получить только id фильма в сервисе кинопоиск, для дальнейшего получения комментариев
     *
     * @param string $imdb_id
     * @return array|null
     */
    public function getFilm(string $imdb_id): ?array
    {
        try {
            $uri = $this->baseApiUrl . "/movie?page=1&limit=1&selectFields=id&externalId.imdb=$imdb_id";

            return $this->service->sendRequest($this->service->createRequest("GET", $uri, $this->baseHeaders));
        } catch (ClientExceptionInterface $exception) {
            \Log::error($exception->getMessage());
        }

        return null;
    }
}
