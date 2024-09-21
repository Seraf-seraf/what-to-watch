<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Comment>
 */
final class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'text' => fake()->sentence(40),
            'rating' => fake()->numberBetween(1, 10),
            'comment_id' => null,
            'film_id' => \App\Models\Film::factory(),
            'created_at' => fake()->dateTime(),
        ];
    }
}
