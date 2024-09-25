<?php

namespace App\Http\Controllers;

use App\Http\Resources\FilmResource;
use App\Http\Resources\PromoResource;
use App\Models\Favorite;
use App\Models\Film;
use App\Models\Promo;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

/**
 * @group Promo
 * Промо фильмы сервиса. Регулируется администратором
 */
class PromoController extends Controller
{
    #[Endpoint('GET /api/v1/promo', 'Получение списка всех промо фильмов')]
    #[Response('
        {
            "data": {
                "id": 5,
                "film": {
                    "id": 65,
                    "name": "Example Movie 1",
                    "posterImage": "https://example.com/poster1.jpg",
                    "previewImage": "https://example.com/preview1.jpg",
                    "backgroundImage": "https://example.com/background1.jpg",
                    "backgroundColor": "#FFFFFF",
                    "videoLink": "https://example.com/video1.mp4",
                    "previewVideoLink": "https://example.com/previewVideo1.mp4",
                    "description": "A great movie about...",
                    "rating": "9.00",
                    "scoresCount": 2,
                    "director": "Director 1",
                    "starring": [
                        "Actor 1",
                        "Actor 2"
                    ],
                    "runTime": 120,
                    "genre": [
                        "action"
                    ],
                    "released": 2021,
                    "status": "ready"
                }
            }
        }
    ')]
    public function index()
    {
        $promos = Promo::with('film')->get();

        return PromoResource::collection($promos)->response()->setStatusCode(200);
    }

    #[Authenticated]
    #[Endpoint('POST /api/v1/promo/{film_id}', 'Установка фильма как промо')]
    #[Response('{"message": "Фильм с id 65 установлен как промо-фильм"}', 201)]
    #[Response('{"error": "Фильм с id 65 уже установлен как промо-фильм"}', 409)]
    #[Response('{"error": "Нет активной сессии"}', 401)]
    #[Response('{"error": "Нет доступа к дейсвтию!"}', 403)]
    #[Response('{"error": "Запрашиваемая страница не существует"}', 404)]
    public function store(Film $film)
    {
        $promo = Promo::query()->firstOrCreate(['film_id' => $film->id]);

        if ($promo->wasRecentlyCreated) {
            return response()->json(['message' => "Фильм с id {$film->id} установлен как промо-фильм"], 201);
        } else {
            return response()->json(['error' => "Фильм с id {$film->id} уже установлен как промо-фильм"], 409);
        }
    }

    #[Authenticated]
    #[Endpoint('DELETE /api/v1/promo/{film_id}', 'Удаление фильма из списка промо фильмов')]
    #[Response('[]', 204)]
    #[Response('{"error": "Нет активной сессии"}', 401)]
    #[Response('{"error": "Нет доступа к дейсвтию!"}', 403)]
    #[Response('{"error": "Запрашиваемая страница не существует"}', 404)]
    public function destroy(Film $film)
    {
        Promo::where('film_id', $film->id)->firstOrFail()->delete();

        return response()->json([], 204);
    }
}
