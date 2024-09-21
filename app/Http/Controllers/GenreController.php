<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenreRequest;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $genres = Genre::query()
            ->orderBy('name', $request->order_to ?? 'desc')
            ->get();

        return GenreResource::collection($genres);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GenreRequest $request)
    {
        $values = $request->validated();

        $genre = Genre::create($values);

        return response()->json([
            'message' => 'Жанр добавлен в список',
            'data' => GenreResource::make($genre)
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GenreRequest $request, Genre $genre)
    {
        $values = $request->validated();

        $genre->update($values);

        return response()->json([
            'message' => "Жанр c id {$genre->id} обновлен",
            'data' => GenreResource::make($genre)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Genre $genre)
    {
        $genre->delete();

        return response()->json([], 204);
    }
}
