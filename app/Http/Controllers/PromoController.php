<?php

namespace App\Http\Controllers;

use App\Http\Resources\FilmsResource;
use App\Models\Film;
use App\Models\Promo;

class PromoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $promos = Promo::with('film')->get();

        return response()->json(FilmsResource::collection($promos));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Film $film)
    {
        if (Promo::where(['film_id' => $film->id])->exists()) {
            return response()->json(['error' => "Фильм с id {$film->id} уже установлен как промо-фильм"], 422);
        }

        $film->promos()->create(['film_id' => $film->id]);

        return response()->json(['message' => "Фильм с id {$film->id} установлен как промо-фильм"], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Film $film)
    {
        Promo::where('film_id', $film->id)->delete();

        return response()->json([], 204);
    }
}
