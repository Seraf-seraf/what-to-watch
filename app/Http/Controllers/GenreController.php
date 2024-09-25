<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenreRequest;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

/**
 * @group Genre
 * Получение списка жанров и управление списком жанров модератором
 */
class GenreController extends Controller
{

    #[QueryParam('orderTo', 'string',
        'Направление сортировки по атрибуту name',
        required: false,
        example: 'asc',
        enum: ['asc', 'desc']
    )]
    #[ResponseFromApiResource(GenreResource::class, Genre::class, collection: true)]
    public function index(Request $request)
    {
        $genres = Genre::query()
            ->orderBy('name', $request->orderTo ?? 'desc')
            ->get();

        return GenreResource::collection($genres);
    }

    #[Authenticated]
    #[Response('{"error": "Нет активной сессии"}', 401)]
    #[Response('{"error": "Нет доступа к дейсвтию!"}', 403)]
    #[ResponseFromApiResource(GenreResource::class, Genre::class, 201,
        additional: ['message' => 'Жанр добавлен в список']
    )]
    public function store(GenreRequest $request)
    {
        $values = $request->validated();

        $genre = Genre::create($values);

        return response()->json([
            'message' => 'Жанр добавлен в список',
            'data' => GenreResource::make($genre)
        ], 201);
    }

    #[Authenticated]
    #[Endpoint('PATCH api/v1/genres/{id}', 'Обновление жанра')]
    #[UrlParam('id', 'int', 'Id жанра', required: true)]
    #[ResponseFromApiResource(GenreResource::class, Genre::class)]
    #[Response('{"error": "Нет активной сессии"}', 401)]
    #[Response('{"error": "Нет доступа к дейсвтию!"}', 403)]
    #[Response('{"error": "Запрашиваемая страница не существует"}', 404)]
    public function update(GenreRequest $request, Genre $genre)
    {
        $values = $request->validated();

        $genre->update($values);

        return response()->json([
            'message' => "Жанр c id {$genre->id} обновлен",
            'data' => GenreResource::make($genre)
        ]);
    }

    #[Authenticated]
    #[Endpoint('DELETE api/v1/genres/{id}', 'Удаление жанра')]
    #[UrlParam('id', 'int', 'Id жанра', required: true)]
    #[Response('{"error": "Нет активной сессии"}', 401)]
    #[Response('{"error": "Нет доступа к дейсвтию!"}', 403)]
    #[Response('{"error": "Запрашиваемая страница не существует"}', 404)]
    public function destroy(Genre $genre)
    {
        $genre->delete();

        return response()->json([], 204);
    }
}
