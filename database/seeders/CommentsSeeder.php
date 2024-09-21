<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Film;
use Illuminate\Database\Seeder;

class CommentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $films = Film::factory(5)->create([
            'status' => Film::STATUS_READY
        ]);

        $films->each(function ($film) {
            $parentsComments = Comment::factory(2)->create([
                'film_id' => $film->id,
                'comment_id' => null
            ]);

            $parentsComments->each(function ($comment) use ($film) {
                Comment::factory(5)->create([
                    'comment_id' => $comment->id,
                    'film_id' => $film->id,
                    'rating' => null
                ]);
            });
        });
    }
}
