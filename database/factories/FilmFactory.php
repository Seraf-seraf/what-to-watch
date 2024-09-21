<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Film;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Film>
 */
final class FilmFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Film::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'imdb_id' => $this->generateUniqImdbId(),
            'name' => fake()->optional()->name,
            'posterImage' => fake()->optional()->word,
            'previewImage' => fake()->optional()->word,
            'backgroundImage' => fake()->optional()->word,
            'backgroundColor' => fake()->optional()->word,
            'videoLink' => fake()->optional()->word,
            'previewVideoLink' => fake()->optional()->word,
            'description' => fake()->optional()->text,
            'director' => fake()->optional()->word,
            'starring' => fake()->optional()->word,
            'runTime' => fake()->optional()->randomNumber(),
            'genre' => fake()->optional()->word,
            'released' => fake()->optional()->randomNumber(),
            'status' => fake()->randomElement([
                Film::STATUS_READY,
                Film::STATUS_PENDING,
                Film::STATUS_MODERATE,
            ]),
        ];
    }

    public function generateUniqImdbId(): string
    {
        do {
            $imdb_id = 'tt' . str_pad((string)rand(0, 9999999), 7, '0', STR_PAD_LEFT);
        } while (Film::where('imdb_id', $imdb_id)->exists());

        return $imdb_id;
    }
}
