<?php

namespace App\Http\Controllers;

use App\Http\Requests\FavoriteRequest;
use App\Http\Resources\FilmsResource;
use App\Models\Favorite;

class FavoriteController extends Controller
{
    public function index()
    {
        $favoriteFilms = Favorite::with(['film', 'user'])->get();

        return response()->json(FilmsResource::collection($favoriteFilms));
    }

    public function add(FavoriteRequest $request)
    {
        $values = $request->validated();
        $user = auth()->user();

        Favorite::create([
            'film_id' => $values['film_id'],
            'user_id' => $user->id,
        ]);

        return response()->json(['message' => "Фильм с id {$values['film_id']} добавлен в избранное"], 201);
    }

    public function delete(FavoriteRequest $request)
    {
        $user_id = auth()->id();

        Favorite::where('user_id', $user_id)
            ->where('film_id', $request->film_id)
            ->delete();

        return response()->json([], 204);
    }
}
