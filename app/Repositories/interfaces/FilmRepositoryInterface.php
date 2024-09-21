<?php

namespace App\Repositories\interfaces;

interface FilmRepositoryInterface
{
    public function getFilm(string $imdb_id): ?array;

    public function getCommentsByFilm(string $imdb_id);
}
