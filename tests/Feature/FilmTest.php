<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Film;
use App\Models\Genre;
use App\Models\User;
use Arr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilmTest extends TestCase
{
    use RefreshDatabase;

    public function testFilmIndex()
    {
        $count = rand(1, 8);
        Film::factory($count)->create(
            [
            'status' => 'ready'
            ]
        );

        $response = $this->get(route('films.index'));

        $response->assertStatus(200)
            ->assertJsonCount($count, 'data')
            ->assertJsonFragment(
                [
                'data' => $response->json('data')
                ]
            );
    }

    public function testFilmsByGenre()
    {
        $genre = Genre::factory()->create();
        $count = rand(1, 8);

        Film::factory($count)->create(
            [
            'status' => 'ready',
            'genre' => $genre
            ]
        );

        $response = $this->get(route('films.index', ['genre' => $genre]));

        $films = $response->json('data');

        $response->assertStatus(200)
            ->assertJsonCount($count, 'data')
            ->assertJsonFragment(
                [
                'data' => $films
                ]
            );
    }

    public function testFilmsByStatus()
    {
        $user = User::factory()->create();
        $user->setAsAdmin();
        $this->actingAs($user);

        Film::factory(2)->create(
            [
            'status' => Film::STATUS_MODERATE,
            ]
        );

        $response = $this->get(route('films.index', ['status' => Film::STATUS_MODERATE]));

        $films = $response->json('data');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(
                [
                'data' => $films
                ]
            );
    }

    public function testFilmsOrderByRating()
    {
        $film1 = Film::factory()
            ->has(Comment::factory()->state(['rating' => 5]))
            ->create(['released' => 2001, 'status' => 'ready']);

        $film2 = Film::factory()
            ->has(Comment::factory()->state(['rating' => 1]))
            ->create(['released' => 2002, 'status' => 'ready']);

        $film3 = Film::factory()
            ->has(Comment::factory()->state(['rating' => 3]))
            ->create(['released' => 2003, 'status' => 'ready']);

        $response = $this->get(route('films.index', ['orderBy' => 'rating']));
        $films = $response->json('data');

        $response->assertStatus(200);
        $this->assertEquals([$film1->id, $film3->id, $film2->id], Arr::pluck($films, 'id'));
    }

    public function testCalculateFilmRatingByComments()
    {
        $film = Film::factory()
            ->has(
                Comment::factory(3)->sequence(
                    ['rating' => 5, 'parent_id' => null],
                    ['rating' => 3, 'parent_id' => null],
                    ['rating' => 3, 'parent_id' => null]
                )
            )
            ->create(['released' => 2001, 'status' => 'ready']);

        $response = $this->get(route('film', ['film' => $film]));

        $data = $response->json('data');

        $response->assertStatus(200);
        $this->assertEquals(3.67, $data['rating']);
        $this->assertEquals(3, $data['scoresCount']);
    }

    public function testFilmInFavorite()
    {
        $film = Film::factory()->create(
            [
            'status' => 'ready'
            ]
        );
        $user = User::factory()->create();
        $this->actingAs($user);

        $favoriteFilm = Favorite::factory()->create(
            [
            'user_id' => $user->id,
            'film_id' => $film->id,
            ]
        );

        $response = $this->get(route('film', ['film' => $film]));

        $response
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                'is_favorite' => true
                ]
            );

        $this->assertInstanceOf(Film::class, $favoriteFilm->film);
        $this->assertEquals($film->id, $favoriteFilm->film->id);
        $this->assertInstanceOf(User::class, $favoriteFilm->user);
        $this->assertEquals($user->id, $favoriteFilm->user->id);
    }

    public function testAdminCanUpdateFilm()
    {
        $user = User::factory()->create();
        $user->setAsAdmin();

        $this->actingAs($user);

        $film = Film::factory()->create();

        $response = $this->patch(
            route('films.update', ['film' => $film]),
            [
            'status' => Film::STATUS_READY,
            'genre' => 'shooter, comedy',
            'released' => '2024'
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                'status' => Film::STATUS_READY,
                'genre' => ["comedy", "shooter"],
                'released' => '2024'
                ]
            );

        $this->assertDatabaseHas(
            Film::class,
            [
            'id' => $film->id,
            'status' => Film::STATUS_READY,
            'released' => '2024'
            ]
        );

        $this->assertEquals(['shooter', 'comedy'], $response->json('data.genre'));
    }

    public function testDeleteFilm()
    {
        $user = User::factory()->create();
        $user->setAsAdmin();
        $this->actingAs($user);

        $film = Film::factory()->create();

        $response = $this->delete(route('films.delete', ['film' => $film]));

        $response->assertStatus(204);
        $this->assertDatabaseMissing(Film::class, ['id' => $film->id]);
    }

    public function testAddFilmFromRepository()
    {
        $user = User::factory()->create();
        $user->setAsAdmin();

        $this->actingAs($user);

        $response = $this->post(
            route('films.add'),
            [
            'imdb_id' => 'tt0059640'
            ]
        );

        $response
            ->assertStatus(201)
            ->assertJsonFragment(
                [
                'message' => 'Фильм с imdb_id tt0059640 добавлен в очередь на создание'
                ]
            );

        $this->assertDatabaseHas(
            Film::class,
            [
            'imdb_id' => 'tt0059640'
            ]
        );
    }

    public function testGetSimilarFilm()
    {
        $film = Film::factory()->create(
            [
            'genre' => 'shooter, comedy'
            ]
        );

        $similarFilm = Film::factory()->create(
            [
            'genre' => 'comedy'
            ]
        );

        $response = $this->get(route('films.similar', ['film' => $film]));

        $response
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                'id' => $similarFilm->id,
                ]
            );
    }

    public function testGetFilmWithNotReadyStatus()
    {
        $film = Film::factory()->create(
            [
            'status' => 'pending'
            ]
        );

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('film', ['film' => $film]));

        $response
            ->assertStatus(404);
    }

    public function testSetWrongPosterUrl()
    {
        $film = Film::factory()->create(
            [
            'status' => 'pending'
            ]
        );

        $user = User::factory()->create();
        $user->setAsAdmin();
        $this->actingAs($user);

        $response = $this->patch(
            route('films.update', ['film' => $film]),
            [
            'posterImage' => 1,
            'previewImage' => 123,
            'backgroundImage' => 123
            ]
        );

        $extensions = implode(', ', config('filesystems.img_extensions'));

        $errorMessage = "Ссылка должна вести на картинку: https://.../image.png; Допустимые расширения: $extensions";

        $response
            ->assertStatus(422)
            ->assertJsonFragment(
                [
                'errors' => [
                    'posterImage' => [$errorMessage],
                    'previewImage' => [$errorMessage],
                    'backgroundImage' => [$errorMessage],
                ]
                ]
            );
    }
}
