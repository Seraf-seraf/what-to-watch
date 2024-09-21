<?php

namespace App\Jobs\Classes;

use App\Models\Film;
use App\Services\FilmService;

class BaseWorkWithFilm
{
    protected function processFilm(string $id, FilmService $service): void
    {
        $imdbData = $service->requestFilm($id);
        $data = $service->transformImdbData($imdbData);

        $film = Film::query()->where('imdb_id', $id)->first();
        $newData = $data['film'];
        $newData = array_merge($newData, $this->processLinks($data['links'], $service, $film->imdb_id));
        $film->update($newData);
    }

    protected function processLinks(array $links, FilmService $service, string $imdbId): array
    {
        $newData = [];

        foreach ($links as $type => $link) {
            if (!is_null($link)) {
                $newData[$type] = $service->saveFile($link, $type, $imdbId);
            }
        }

        return $newData;
    }
}
