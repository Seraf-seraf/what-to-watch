<?php

namespace App\Http\Controllers;

use App\Http\Resources\FavoriteResource;
use App\Http\Resources\FilmResource;
use App\Models\Favorite;
use App\Models\Film;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

/**
 * @group  Favorite list
 * Список избранных фильмов
 */
class FavoriteController extends Controller
{
    #[Endpoint('GET /api/v1/favorite', 'Получение списка фильмов, добавленных в список избранных')]
    #[ResponseFromApiResource(FavoriteResource::class, Favorite::class, collection: true, with: ['film'], paginate: 8)]
    #[Response('{"error": "Нет активной сессии"}', 401)]
    public function index()
    {
        $favoriteFilms = Favorite::query()
            ->with('film')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'DESC')
            ->paginate(8);

        return FavoriteResource::collection($favoriteFilms);
    }

    #[Authenticated]
    #[Endpoint('POST /api/v1/favorite/{film_id}', 'Добавление фильма в список избранных')]
    #[UrlParam('film_id', 'int', 'ID фильма', example: 65)]
    #[Response('{"error": "Нет активной сессии"}', 401)]
    #[Response('{"error": "Запрашиваемая страница не существует"}', 404)]
    public function add(Film $film)
    {
        $user = auth()->user();

        $favorite = Favorite::query()->firstOrCreate(['user_id' => $user->id, 'film_id' => $film->id]);

        if ($favorite->wasRecentlyCreated) {
            return response()->json(['message' => "Фильм с id {$film->id} добавлен в избранное"], 201);
        } else {
            return response()->json(['error' => "Фильм с id {$film->id} уже в избранном"], 409);
        }
    }

    #[Authenticated]
    #[Endpoint('DELETE /api/v1/favorite/{film_id}', 'Удаление фильма из списка избранных')]
    #[UrlParam('film_id', 'int', 'ID фильма', example: 65)]
    #[Response('{"error": "Нет активной сессии"}', 401)]
    #[Response('{"error": "Запрашиваемая страница не существует"}', 404)]
    public function delete(Film $film)
    {
        $user_id = auth()->id();

        Favorite::query()
            ->where('user_id', $user_id)
            ->where('film_id', $film->id)
            ->firstOrFail()
            ->delete();

        return response()->json([], 204);
    }
}
