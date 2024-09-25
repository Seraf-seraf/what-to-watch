<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilmRequest;
use App\Http\Resources\FilmResource;
use App\Jobs\PendingFilms;
use App\Models\Film;
use App\Models\User;
use App\Services\FilmService;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @group Film
 * Эндпоинты для работой с фильмами, редактировать фильмы могут только модераторы.
 * У фильмов есть три статуса:
 * - со статусом Film::STATUS_READY доступны пользователям,
 * - со статусом Film::STATUS_PENDING, который устанавлвиается при добавлении фильма,
 *   обновляются данными из omdbApi
 * - со статусом Film::STATUS_MODERATE редактируются модераторами
 */
class FilmController extends Controller
{
    #[Endpoint('GET api/v1/films', 'Список фильмов')]
    #[QueryParam('status', 'string',
        'Администраторы могут фильтровать фильмы по разным статусам
        У обычных пользователей этот фильтр не работает',
        required: false,
        example: 'ready'
    )]
    #[QueryParam('genre', 'string', 'Фильтр по жанру фильма', required: false, example: 'Шутер')]
    #[QueryParam('orderBy', 'string', 'Фильтр по атрибуту. По умолчанию сортируется по released',
        required: false,
        example: 'released'
    )]
    #[QueryParam('orderTo', 'string', 'Направление фильтра по атрибуту',
        required: false,
        example: 'asc',
        enum: ['asc', 'desc']
    )]
    #[ResponseFromApiResource(FilmResource::class, Film::class, collection: true, paginate: 8)]
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->get('user');
        $films = Film::query()
            ->with(['favorites' => fn ($query) => $query->where('user_id', auth()->id())])
            ->when(
                $request->has('status') && auth()->user()?->isAdmin(),
                function ($query) use ($request) {
                    $query->where(['status' => $request->get('status')]);
                },
                function ($query) use ($request) {
                    $query->where(['status' => 'ready']);
                }
            )
            ->when($request->has('genre'), function ($query) use ($request) {
                $query->whereJsonContains('genre', $request->get('genre'));
            })
            ->ordered($request->get('orderBy'), $request->get('orderTo'))
            ->paginate(8);

        return FilmResource::collection($films);
    }

    #[Endpoint('GET /api/v1/films/{film_id}', "
        Получение фильма по id
    ")]
    #[UrlParam('film_id', 'int', 'ID фильма', required: true, example: 65)]
    #[Response('{"error": "Запрашиваемая страница не существует"}', 404)]
    #[ResponseFromApiResource(FilmResource::class, Film::class)]
    public function film(Film $film, Request $request)
    {
        $user = $request->attributes->get('user');

        if ($film->status !== 'ready' && !$user?->isAdmin()) {
            throw new NotFoundHttpException();
        }

        return FilmResource::make($film);
    }

    #[Endpoint('GET /api/v1/films/{film_id}/similar', "
        Поиск похожих фильмов по жанрам. Вывод массива фильмов, у которых хоть 1 жанр совпадает.
        Выводится 4 похожих фильма (по умолчанию).
        Изменить значение можно в конфиге приложения: config('app.api.similar.limit')
    ")]
    #[UrlParam('film_id', 'int', 'ID фильма', example: 65)]
    #[Response('{"error": "Запрашиваемая страница не существует"}', 404)]
    #[ResponseFromApiResource(FilmResource::class, Film::class, collection: true)]
    public function similar(Film $film, FilmService $service)
    {
        return $service->getSimilar($film);
    }

    #[Authenticated]
    #[Endpoint('PATCH /api/v1/films/{film_id}', "
        Обновление данных о фильме.
        Выполнять действие может только модератор
    ")]
    #[UrlParam('film_id', 'int', 'ID фильма', required: true, example: 65)]
    #[BodyParam('posterImage', 'string',
        'Ссылка должна вести на картинку: https://.../image.png;
        Допустимые расширения расширения задаются в config("filesystems.img_extensions")',
        required: false,
        example: 'https://img.freepik.com/free-photo/view-of-3d-adorable-cat-with-fluffy-clouds_23-2151113419.jpg'
    )]
    #[BodyParam('previewImage', 'string',
        'Ссылка должна вести на картинку: https://.../image.png;
        Допустимые расширения расширения задаются в config("filesystems.img_extensions")',
        required: false,
        example: 'https://img.freepik.com/free-photo/view-of-3d-adorable-cat-with-fluffy-clouds_23-2151113419.jpg'
    )]
    #[BodyParam('backgroundImage', 'string',
        'Ссылка должна вести на картинку: https://.../image.png;
        Допустимые расширения расширения задаются в config("filesystems.img_extensions")',
        required: false,
        example: 'https://img.freepik.com/free-photo/view-of-3d-adorable-cat-with-fluffy-clouds_23-2151113419.jpg'
    )]
    #[BodyParam('videoLink', 'string', 'Валидный url адрес',
        required: false,
        example: 'https://www.youtube.com/watch?v=dQw4w9WgQ'
    )]
    #[BodyParam('previewVideoLink', 'Валидный url адрес',
        required: false,
        example: 'https://www.youtube.com/watch?v=dQw4w9WgQ'
    )]
    #[BodyParam('director', 'string', 'Директор', required: false)]
    #[BodyParam('starring', 'string',
        'Актеры в фильме. Заполняется в строку, можно заполнять через запятую',
        required: false,
        example: 'Актер 1, Актер 2'
    )]
    #[BodyParam('released', 'string', 'Год выпуска фильма', required: false, example: '2003 или 2003-2005')]
    #[BodyParam('runTime', 'int', 'Время длительности фильма', required: false, example: 140)]
    #[BodyParam('genre', 'string',
        'Жанры фильма. Заполняется в строку, жанры можно заполнять через запятую',
        required: false,
        example: 'комедия, семейный'
    )]
    #[BodyParam('status', 'string', 'Статусы фильмов', required: false, example: Film::STATUS_MODERATE, enum: FilmRequest::STATUS_FILM)]
    #[BodyParam('imdb_id', 'string',
        'imdbId, который должен соответстовать регулярному выражению: /^tt\d{7,8}$/',
        required: true,
        example: 'tt1111111')
    ]
    #[ResponseFromApiResource(FilmResource::class, Film::class)]
    #[Response('{"error": "Нет активной сессии"}', 401)]
    #[Response('{"error": "Нет доступа к дейсвтию!"}', 403)]
    #[Response('{"error": "Запрашиваемая страница не существует"}', 404)]
    public function update(Film $film, FilmRequest $request)
    {
        $values = $request->validated();

        $film->update($values);

        return FilmResource::make($film);
    }

    #[Authenticated]
    #[Endpoint('POST /api/v1/films', "
        Добавление фильма в базу. Проставляется статус ". Film::STATUS_PENDING. ".
        У фильмов с этим статусом обновляются данные посредством обращения раз в день к данным omdbApi.
        Выполнять действие может только модератор.
    ")]
    #[UrlParam('id', 'int', 'ID фильма', example: 65)]
    #[BodyParam('posterImage', 'string',
        'Ссылка должна вести на картинку: https://.../image.png;
        Допустимые расширения расширения задаются в config("filesystems.img_extensions")',
        required: false,
        example: 'https://img.freepik.com/free-photo/view-of-3d-adorable-cat-with-fluffy-clouds_23-2151113419.jpg'
    )]
    #[BodyParam('previewImage', 'string',
        'Ссылка должна вести на картинку: https://.../image.png;
        Допустимые расширения расширения задаются в config("filesystems.img_extensions")',
        required: false,
        example: 'https://img.freepik.com/free-photo/view-of-3d-adorable-cat-with-fluffy-clouds_23-2151113419.jpg'
    )]
    #[BodyParam('backgroundImage', 'string',
        'Ссылка должна вести на картинку: https://.../image.png;
        Допустимые расширения расширения задаются в config("filesystems.img_extensions")',
        required: false,
        example: 'https://img.freepik.com/free-photo/view-of-3d-adorable-cat-with-fluffy-clouds_23-2151113419.jpg'
    )]
    #[BodyParam('videoLink', 'string', 'Валидный url адрес',
        required: false,
        example: 'https://www.youtube.com/watch?v=dQw4w9WgQ'
    )]
    #[BodyParam('previewVideoLink', 'Валидный url адрес',
        required: false,
        example: 'https://www.youtube.com/watch?v=dQw4w9WgQ'
    )]
    #[BodyParam('released', 'int', 'Год выпуска фильма', required: false, example: '2003 или 2003-2005')]
    #[BodyParam('runTime', 'int', 'Время длительности фильма', required: false, example: 140)]
    #[BodyParam('starring', 'string',
        'Актеры в фильме. Заполняется в строку, можно заполнять через запятую',
        required: false,
        example: 'Актер 1, Актер 2'
    )]
    #[BodyParam('genre', 'string',
        'Жанры фильма. Заполняется в строку, жанры можно заполнять через запятую',
        required: false,
        example: 'комедия, семейный'
    )]
    #[BodyParam('status', 'string', 'Статусы фильмов',
        required: false,
        example: Film::STATUS_MODERATE,
        enum: FilmRequest::STATUS_FILM)]
    #[BodyParam('imdb_id', 'string',
        'imdbId, который должен соответстовать регулярному выражению: /^tt\d{7,8}$/',
        required: true,
        example: 'tt1111111')
    ]
    #[Response('message: Фильм с imdb_id tt4523465 добавлен в очередь на создание', status: 201)]
    #[Response('{"error": "Нет активной сессии"}', 401)]
    #[Response('{"error": "Нет доступа к дейсвтию!"}', 403)]
    public function add(FilmRequest $request)
    {
        $data = $request->validated();
        Film::create($data);

        return response()->json([
            'message' => "Фильм с imdb_id {$data['imdb_id']} добавлен в очередь на создание"
        ], 201);
    }

    #[Authenticated]
    #[Endpoint('DELETE api/v1/films/{film_id}', 'Удаление фильма')]
    #[UrlParam('film_id', 'int', 'ID фильма', example: 65)]
    #[Response('{"error": "Нет активной сессии"}', 401)]
    #[Response('{"error": "Нет доступа к дейсвтию!"}', 403)]
    #[Response('{"error": "Запрашиваемая страница не существует"}', 404)]
    #[Response('[]', status: 204)]
    public function delete(Film $film)
    {
        $film->delete();

        return response()->json([], 204);
    }
}
