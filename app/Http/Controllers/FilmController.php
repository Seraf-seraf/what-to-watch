<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilmRequest;
use App\Http\Resources\FilmsResource;
use App\Models\Film;
use App\Models\User;
use App\Services\FilmService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FilmController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->get('user');
        $films = Film::query()
            ->when(
                $request->has('status') && $user?->isAdmin(),
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

        return FilmsResource::collection($films)->response()->setStatusCode(200);
    }

    public function film(Film $film, Request $request)
    {
        $user = $request->attributes->get('user');

        if ($film->status !== 'ready' && !$user?->isAdmin()) {
            throw new NotFoundHttpException();
        }

        return FilmsResource::make($film)->response()->setStatusCode(200);
    }

    public function similar(Film $film, FilmService $service)
    {
        return $service->getSimilar($film);
    }

    public function update(Film $film, FilmRequest $request)
    {
        $values = $request->validated();

        $film->update($values);

        return FilmsResource::make($film);
    }

    public function add(FilmRequest $request)
    {
        $data = $request->validated();
        Film::create($data);

        return response()->json([
            'message' => "Фильм с imdb_id {$data['imdb_id']} добавлен в очередь на создание"
        ], 201);
    }

    public function delete(Film $film)
    {
        $film->delete();

        return response()->json([], 204);
    }
}
