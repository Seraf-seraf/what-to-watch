<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexPage()
    {
        Genre::factory(5)->create();

        $response = $this->get(route('genres.index'));

        $response
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure(
                [
                'data' => [
                    '*' => ['name']
                ]
                ]
            );
    }

    public function testStoreGenre()
    {
        $user = User::factory()->create();
        $user->setAsAdmin();

        $this->actingAs($user);

        $response = $this->post(
            route('genres.store'),
            [
            'name' => 'Шутер'
            ]
        );

        $response
            ->assertStatus(201)
            ->assertJsonFragment(
                [
                'message' => 'Жанр добавлен в список',
                'data' => [
                    'name' => 'Шутер'
                ]
                ]
            );

        $this->assertDatabaseCount(Genre::class, 1);
    }

    public function testUpdateGenre()
    {
        $user = User::factory()->create();
        $user->setAsAdmin();

        $this->actingAs($user);

        $genre = Genre::factory()->create(
            [
            'name' => 'Comedy'
            ]
        );

        $response = $this->patch(
            route('genres.update', ['genre' => $genre]),
            [
            'name' => '123'
            ]
        );

        $response->assertStatus(200);

        $this
            ->assertDatabaseHas(
                Genre::class,
                [
                'name' => '123'
                ]
            )
            ->assertDatabaseCount(Genre::class, 1);
    }

    public function testUniqueGenre()
    {
        $user = User::factory()->create();
        $user->setAsAdmin();

        $this->actingAs($user);

        Genre::factory()->create(
            [
            'name' => 'Шутер'
            ]
        );

        $response = $this->post(
            route('genres.store'),
            [
            'name' => 'Шутер'
            ]
        );

        $response
            ->assertStatus(422)
            ->assertJsonFragment(
                [
                'errors' => [
                    'name' => ['Поле name должно быть уникальным']
                ]
                ]
            );

        $this->assertDatabaseCount(Genre::class, 1);
    }

    public function testUserCantCreateAndUpdateGenre()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(
            route('genres.store'),
            [
            'name' => 'Шутер'
            ]
        );

        $response
            ->assertStatus(403);
        $this->assertDatabaseCount(Genre::class, 0);

        $genre = Genre::factory()->create(
            [
            'name' => 'Comedy'
            ]
        );

        $response = $this->patch(
            route('genres.update', ['genre' => $genre]),
            [
            'name' => '123'
            ]
        );

        $response->assertStatus(403);

        $this
            ->assertDatabaseHas(
                Genre::class,
                [
                'name' => 'Comedy'
                ]
            )
            ->assertDatabaseCount(Genre::class, 1);
    }

    public function testDeleteGenre()
    {
        $user = User::factory()->create();
        $user->setAsAdmin();
        $this->actingAs($user);

        $genre = Genre::factory()->create();

        $response = $this->delete(route('genres.destroy', ['genre' => $genre]));

        $response->assertStatus(204);
        $this->assertDatabaseCount(Genre::class, 0);
    }
}
