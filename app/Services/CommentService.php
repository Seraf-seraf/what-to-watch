<?php

namespace App\Services;

use App\Models\Film;
use App\Repositories\interfaces\FilmRepositoryInterface;
use DateTime;
use DateTimeZone;

class CommentService
{
    public function __construct(private readonly FilmRepositoryInterface $repository)
    {
    }

    public function requestComments(string $imdb_id): ?array
    {
        return $this->repository->getCommentsByFilm($imdb_id);
    }

    public function reformatCommentsFromKinopoisk(?array $comments, string $imdb_id): ?array
    {
        $result = [];
        $filmId = Film::where(['imdb_id' => $imdb_id])->value('id');

        if (!$filmId || empty($comments)) {
            return null;
        }

        foreach ($comments as $value) {
            $item = [
                'id' => $value['id'],
                'rating' => $this->reformatRatingCommentFromKinopoisk($value['type']),
                'text' => htmlspecialchars($value['review']),
                'film_id' => $filmId,
                'created_at' => isset($value['createdAt'])
                    ? (new DateTime())->setTimestamp((int)$value['createdAt'])
                    : null

            ];
            $result[] = $item;
        }

        return $result;
    }

    public function reformatRatingCommentFromKinopoisk(string $ratingType): int
    {
        return match ($ratingType) {
            'Позитивный' => 10,
            'Нейтральный' => 5,
            'Негативный' => 1,
        };
    }
}
