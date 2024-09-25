<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Film;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function testFavoriteList()
    {
        Favorite::factory(5)->create(['user_id' => $this->user]);

        $response = $this->get(route('favorite.index'));
        $response->assertStatus(200);

        $this->assertEquals(5, count($response->json('data')));
    }

    public function testAddFilmToFavorite()
    {
        $response = $this->post(route('favorite.add', ['film' => $this->film]));

        $response
            ->assertStatus(201)
            ->assertJson(
                [
                'message' => "Фильм с id {$this->film->id} добавлен в избранное"
                ]
            );

        $this->assertDatabaseHas(
            Favorite::class,
            [
            'film_id' => $this->film->id,
            'user_id' => $this->user->id
            ]
        );
    }

    public function testDeleteFavoriteFilm()
    {
        Favorite::factory()->create(
            [
            'film_id' => $this->film->id,
            'user_id' => $this->user->id
            ]
        );

        $response = $this->delete(route('favorite.delete', ['film' => $this->film]));

        $response->assertStatus(204);

        $this->assertDatabaseMissing(
            Favorite::class,
            [
            'film_id' => $this->film->id,
            'user_id' => $this->user->id
            ]
        );
    }

    public function testDeleteFavoriteFilmIfNotExistsInFavorite()
    {
        $response = $this->delete(route('favorite.delete', ['film' => $this->film]));

        $response
            ->assertStatus(404)
            ->assertJson(
                [
                    "error" => "Запрашиваемая страница не существует"
                ]
            );

        $this->assertDatabaseCount(Favorite::class, 0);
    }

    public function testDoNotAllowDuplicateFilmInFavoriteList()
    {
        Favorite::factory()->create(
            [
            'film_id' => $this->film->id,
            'user_id' => $this->user->id
            ]
        );

        $response = $this->post(route('favorite.add', ['film' => $this->film]));

        $response
            ->assertStatus(409)
            ->assertJson(
                [
                    'error' => "Фильм с id {$this->film->id} уже в избранном"
                ]
            );

        $this->assertDatabaseCount(Favorite::class, 1);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->film = Film::factory()->create(
            [
            'status' => Film::STATUS_READY
            ]
        );
        $this->actingAs($this->user);
    }
}
